<?php

namespace GetCandy\Hub\Editing;

class ProductSection
{
    public $sections;

    public function __construct()
    {
        $this->sections = collect([
            [
                'handle'    => 'basic-information',
                'component' => 'hub.components.products.editing.basic-information',
            ],
            [
                'handle'    => 'attributes',
                'component' => 'hub.components.products.editing.attributes',
            ],
            [
                'handle'    => 'images',
                'component' => 'hub.components.products.editing.images',
            ],
            [
                'handle'    => 'channels',
                'component' => 'hub.components.products.editing.availability',
            ],
            // [
            //     'handle' => 'customer-groups',
            //     'component' => 'hub.components.products.editing.customer-groups',
            // ],
            // [
            //     'handle' => 'variations',
            //     'component' => 'hub.components.products.editing.variations',
            // ],
        ]);
    }

    public function add($section)
    {
        $this->sections->push($section);
    }
}
