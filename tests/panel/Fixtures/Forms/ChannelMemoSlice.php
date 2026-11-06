<?php

namespace Lunar\Tests\Panel\Fixtures\Forms;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Channel;
use Lunar\Panel\Forms\FormSlice;
use RuntimeException;

/**
 * A plain form slice on Channel for the settings-form tests. Channels have
 * no spare column, so the memo lives in a static store keyed by channel id;
 * the point is the composition, not the storage.
 */
class ChannelMemoSlice extends FormSlice
{
    /** @var array<int, string|null> */
    public static array $memos = [];

    /** @var array<int, array<string, mixed>> */
    public static array $commits = [];

    public function model(): string
    {
        return Channel::class;
    }

    public function key(): string
    {
        return 'notes';
    }

    public function fields(Model $record): array
    {
        return ['memo'];
    }

    public function currentValues(Model $record): array
    {
        return ['memo' => self::$memos[$record->getKey()] ?? null];
    }

    public function rules(Model $record): array
    {
        return ['memo' => ['required', 'string', 'max:20']];
    }

    public function commit(Model $record, array $values): void
    {
        if (($values['memo'] ?? null) === 'boom') {
            throw new RuntimeException('Channel memo slice commit failed.');
        }

        self::$commits[] = $values;
        self::$memos[$record->getKey()] = $values['memo'] ?? null;
    }

    public function labels(): array
    {
        return ['memo' => 'Memo'];
    }
}
