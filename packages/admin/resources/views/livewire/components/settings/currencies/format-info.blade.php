<div class="p-6 space-y-2 text-sm">
  @foreach(__('adminhub::settings.currencies.form.format_help_text') as $value)
    <p>{!! $value !!}</p>
  @endforeach
</div>
