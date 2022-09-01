<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;
use Spatie\Activitylog\Facades\LogBatch;

trait HasImages
{
    /**
     * New images we want to upload.
     *
     * @var array
     */
    public $imageUploadQueue = [];

    /**
     * The existing images for the model.
     *
     * @var array
     */
    public $images = [];

    public function getHasImagesListeners()
    {
        return [
            'upload:finished' => 'handleUploadFinished',
        ];
    }

    /**
     * Define validation rules for images.
     *
     * @return array
     */
    protected function hasImagesValidationRules()
    {
        return [
            'imageUploadQueue.*' => 'image|max:' . max_upload_filesize(),
            'images.*.caption'   => 'nullable|string',
        ];
    }

    /**
     * Mount the component trait.
     *
     * @return void
     */
    public function mountHasImages()
    {
        $owner = $this->getMediaModel();

        $this->images = $owner->media->map(function ($media) {
            return [
                'id'        => $media->id,
                'sort_key'  => Str::random(),
                'thumbnail' => $media->getFullUrl('medium'),
                'original'  => $media->getFullUrl(),
                'preview'   => false,
                'edit'      => false,
                'caption'   => $media->getCustomProperty('caption'),
                'primary'   => $media->getCustomProperty('primary'),
                'position'  => $media->getCustomProperty('position', 1),
            ];
        })->sortBy('position')->values()->toArray();
    }

    /**
     * Listener for when new images are uploaded.
     *
     * @return void
     */
    public function updatedImages()
    {
        $this->validate($this->hasImagesValidationRules());
    }

    /**
     * Abstract method to get the media model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getMediaModel();

    /**
     * Method to handle when Livewire uploads a product image.
     *
     * @param  string  $name
     * @param  array  $filenames
     * @return void
     */
    public function handleUploadFinished($name, array $filenames = [])
    {
        /**
         * If the upload wasn't triggered via the drag and drop upload queue
         * then we ignore it since we don't want the files to appear in the
         * main image block.
         */
        if ($name != 'imageUploadQueue') {
            return;
        }

        if ($this->errorBag->count()) {
            unset($this->imageUploadQueue[0]);

            return;
        }
        foreach ($filenames as $key => $filename) {
            $file = TemporaryUploadedFile::createFromLivewire($filename);

            $this->images[] = [
                'thumbnail' => $file->temporaryUrl(),
                'sort_key'  => Str::random(),
                'filename'  => $filename,
                'original'  => $file->temporaryUrl(),
                'caption'   => null,
                'position'  => count($this->images) + 1,
                'preview'   => false,
                'edit'      => false,
                'primary'   => !count($this->images),
            ];

            unset($this->imageUploadQueue[$key]);
        }
    }

    /**
     * Method to handle reordering.
     *
     * @param  array  $sort
     * @return void
     */
    public function sort($sort)
    {
        foreach ($sort['items'] as $item) {
            $index = collect($this->images)->search(fn ($image) => $item['id'] == $image['sort_key']);
            $this->images[$index]['position'] = $item['order'];
        }

        $this->images = collect($this->images)->sortBy('position')->values()->toArray();
    }

    /**
     * Update all images based on changes that may of occured on the array.
     *
     * @return void
     */
    public function updateImages()
    {
        DB::transaction(function () {
            LogBatch::startBatch();

            $owner = $this->getMediaModel();

            // Need to find any images that have been deleted.
            // We need to also get a fresh instance of the relationship
            // as we may have changes that Livewire/Eloquent might not be aware of.
            $owner->refresh()->media->reject(function ($media) {
                $imageIds = collect($this->images)->pluck('id')->toArray();

                return in_array($media->id, $imageIds);
            })->each(function ($media) {
                $media->forceDelete();
            });

            foreach ($this->images as $key => $image) {
                $file = null;
                $imageEdited = false;

                // edited image
                if ($image['file'] ?? false && $image['file'] instanceof TemporaryUploadedFile) {
                    /** @var TemporaryUploadedFile $file */
                    $file = $image['file'];

                    if (isset($image['id'])) {
                        $owner->media()->find($image['id'])->delete();
                    }

                    unset($this->images[$key]['file']);

                    $imageEdited = true;
                }

                if (empty($image['id']) || $imageEdited) {
                    if (!$imageEdited) {
                        $file = TemporaryUploadedFile::createFromLivewire(
                            $image['filename']
                        );
                    }

                    $media = $owner->addMedia($file->getRealPath())
                        ->toMediaCollection('products');

                    activity()
                        ->performedOn($owner)
                        ->withProperties(['media' => $media->toArray()])
                        ->event('added_image')
                        ->useLog('getcandy')
                        ->log('added_image');

                    // Add ID for future and processing now.
                    $this->images[$key]['id'] = $media->id;

                    // reset image thumbnail
                    if ($imageEdited) {
                        $this->images[$key]['thumbnail'] = $media->getFullUrl('medium');
                        $this->images[$key]['original'] = $media->getFullUrl();
                    }

                    $image['id'] = $media->id;
                }

                $media = app(config('media-library.media_model'))::find($image['id']);

                $media->setCustomProperty('caption', $image['caption']);
                $media->setCustomProperty('primary', $image['primary']);
                $media->setCustomProperty('position', $image['position']);
                $media->save();
            }

            LogBatch::endBatch();
        });
    }

    /**
     * Sets an image to be primary and if one already exists will
     * remove it's primary state.
     *
     * @param  int|string  $imageKey
     * @return void
     */
    public function setPrimary($imageKey)
    {
        foreach ($this->images as $key => $image) {
            $this->images[$key]['primary'] = $imageKey == $key;
        }
    }

    /**
     * Method to handle firing of command to generate media conversions.
     *
     * @param  string  $id
     * @return void
     */
    public function regenerateConversions($id)
    {
        Artisan::call('media-library:regenerate --ids=' . $id);
        $this->notify(
            __('adminhub::partials.image-manager.remake_transforms.notify.success')
        );
    }

    /**
     * Removes an image from the array using it's sort key.
     *
     * @param  string  $sortKey
     * @return void
     */
    public function removeImage($sortKey)
    {
        $index = collect($this->images)->search(fn ($image) => $sortKey == $image['sort_key']);

        $image = $this->images[$index];

        unset($this->images[$index]);

        $this->images = array_values($this->images);

        // If this was a primary image and we have images left over
        // set the first image to be primary.
        if ($image['primary'] && count($this->images)) {
            $this->images[array_key_first($this->images)]['primary'] = true;
        }
    }
}
