<?php

namespace Lunar\Panel\Http\Requests\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Lunar\Core\DataObjects\CouponBatch;

/**
 * Generates a batch of codes: a quantity, or one code per email address
 * pasted or uploaded as CSV.
 */
class DiscountCouponGenerateRequest extends FormRequest
{
    public const MAX_QUANTITY = 100000;

    /**
     * @var list<string>
     */
    protected array $emails = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', 'in:quantity,emails'],
            'quantity' => ['exclude_unless:source,quantity', 'required', 'integer', 'min:1', 'max:'.self::MAX_QUANTITY],
            'emails' => ['exclude_unless:source,emails', 'nullable', 'string'],
            'emails_file' => ['exclude_unless:source,emails', 'nullable', 'file', 'mimes:csv,txt', 'max:10240'],
            'prefix' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'length' => ['required', 'integer', 'min:6', 'max:32'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || $this->input('source') !== 'emails') {
                return;
            }

            $this->collectEmails($validator);
        }];
    }

    public function batch(): CouponBatch
    {
        $options = [
            'length' => (int) $this->validated('length'),
            'prefix' => (string) $this->validated('prefix'),
            'maxUses' => $this->validated('max_uses') === null ? null : (int) $this->validated('max_uses'),
        ];

        return $this->validated('source') === 'emails'
            ? CouponBatch::forEmails($this->emails, ...$options)
            : CouponBatch::ofQuantity((int) $this->validated('quantity'), ...$options);
    }

    /**
     * Anything with an @ is taken as an address and must be valid; other
     * cells, like a CSV header or a name column, are skipped.
     */
    protected function collectEmails(Validator $validator): void
    {
        $file = $this->file('emails_file');
        $text = $this->input('emails', '')."\n".($file instanceof UploadedFile ? $file->getContent() : '');

        $tokens = preg_split('/[\s,;"\']+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $candidates = array_filter($tokens, fn (string $token) => str_contains($token, '@'));
        $invalid = array_filter($candidates, fn (string $token) => filter_var($token, FILTER_VALIDATE_EMAIL) === false);

        if ($invalid !== []) {
            $validator->errors()->add('emails', __('panel::discounts.coupons_generate_invalid_emails', [
                'emails' => implode(', ', array_slice(array_values($invalid), 0, 3)),
            ]));

            return;
        }

        if ($candidates === []) {
            $validator->errors()->add('emails', __('panel::discounts.coupons_generate_no_emails'));

            return;
        }

        if (count($candidates) > self::MAX_QUANTITY) {
            $validator->errors()->add('emails', __('panel::discounts.coupons_generate_too_many_emails', ['max' => number_format(self::MAX_QUANTITY)]));

            return;
        }

        $this->emails = array_values($candidates);
    }
}
