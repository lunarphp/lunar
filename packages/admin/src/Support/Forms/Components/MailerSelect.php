<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MailerSelect extends Field
{
    protected string $view = 'lunarpanel::forms.components.mailer-select';

    public ?Model $context = null;

    public array $mailers = [];

    public ?string $selectedMailer = null;

    public ?string $additionalContent = null;

    public function context(Model $model)
    {
        $this->context = $model;

        return $this;
    }

    public function getMailers()
    {
        return collect(
            $this->mailers
        )->mapWithKeys(
            fn ($mailer) => [$mailer => Str::title(
                Str::snake(class_basename($mailer), ' ')
            )]
        );
    }

    public function getPreview()
    {
        if (! $this->selectedMailer || ! class_exists($this->selectedMailer)) {
            return '';
        }

        $mailer = new $this->selectedMailer($this->context);

        $mailer
            ->with('order', $this->context)
            ->with('content', '');

        return trim($mailer->render());
    }

    public function mailers(array $mailers)
    {
        $this->mailers = $mailers;
        $this->selectedMailer = $mailers[0] ?? null;

        return $this;
    }

    public function additionalContent(string $content = null)
    {
        $this->additionalContent = $content;

        return $this;
    }
}
