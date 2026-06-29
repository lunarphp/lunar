<?php

namespace Lunar\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\Concerns\AppliesToExistingSelect;
use Lunar\Filament\Forms\Components\Concerns\ExcludesAttachedRecords;
use Lunar\Filament\Forms\Components\Concerns\SearchesLunarRecords;

/**
 * A searchable Product picker. Uses the shared {@see RecordSearch}
 * backend so Scout and the translated-attribute DB fallback both work
 * without per-call-site wiring.
 */
class ProductSelect extends Select
{
    use AppliesToExistingSelect;
    use ExcludesAttachedRecords;
    use SearchesLunarRecords;

    protected bool $showSku = false;

    protected bool $showThumbnail = false;

    /**
     * @var array<int, string>|null
     */
    protected ?array $statusScope = null;

    protected ?Channel $channelScope = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.product.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.product.placeholder'));
        $this->searchable();
        $this->getSearchResultsUsing(fn (string $search): array => $this->searchLunarRecords($search));
        $this->getOptionLabelUsing(function ($value): ?string {
            $model = $this->lunarModel();
            $record = $model::find($value);

            return $record ? $this->optionLabel($record) : null;
        });
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Product::class;
    }

    public function showSku(bool $condition = true): static
    {
        $this->showSku = $condition;

        return $this;
    }

    public function showThumbnail(bool $condition = true): static
    {
        $this->showThumbnail = $condition;

        return $this;
    }

    /**
     * @param  string|array<int, string>  $statuses
     */
    public function scopeStatus(string|array $statuses): static
    {
        $this->statusScope = is_array($statuses) ? $statuses : [$statuses];

        $statusScope = $this->statusScope;

        $this->modifyOptionsQueryUsing(function ($query) use ($statusScope) {
            if (method_exists($query, 'whereIn')) {
                return $query->whereIn('status', $statusScope);
            }

            return $query;
        });

        return $this;
    }

    public function withinChannel(Channel|Closure $channel): static
    {
        $this->channelScope = $channel instanceof Closure ? $this->evaluate($channel) : $channel;

        $resolvedChannel = $this->channelScope;

        $this->modifyOptionsQueryUsing(function ($query) use ($resolvedChannel) {
            if (! $resolvedChannel || ! method_exists($query, 'whereHas')) {
                return $query;
            }

            return $query->whereHas('channels', fn ($q) => $q->where('channels.id', $resolvedChannel->getKey()));
        });

        return $this;
    }

    public function optionLabel(Model $record): string
    {
        $name = $record->translate('name');

        if ($this->showSku) {
            $sku = optional($record->variants()->first())->sku;

            if ($sku) {
                return "{$name} — {$sku}";
            }
        }

        return $name;
    }
}
