<div
  x-data="{
    pond: {}
  }"
  x-on:remove-images.window="this.pond.removeFiles()"
  x-init="
    FilePond.registerPlugin(FilePondPluginImagePreview)
    FilePond.registerPlugin(FilePondPluginFileValidateSize)
    FilePond.registerPlugin(FilePondPluginFileValidateType);


    this.pond = FilePond.create($refs.input, {
      acceptedFileTypes: @if(is_array($filetypes)) {{ json_encode($filetypes, true) }} @else ['{{ $filetypes }}'] @endif,
      imagePreviewHeight: 100,
      maxFileSize: 'Number({{ max_upload_filesize() }}) * 1000',
      allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
      onprocessfile: (error, file) => {
        if (!error) {
          this.pond.removeFile(file.id)
        }
      },
      server: {
          process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
              @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress)
          },
          revert: (filename, load) => {
              @this.removeUpload('{{ $attributes['wire:model'] }}', filename, load)
          },
      },
    })

    this.addEventListener('pondReset', e => {
      this.pond.removeFiles();
    });
  "
  wire:ignore
>
  <input type="file" x-ref="input">
</div>
