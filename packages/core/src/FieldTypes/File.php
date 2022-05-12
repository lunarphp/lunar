<?php

namespace GetCandy\FieldTypes;

use GetCandy\Base\FieldType;
use JsonSerializable;
use Livewire\TemporaryUploadedFile;

class File implements FieldType, JsonSerializable
{
    /**
     * @var string
     */
    protected $value;

    /**
     * The config to use.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Serialize the class.
     *
     * @return string
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    /**
     * Create a new instance of Text field type.
     *
     * @param  string  $value
     */
    public function __construct($value = '', array $config = [])
    {
        $this->withConfig($config)->setValue($value);
    }

    /**
     * Returns the value when accessed as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Return the value of this field.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  string  $value
     */
    public function setValue($value)
    {
        if ($value instanceof TemporaryUploadedFile) {
            $disk = $this->config['disk'] ?? config('filesystems.default');

            $this->value = [
                'disk' => $disk,
                'path' => $value->store($this->config['path'] ?? null, $disk),
                'filename' => $value->getClientOriginalName(),
                'mime_type' => $value->getMimeType(),
            ];
        } else {
            $this->value = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('adminhub::fieldtypes.file.label');
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsView(): string
    {
        return 'adminhub::field-types.file.settings';
    }

    /**
     * {@inheritDoc}
     */
    public function getView(): string
    {
        return 'adminhub::field-types.file.view';
    }

    public function withConfig($config = []): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'view'    => 'adminhub::field-types.file',
            'options' => [
                'path' => 'nullable',
                'disk' => 'nullable',
            ],
        ];
    }
}
