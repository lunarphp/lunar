<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;
use Lunar\Base\BaseModel;

class ModelUrl extends Component
{
    /**
     * The instance of the URL generator.
     *
     * @var mixed
     */
    protected $generator = null;

    /**
     * The model which has the routes.
     *
     * @var BaseModel|null
     */
    protected ?BaseModel $model = null;

    /**
     * Whether the preview URL should be returned.
     *
     * @var bool
     */
    public bool $preview = false;

    public function __construct(BaseModel $model = null, bool $preview = false)
    {
        $generators = config('lunar-hub.storefront.model_routes', []);

        $this->generator = $generators[get_class($model)] ?? null;
        $this->preview = $preview;
        $this->model = $model;
    }

    public function getUrl()
    {
        if (!$this->generator) {
            return;
        }

        $class = app($this->generator);

        return $this->preview ? $class->preview($this->model) : $class->view($this->model);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.model-url', [
            'url' => $this->getUrl(),
        ]);
    }
}
