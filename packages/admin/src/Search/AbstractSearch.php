<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\Base\SearchInterface;

abstract class AbstractSearch implements SearchInterface
{
    public $engine;

    public $options;

    public $term;

    public function __construct()
    {
        $this->driver = config('scout.driver');
    }

    abstract public function getModel();

    public function build($engine, $term, $options)
    {
        // $this->engine = $engine;
        // $this->term = $term;
        // $this->options = $options;

        if ($this->driver == 'meilisearch') {
            return (new Meilisearch)->build($this);
        }

        return $engine->search($this->term, $options);
    }

    abstract public function search($term, $options = [], $perPage = 25, $page = 1);
}
