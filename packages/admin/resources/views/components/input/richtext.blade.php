<div
    x-ref="input"
    x-data="{
        value: @entangle($attributes->wire('model')),
        init() {
          {{ $instanceId }} = new Quill($refs.editor, {
            theme: 'snow',
            modules: {
              toolbar: [
                [{ 'size': [] }],
                [ 'bold', 'italic', 'underline', 'strike' ],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'super' }, { 'script': 'sub' }],
                [{ 'header': '1' }, { 'header': '2' }, 'blockquote', 'code-block' ],
                [{ 'list': 'ordered' }, { 'list': 'bullet'}, { 'indent': '-1' }, { 'indent': '+1' }],
                [ {'direction': 'rtl'}, { 'align': [] }],
                [ 'link', 'image', 'video', 'formula' ],
                [ 'clean' ]
              ]
            }
          })

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
