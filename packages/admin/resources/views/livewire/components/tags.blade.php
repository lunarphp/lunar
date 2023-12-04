<div class="relative mt-1 space-y-4"
    x-data="{
        tags: $wire.entangle('tags'),
        value: $wire.entangle('searchTerm', true),
    
        addTag(newTag) {
            let trimTag = newTag.toUpperCase().trim();
    
            if (!this.tags.includes(trimTag)) {
                this.tags.push(trimTag);
            }            
        },
    
        removeTag(index) {
            this.tags.splice(index, 1);
        },
    
        removeLastTag(key) {
            if (key === 'Backspace' && !this.value) {
                this.tags.splice(this.tags.length - 1, 1);
            }
        },

        clearInput(){
            this.value = null
        }
    }"
    x-on:click.outside="value = null"
    x-init="$watch('value', (newValue) => {
        if(newValue === null) return

        let trimTag = newValue.trim()
    
        if (trimTag.endsWith(',')) {
            let newTag = trimTag.replaceAll(',', '')
    
            if(newTag === null || !newTag.length){
                clearInput()

                return
            } 

            addTag(newTag)

            clearInput()
        }
    })">

    <div class="flex flex-wrap gap-2">
        <template x-for="(tag, tagIndex) in tags" :key="tagIndex">
            <x-filament::button
                icon="heroicon-m-x-mark"
                icon-position="after"
                size="sm"
                color="primary"
                outlined
                @click.prevent="removeTag(tagIndex)"
            >
                <span x-text="tag"></span>
            </x-filament::button>
        </template>
    </div>

    <div class="flex gap-4">
        <x-filament::input.wrapper class="flex-1">
            <x-filament::input
                placeholder="{{ __('lunarpanel::components.tags.input.placeholder') }}"
                maxlength="255"
                x-model="value"
                x-on:keyup.enter="addTag($event.target.value); clearInput()"
                x-on:keyup="removeLastTag($event.key)"
                type="text"
                class="text-sm border-none rounded-lg focus:outline-none"
                
            />
        </x-filament::input.wrapper>
        
        <div>
            {{ $this->saveAction }}
        </div>
    </div>

    @if ($this->availableTags->count())
        <div class="z-10 absolute p-2 space-y-1 bg-white ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10 rounded-lg w-full shadow-lg -translate-y-2">
            @foreach ($this->availableTags as $tag)
                <button type="button"
                    wire:click="addTag('{{ $tag }}')"
                    class="p-2 text-sm hover:bg-primary-400/10 rounded-lg text-gray-700 dark:text-gray-200 text-left block w-full"
                >
                    {{ $tag }}
                </button>
            @endforeach
        </div>
    @endif
</div>
