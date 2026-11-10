<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Lunar\Filament\Tables\Columns\ThumbnailImageColumn;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

function thumbnailRecord(string $url): Model
{
    return new class(['url' => $url]) extends Model
    {
        protected $guarded = [];

        public function getThumbnailImage(): ?string
        {
            return $this->url !== '' ? $this->url : null;
        }
    };
}

function makeThumbnailColumn(): ThumbnailImageColumn
{
    $column = new class('thumbnail') extends ThumbnailImageColumn
    {
        protected function getStateCacheKey(): ?string
        {
            return null;
        }
    };

    return $column->configure();
}

it('falls back to the record thumbnail', function () {
    $column = makeThumbnailColumn()
        ->record(thumbnailRecord('https://example.com/record.jpg'));

    expect($column->getState())->toBe('https://example.com/record.jpg')
        ->and($column->getImageUrl($column->getState()))->toBe('https://example.com/record.jpg')
        ->and($column->toEmbeddedHtml())->toContain('src="https://example.com/record.jpg"');
});

it('resolves the thumbnail through the configured callback', function () {
    $column = makeThumbnailColumn()
        ->record(thumbnailRecord('https://example.com/record.jpg'))
        ->resolveThumbnailUrlUsing(fn (?Model $record): string => 'https://example.com/resolved.jpg');

    expect($column->getState())->toBe('https://example.com/resolved.jpg')
        ->and($column->toEmbeddedHtml())->toContain('src="https://example.com/resolved.jpg"');
});

it('uses the default image url when the thumbnail is blank', function () {
    $column = makeThumbnailColumn()
        ->record(thumbnailRecord(''))
        ->defaultImageUrl('https://example.com/default.jpg');

    expect($column->getState())->toBeNull()
        ->and($column->toEmbeddedHtml())->toContain('src="https://example.com/default.jpg"');
});

it('passes a storage path through the parent image url', function () {
    Storage::fake('public');
    Storage::disk('public')->put('images/thumb.jpg', 'image');

    $column = makeThumbnailColumn()
        ->disk('public')
        ->record(thumbnailRecord(''))
        ->resolveThumbnailUrlUsing(fn (): string => 'images/thumb.jpg');

    expect($column->getState())->toBe('images/thumb.jpg')
        ->and($column->getImageUrl($column->getState()))->toBe(Storage::disk('public')->url('images/thumb.jpg'))
        ->and($column->toEmbeddedHtml())->toContain('src="'.e(Storage::disk('public')->url('images/thumb.jpg')).'"');
});
