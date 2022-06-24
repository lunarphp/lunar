<div>
    <x-hub::button theme="gray" size="sm" type="button" wire:click.prevent="$set('showStatusSelect', true)">
        Update Status
    </x-hub::button>

    <x-hub::slideover title="Update status" form="updateStatus" wire:model="showStatusSelect">
        <div class="space-y-4">
            <x-hub::input.group :label="__('adminhub::inputs.status.label')" for="status" required :error="$errors->first('status')">
                <x-hub::input.select wire:model="newStatus" required>
                    <option value readonly>Select a new status</option>
                  @foreach($this->statuses as $handle => $status)
                    <option value="{{ $handle }}">{{ $status['label'] }}</option>
                  @endforeach
                </x-hub::input.select>
            </x-hub::input.group>
            @if(!$newStatus)
                <x-hub::alert>
                    Select an order status to see available mailers.
                </x-hub::alert>
            @else
                <x-hub::input.group
                    label="Additional content"
                    instructions="If supported, add an additional message to the notification or mailer."
                    for="additionalContent"
                    :error="$errors->first('additionalContent')"
                >
                    <x-hub::input.textarea wire:model="additionalContent" rows="4" />
                </x-hub::input.group>

                <x-hub::input.group
                    label="Mailers"
                    instructions="Select which mailers you would like to send."
                    for="selectedMailers"
                    :error="$errors->first('selectedMailers')"
                >
                @forelse($this->availableMailers as $key => $mailer)
                    <label wire:key="mailer_{{ $key }}" class="flex items-center justify-between border rounded shadow-sm py-2 px-3">
                        <div class="flex items-center space-x-4">
                            <x-hub::input.checkbox wire:model="selectedMailers" :value="$key"/>
                            <span class="text-sm">{{ $mailer['name'] }}</span>
                        </div>
                        <div>
                            <x-hub::button size="sm" theme="gray" type="button" wire:click="$set('previewTemplate', '{{ $key }}')">Preview</x-hub::button>
                        </div>
                    </label>
                @empty
                    <x-hub::alert level="danger">
                        There are no mailers available for this status.
                    </x-hub::alert>
                @endforelse
                </x-hub::input.group>

                <x-hub::input.group
                    label="Notifications"
                    instructions="Select which notifications you would like to send."
                    for="additionalContent"
                    :error="$errors->first('additionalContent')"
                >
                @forelse($this->availableNotifications as $notification)
                @empty
                    <x-hub::alert level="danger">
                        There are no notifications available for this status.
                    </x-hub::alert>
                @endforelse
                </x-hub::input.group>

                @if(count($this->selectedMailers))
                    <div class="space-y-4">
                        <x-hub::input.group label="Email Addresses" for="email_addresses" instructions="Select which email addresses you want to use">
                            @foreach($this->availableEmailAddresses as $key => $email)
                            <label wire:key="email_{{ $key }}" class="flex items-center justify-between border rounded shadow-sm py-2 px-3">
                                <div class="flex items-center space-x-4">
                                    <x-hub::input.checkbox wire:model="emailAddresses" :value="$email['address']"/>
                                    <span class="text-sm">{{ $email['address'] }} - <span class="text-gray-500">{{ $email['type'] }}</span></span>
                                </div>
                            </label>
                            @endforeach
                        </x-hub::input.group>

                        <x-hub::input.group label="Additional email" for="additional_email" instructions="If you need to use a custom email address, enter it here.">
                            <x-hub::input.text wire:model="additionalEmail" id="additional_email" type="email" />
                        </x-hub::input.group>
                    </div>
                @endif


            @endif
        </div>
        <x-slot name="footer">
            <x-hub::button wire:click.prevent="$set('showStatusSelect', false)" theme="gray">
              {{ __('adminhub::global.cancel') }}
            </x-hub::button>
            <x-hub::button type="submit">
              Update status
            </x-hub::button>
          </x-slot>
    </x-hub::slideover>

    <x-hub::slideover title="Preview template" wire:model="previewTemplate" class="!max-w-4xl">
        <x-hub::alert>
            This is a preview of how your email will look.
        </x-hub::alert>
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
                {{-- sandbox="allow-same-origin" --}}
                x-ref="iframe"
            ></iframe>
        </div>
    </x-hub::slideover>
</div>
