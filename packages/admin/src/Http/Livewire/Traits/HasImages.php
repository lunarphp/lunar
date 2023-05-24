<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\FileUploadConfiguration;
use Livewire\TemporaryUploadedFile;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    /**
     * An array of selected images.
     */
    public array $selectedImages = [];

    /**
     * Whether to shoe the image select modal dialog.
     */
    public bool $showImageSelectModal = false;

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
            'imageUploadQueue.*' => 'image|max:'.max_upload_filesize(),
            'images.*.caption' => 'nullable|string',
            'showImageSelectModal' => 'boolean',
            'selectedImages' => 'nullable|array|min:0',
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

        $this->images = $owner->getMedia('images')->mapWithKeys(function ($media) {
            $key = Str::random();

            return [
                $key => [
                    'id' => $media->id,
                    'sort_key' => $key,
                    'thumbnail' => $media->getFullUrl('medium'),
                    'original' => $media->getFullUrl(),
                    'preview' => false,
                    'edit' => false,
                    'caption' => $media->getCustomProperty('caption'),
                    'primary' => $media->getCustomProperty('primary'),
                    'position' => $media->getCustomProperty('position', 1),
                ],
            ];
        })->sortBy('position')->toArray();
    }

    /**
     * Listener for when new images are uploaded.
     *
     * @return void
     */
    public function updatedImages($value, $key)
    {
        $this->validate($this->hasImagesValidationRules());

        [$index, $field] = explode('.', $key);
        if ($field == 'primary' && $value) {
            // Make sure other defaults are unchecked...
            $this->images = collect($this->images)->map(function ($image, $imageIndex) use ($index) {
                if ($index != $imageIndex) {
                    $image['primary'] = false;
                } else {
                    $image['primary'] = true;
                }

                return $image;
            })->toArray();
        }
    }

    /**
     * Return the id's of the current images.
     *
     * @return array
     */
    public function getCurrentImageIdsProperty()
    {
        return collect($this->images)->pluck('id')->filter()->toArray();
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

        foreach ($filenames as $fileKey => $filename) {
            $file = TemporaryUploadedFile::createFromLivewire($filename);

            $sortKey = Str::random();

            $this->images[$sortKey] = [
                'thumbnail' => $file->temporaryUrl(),
                'sort_key' => $sortKey,
                'filename' => $filename,
                'original' => $file->temporaryUrl(),
                'caption' => null,
                'position' => collect($this->images)->max('position') + 1,
                'preview' => false,
                'edit' => false,
                'primary' => ! count($this->images),
            ];

            unset($this->imageUploadQueue[$fileKey]);
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

        $this->images = collect($this->images)->sortBy('position')->toArray();
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
            $owner->refresh()->getMedia('images')->reject(function ($media) {
                $imageIds = collect($this->images)->pluck('id')->toArray();

                return in_array($media->id, $imageIds);
            })->each(function ($media) {
                $media->forceDelete();
            });

            $variants = collect();

            if ($owner->variants) {
                $variants = $owner->variants->load('images');
            }

            foreach ($this->images as $key => $image) {
                $file = null;
                $imageEdited = false;
                $previousMediaId = false;

                // edited image
                if ($image['file'] ?? false && $image['file'] instanceof TemporaryUploadedFile) {
                    /** @var TemporaryUploadedFile $file */
                    $file = $image['file'];

                    if (isset($image['id'])) {
                        $previousMediaId = $image['id'];
                    }

                    unset($this->images[$key]['file']);

                    $imageEdited = true;
                }

                if (empty($image['id']) || $imageEdited) {
                    if (! $imageEdited) {
                        $file = TemporaryUploadedFile::createFromLivewire(
                            $image['filename']
                        );
                    }

                    // after editing few times the name will get longer and eventually failed to upload
                    $filename = Str::of($file->getFilename())
                        ->beforeLast('.')
                        ->substr(0, 128)
                        ->append('.', $file->getClientOriginalExtension());

                    if (FileUploadConfiguration::isUsingS3()) {
                        $media = $owner->addMediaFromDisk($file->getRealPath())
                            ->usingFileName($filename)
                            ->toMediaCollection('images');
                    } else {
                        $media = $owner->addMedia($file->getRealPath())
                            ->usingFileName($filename)
                            ->toMediaCollection('images');
                    }

                    activity()
                        ->performedOn($owner)
                        ->withProperties(['media' => $media->toArray()])
                        ->event('added_image')
                        ->useLog('lunar')
                        ->log('added_image');

                    // Add ID for future and processing now.
                    $this->images[$key]['id'] = $media->id;

                    // reset image thumbnail
                    if ($imageEdited) {
                        $this->images[$key]['thumbnail'] = $media->getFullUrl('medium');
                        $this->images[$key]['original'] = $media->getFullUrl();

                        // link variants image to the new media
                        if ($previousMediaId) {
                            $variants->each(function ($variant) use ($previousMediaId, $media) {
                                $variantMedia = $variant->images->where('id', $previousMediaId)->first();

                                if ($variantMedia) {
                                    $variant->images()->attach($media, [
                                        'primary' => $variantMedia->pivot->primary,
                                    ]);
                                }
                            });

                            $owner->media()->find($previousMediaId)->delete();
                        }
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
        Artisan::call('media-library:regenerate', [
            '--ids' => $id,
            '--force' => true,
        ]);

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
        if (! isset($this->images[$sortKey])) {
            return;
        }

        $image = $this->images[$sortKey];

        unset($this->images[$sortKey]);

        // If this was a primary image and we have images left over
        // set the first image to be primary.
        if ($image['primary'] && count($this->images)) {
            $this->images[array_key_first($this->images)]['primary'] = true;
        }
    }

    public function selectImages()
    {
        $chosen = Media::findMany($this->selectedImages);

        $images = collect($this->images);

        $maxPosition = $images->max('position');

        foreach ($chosen as $media) {
            $key = Str::random();
            $this->images[$key] = [
                'id' => $media->id,
                'thumbnail' => $media->getUrl('small'),
                'sort_key' => $key,
                'filename' => $media->file_name,
                'original' => $media->getUrl(),
                'caption' => null,
                'position' => $maxPosition + 1,
                'preview' => false,
                'edit' => false,
                'primary' => ! count($this->images),
            ];
        }

        $hasPrimary = $images->search(fn ($image) => $image['primary'] === true);

        if ($hasPrimary === false) {
            $this->images[array_key_first($this->images)]['primary'] = true;
        }

        $this->selectedImages = [];
        $this->showImageSelectModal = false;
    }
}
