<div>
    Sent <strong>{{ $log->getExtraProperty('mailer') ?: 'Email notification' }} </strong> to {{ $log->getExtraProperty('email') }}
    <x-hub::button size="xs" theme="gray" type="button" wire:click="$set('showPreview', true)">
        View Email
    </x-hub::button>
    <x-hub::slideover title="Sent template" wire:model="showPreview" class="!max-w-4xl">
        <div class="h-full flex-col" x-data="{
            init() {
                $nextTick(() => {
                    let iframe = this.$refs['iframe'];
                    let style = iframe.style.height = iframe.contentWindow.document.documentElement.scrollHeight + 'px';
                })
            }
        }">
            <iframe
                srcdoc="{{ $this->previewHtml }}"
                class="grow w-full h-full"
                x-ref="iframe"
            ></iframe>
        </div>
    </x-hub::slideover>
</div>
