<?php

use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Lunar\Filament\Tables\Columns\ThumbnailImageColumn;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

function thumbnailRecord(string $url): Model
{
    return new class(['url' => $url]) extends Model
    {
        protected $guarded = [];

        public function getThumbnailImage(): string
        {
            return $this->url;
        }
    };
}

it('keeps getImageUrl compatible with the parent signature', function () {
    $parent = new ReflectionMethod(ImageColumn::class, 'getImageUrl');
    $child = new ReflectionMethod(ThumbnailImageColumn::class, 'getImageUrl');

    expect($child->getNumberOfParameters())->toBeGreaterThanOrEqual($parent->getNumberOfParameters());
});

it('falls back to the record thumbnail', function () {
    $column = ThumbnailImageColumn::make('thumbnail')
        ->record(thumbnailRecord('https://example.com/record.jpg'));

    expect($column->getImageUrl())->toBe('https://example.com/record.jpg');
});

it('resolves the thumbnail through the configured callback', function () {
    $column = ThumbnailImageColumn::make('thumbnail')
        ->record(thumbnailRecord('https://example.com/record.jpg'))
        ->resolveThumbnailUrlUsing(fn (?Model $record): string => 'https://example.com/resolved.jpg');

    expect($column->getImageUrl(null, null))->toBe('https://example.com/resolved.jpg');
});
