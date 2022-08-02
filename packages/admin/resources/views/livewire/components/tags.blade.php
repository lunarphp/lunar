<div class="relative mt-1"
     x-data="{
         tags: @entangle('tags'),
         value: @entangle('searchTerm'),
     
         addTag(newTag) {
             let trimTag = newTag.toUpperCase().trim();
     
             if (!this.tags.includes(trimTag)) {
                 this.tags.push(trimTag);
             }
     
             this.value = '';
         },
     
         removeTag(index) {
             this.tags.splice(index, 1);
         },
     
         removeLastTag(key) {
             if (key === 'Backspace' && !this.value) {
                 this.tags.splice(this.tags.length - 1, 1);
             }
         }
     }"
     x-init="$watch('value', (newValue) => {
         let trimTag = newValue.trim()
     
         if (trimTag.endsWith(',')) {
             let newTag = trimTag.replace(',', '')
     
             addTag(newTag)
         }
     })">
    <div
         class="flex flex-wrap items-center w-full gap-2 px-2 border border-gray-300 rounded-md shadow-sm form-input disabled:opacity-50 disabled:cursor-not-allowed">
        <template x-for="(tag, tagIndex) in tags"
                  :key="tagIndex">
            <span class="flex items-center px-2 py-1 space-x-1 text-sm leading-none bg-purple-100 rounded">
                <span x-text="tag"
                      class="text-purple-700"></span>

                <button type="button"
                        @click.prevent="removeTag(tagIndex)"
                        class="text-purple-400 hover:text-purple-900">
                    <x-hub::icon ref="x"
                                 style="solid"
                                 class="w-4" />
                </button>
            </span>
        </template>

        <input placeholder="Seperate tags with a ,"
               maxlength="255"
               x-model.debounce.500ms="value"
               x-on:keyup.enter="addTag($event.target.value)"
               x-on:keyup="removeLastTag($event.key)"
               type="text"
               class="text-sm leading-none border-none rounded-none focus:outline-none">
    </div>

    @if ($this->availableTags->count())
        <div class="absolute bg-white border border-gray-300 rounded-md w-full shadow-sm mt-1 divide-y divide-gray-100">
            @foreach ($this->availableTags as $tag)
                <button type="button"
                        wire:click="addTag('{{ $tag }}')"
                        class="p-2 text-sm hover:bg-gray-100 rounded text-gray-700 text-left block w-full">
                    {{ $tag }}
                </button>
            @endforeach
        </div>
    @endif

    @if ($independant)
        <div class="mt-2 flex justify-end w-full">
            <x-hub::button wire:click="saveTags"
                           type="button"
                           theme="gray"
                           size="sm">
                Save Tags
            </x-hub::button>
        </div>
    @endif
</div>
