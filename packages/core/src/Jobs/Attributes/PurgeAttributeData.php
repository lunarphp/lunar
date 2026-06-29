<?php

namespace Lunar\Core\Jobs\Attributes;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Facades\AttributeManifest;

class PurgeAttributeData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $attributeId,
    ) {}

    /**
     * Strip the deleted attribute's id key out of every attributable model's attribute_data.
     */
    public function handle(): void
    {
        $key = (string) $this->attributeId;

        foreach (AttributeManifest::getTypes() as $modelClass) {
            $resolved = $modelClass;

            $resolved::query()
                ->whereNotNull('attribute_data')
                ->chunkById(100, function (Collection $records) use ($key, $resolved): void {
                    foreach ($records as $record) {
                        $raw = json_decode((string) $record->getRawOriginal('attribute_data'), true);

                        if (! is_array($raw) || ! array_key_exists($key, $raw)) {
                            continue;
                        }

                        unset($raw[$key]);

                        $resolved::query()
                            ->whereKey($record->getKey())
                            ->toBase()
                            ->update(['attribute_data' => json_encode($raw)]);
                    }
                });
        }
    }
}
