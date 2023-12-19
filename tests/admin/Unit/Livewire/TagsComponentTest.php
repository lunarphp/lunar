<?php

namespace Lunar\Tests\Admin\Unit\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use Lunar\Admin\Livewire\Components\Tags;
use Lunar\Admin\Support\Infolists\Components\Tags as ComponentsTags;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\Tag;

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('livewire.tags-component');

describe('tags component', function () {
    beforeEach(function () {
        $this->asStaff();

        $currency = Currency::factory()->create();

        $tags = Tag::factory(5)->create();

        $order = Order::factory()->create([
            'currency_code' => $currency->code,
        ]);

        $order->tags()->sync($tags);

        $this->taggable = $order;
    });

    test('can render', function () {
        $tags = $this->taggable->tags()->get();

        Livewire::test(Tags::class, [
            'taggable' => $this->taggable,
        ])
            ->assertStatus(200)
            ->assertSet('taggable', $this->taggable)
            ->assertSet('tags', $tags->pluck('value')->toArray())
            ->assertActionExists('save');
    });

    test('can add tag', function () {
        $tag = Str::random(5);

        $tags = $this->taggable->tags()->get()->pluck('value');
        $tagsCount = count($tags);

        Livewire::test(Tags::class, [
            'taggable' => $this->taggable,
        ])
            ->set('tags', $tags->push($tag)->toArray())
            ->callAction('save');

        $tags = $this->taggable->tags()->get();

        expect($tags->count())
            ->toBeGreaterThan($tagsCount);

        expect($tags->toArray())
            ->toHaveCount($tagsCount + 1);
    });

    test('can render in infolist', function () {
        Livewire::test(TestTagsComponentInInfolist::class, [
            'order' => $this->taggable,
        ])
            ->assertStatus(200)
            ->assertSeeLivewire(Tags::class);
    });

});

class TestTagsComponentInInfolist extends Component implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    public $order;

    public function orderInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->order)
            ->schema([
                ComponentsTags::make('tagging'),
            ]);
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                {{ $this->orderInfolist }}
            </div>
        HTML;
    }
}
