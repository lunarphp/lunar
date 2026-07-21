<?php

namespace Lunar\Panel\Sections\Catalog;

use Lunar\Core\Models\Product;
use Lunar\Panel\Actions\PageAction;
use Lunar\Panel\Support\Position;

/**
 * Duplicate the product being edited — the panel's first first-party page
 * action, delegating to core's DuplicateProduct through a dedicated
 * endpoint. Lives in the page-header ellipsis.
 */
class DuplicateProductPageAction extends PageAction
{
    public function key(): string
    {
        return 'duplicate';
    }

    public function label(): string
    {
        return __('panel::products.action_duplicate');
    }

    public function icon(): ?string
    {
        return 'copy';
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    public function method(): string
    {
        return 'post';
    }

    public function permission(): ?string
    {
        return CatalogSection::PRODUCTS_PERMISSION;
    }

    public function confirmationMessage(): ?string
    {
        return __('panel::products.confirm_duplicate');
    }

    public function url(mixed $context = null): ?string
    {
        return $context instanceof Product
            ? route('panel.products.duplicate', $context)
            : null;
    }
}
