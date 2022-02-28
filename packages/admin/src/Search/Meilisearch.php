<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\Base\SearchInterface;

class Meilisearch
{
    public function search()
    {
    }

    public function build(SearchInterface $search)
    {
        $model = $search->getModel();

        $incomingOptions = $search->options;

        $filters = collect();

        $options = [
            // 'limit' => $this->perPage,
            // 'offset' => ($this->perPage * $this->page) - $this->perPage,
            'facetsDistribution' => (new $model)->getFilterableAttributes(),
            // 'filter' => null,
            // 'sort' => [$this->sort],
        ];

        foreach ($incomingOptions['filters'] ?? [] as $field => $values) {
            if (empty($values)) {
                continue;
            }

            $filterString = collect($values)->map(function ($value) use ($field) {
                return $field.' = "'.$value.'"';
            })->join('OR');

            $filters->push('('.$filterString.')');
        }

        if ($filters->count()) {
            $options['filter'] = $filters->join(' AND ');
        }

        return $search->engine->search($search->term, $options);
    }
}
