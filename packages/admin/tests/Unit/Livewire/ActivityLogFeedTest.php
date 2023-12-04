<?php

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use Lunar\Admin\Livewire\Components\ActivityLogFeed;
use Lunar\Admin\Support\Infolists\Components\Timeline;
use Lunar\Models\Currency;
use Lunar\Models\Order;

uses(\Lunar\Admin\Tests\Unit\Livewire\TestCase::class)
    ->group('livewire.activity-feed');

describe('activity feed component', function () {
    beforeEach(function () {
        $this->asStaff();

        $currency = Currency::factory()->create();

        $this->subject = Order::factory()->create([
            'currency_code' => $currency->code,
        ]);
    });

    test('can render', function () {
        Livewire::test(ActivityLogFeed::class, [
            'subject' => $this->subject,
        ])
            ->assertStatus(200)
            ->assertSet('subject', $this->subject)
            ->assertFormExists()
            ->assertFormFieldExists('comment')
            ->assertActionExists('addComment')
            ->assertSee(__('lunarpanel::components.activity-log.partials.create.created', [
                'model' => str($this->subject::class)->classBasename()->snake(' ')->ucfirst(),
            ]));
    });

    test('can add comment', function () {
        $comment = Str::random();

        Livewire::test(ActivityLogFeed::class, [
            'subject' => $this->subject,
        ])
            ->assertStatus(200)
            ->callAction('addComment')
            ->assertHasFormErrors(['comment' => 'required'])
            ->fillForm([
                'comment' => $comment,
            ])
            ->callAction('addComment')
            ->assertHasNoFormErrors();

        $commentEntry = $this->subject->activities()->whereEvent('comment')->first();

        expect($commentEntry->getExtraProperty('content'))
            ->toEqual($comment);
    });

    test('can render in infolist', function () {
        Livewire::test(TestActivityFeedComponentInInfolist::class, [
            'order' => $this->subject,
        ])
            ->assertStatus(200)
            ->assertSeeLivewire(ActivityLogFeed::class);
    });

});

class TestActivityFeedComponentInInfolist extends Component implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    public $order;

    public function orderInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->order)
            ->schema([
                Timeline::make('timeline'),
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
