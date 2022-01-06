<?php

namespace GetCandy\Hub\Views\Components\ActivityLog;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Images extends Component
{
    public Collection $batch;

    public function __construct(Collection $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        $mediaIds = $this->batch->map(function ($log) {
            return $log->properties['media']['id'] ?? null;
        });

        $media = Media::findMany($mediaIds);

        return view('adminhub::components.activity-log.images', [
            'images' => $this->batch->map(function ($log) use ($media) {
                return [
                    'log'   => $log,
                    'media' => $media->first(fn ($image) => $image->id == ($log->properties['media']['id'] ?? null)),
                ];
            }),
        ]);
    }
}
