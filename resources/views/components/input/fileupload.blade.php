<div
  x-data
  x-on:remove-images.window="Pond.removeFiles()"
  x-init="
    FilePond.registerPlugin(FilePondPluginImagePreview)
    FilePond.registerPlugin(FilePondPluginFileValidateSize)
    FilePond.registerPlugin(FilePondPluginFileValidateType);

    FilePond.setOptions({
        acceptedFileTypes: @if(is_array($filetypes)) {{ json_encode($filetypes, true) }} @else ['{{ $filetypes }}'] @endif,
        imagePreviewHeight: 100,
        maxFileSize: 'Number({{ max_upload_filesize() }}) * 1000',
        allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
        onprocessfile: (error, file) => {
          if (!error) {
            Pond.removeFile(file.id)
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
    });
    Pond = FilePond.create($refs.input)

    this.addEventListener('pondReset', e => {
        Pond.removeFiles();
    });
  "
  wire:ignore
>
  <input type="file" x-ref="input">
</div>
