<div
    x-ref="input"
    x-data="{
        value: @entangle($attributes->wire('model')),
        init() {
          {{ $instanceId }} = new Quill($refs.editor, {{ json_encode($options) }})

          {{ $instanceId }}.on('text-change', () => {
            $dispatch('quill-input', {{ $instanceId }}.root.innerHTML)
          })
        }
    }"
    x-on:quill-input="value = $event.detail"
    wire:ignore
>
  <div>
      <div x-ref="editor">{!! $initialValue !!}</div>
  </div>
</div>
