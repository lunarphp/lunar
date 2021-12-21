<div
    x-ref="input"
    x-data="{
        value: @entangle($attributes->wire('model'))
    }"
    x-init="
      {{ $instanceId }} = new Quill($refs.editor, {
        theme: 'snow'
      })

      {{ $instanceId }}.on('text-change', () => {
        $dispatch('quill-input', {{ $instanceId }}.root.innerHTML)
      })
    "
    x-on:quill-input="value = $event.detail"
>
  <div wire:ignore>
      <div x-ref="editor">{!! $initialValue !!}</div>
  </div>
</div>
