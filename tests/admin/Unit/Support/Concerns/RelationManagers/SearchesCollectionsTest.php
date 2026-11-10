<?php

use Illuminate\Support\Facades\DB;
use Lunar\Admin\Support\Concerns\RelationManagers\SearchesCollections;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Contracts\Collection as CollectionContract;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('support.relationManagers');

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $this->searcher = new class
    {
        use SearchesCollections;

        public static function search(string $search, int $limit): array
        {
            return self::getCollectionSearchResults($search, $limit);
        }

        public static function label(CollectionContract $record): string
        {
            return self::getCollectionOptionLabel($record);
        }

        public static function path(CollectionContract $record): string
        {
            return self::getCollectionPath($record);
        }
    };
});

function createCollectionWithPath(string $name, ?CollectionGroup $group = null, ?Collection $parent = null): Collection
{
    $collection = Collection::factory()->create([
        'collection_group_id' => $group?->getKey() ?? CollectionGroup::factory(),
        'attribute_data' => collect([
            'name' => new Text($name),
        ]),
    ]);

    if ($parent) {
        $collection->appendToNode($parent)->save();
    }

    return $collection->refresh();
}

it('labels a collection with its group and ancestors', function () {
    $group = CollectionGroup::factory()->create(['name' => 'Main']);

    $clothing = createCollectionWithPath('Clothing', $group);
    $tops = createCollectionWithPath('Tops', $group, $clothing);

    expect($this->searcher::label($tops))->toEqual('Main > Clothing > Tops');
    expect($this->searcher::path($tops))->toEqual('Main > Clothing');
});

it('labels a root collection with just its group', function () {
    $group = CollectionGroup::factory()->create(['name' => 'Main']);

    $clothing = createCollectionWithPath('Clothing', $group);

    expect($this->searcher::label($clothing))->toEqual('Main > Clothing');
});

it('limits how many search results it labels', function () {
    $group = CollectionGroup::factory()->create(['name' => 'Main']);

    for ($i = 0; $i < 10; $i++) {
        createCollectionWithPath("Tops {$i}", $group);
    }

    expect($this->searcher::search('Tops', 4))->toHaveCount(4);
});

it('does not query per collection when labelling search results', function () {
    $group = CollectionGroup::factory()->create(['name' => 'Main']);

    $clothing = createCollectionWithPath('Clothing', $group);

    for ($i = 0; $i < 10; $i++) {
        createCollectionWithPath("Tops {$i}", $group, $clothing);
    }

    DB::enableQueryLog();

    $results = $this->searcher::search('Tops', 50);

    $queries = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($results)->not->toBeEmpty();

    // Lazy loaded, the group and ancestors behind each label would cost two
    // queries per result on top of the search itself.
    expect($queries)->toBeLessThan(10);
});
