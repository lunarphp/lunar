<?php

namespace Lunar\Base;

use Spatie\Image\Manipulations;

class StandardMediaConversions
{
    public function apply(BaseModel $model)
    {
        $conversions = [
            'zoom' => [
                'width' => 500,
                'height' => 500,
            ],
            'large' => [
                'width' => 800,
                'height' => 800,
            ],
            'medium' => [
                'width' => 500,
                'height' => 500,
            ],
        ];

        foreach ($conversions as $key => $conversion) {
            $model->addMediaConversion($key)
                ->fit(
                    Manipulations::FIT_FILL,
                    $conversion['width'],
                    $conversion['height']
                )->keepOriginalImageFormat();
        }
    }
}
