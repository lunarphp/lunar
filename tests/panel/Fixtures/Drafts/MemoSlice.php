<?php

namespace Lunar\Tests\Panel\Fixtures\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Drafts\DraftSlice;
use Lunar\Panel\Models\EditDraft;
use RuntimeException;

/**
 * A first-party-style draft slice on Customer for the form slice tests: one
 * `memo` field stored under the customer's meta column, with a commit that
 * can be made to fail and a discard hook that records its calls.
 */
class MemoSlice extends DraftSlice
{
    /** @var array<int, string> */
    public static array $committed = [];

    /** @var array<int, int> */
    public static array $discarded = [];

    public function __construct(protected UpdatesCustomer $updatesCustomer) {}

    public function model(): string
    {
        return Customer::class;
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
        /** @var Customer $record */
        return ['memo' => $record->meta['memo'] ?? null];
    }

    public function normalize(array $data): array
    {
        if (array_key_exists('memo', $data) && $data['memo'] === '') {
            $data['memo'] = null;
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        return ['memo' => ['nullable', 'string', 'max:20']];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Customer $record */
        if (($values['memo'] ?? null) === 'boom') {
            throw new RuntimeException('Memo slice commit failed.');
        }

        self::$committed[] = 'slice';

        $this->updatesCustomer->execute($record, [
            'meta' => [...($record->meta?->getArrayCopy() ?? []), 'memo' => $values['memo'] ?? null],
        ]);
    }

    public function labels(): array
    {
        return ['memo' => 'Memo'];
    }

    public function discard(Model $record, EditDraft $draft): void
    {
        self::$discarded[] = (int) $draft->getKey();
    }
}
