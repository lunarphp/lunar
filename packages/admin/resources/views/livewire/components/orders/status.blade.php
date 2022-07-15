<div>
    <x-hub::button theme="gray" size="sm" type="button" wire:click.prevent="$set('showStatusSelect', true)">
        {{ __('adminhub::components.orders.status.update_btn') }}
    </x-hub::button>

    <x-hub::slideover title="Update status" form="updateStatus" wire:model="showStatusSelect">
        <div class="space-y-4">
            <x-hub::input.group :label="__('adminhub::inputs.status.label')" for="status" required :error="$errors->first('status')">
                <x-hub::input.select wire:model="newStatus" required>
                    <option value readonly>{{ __('adminhub::components.orders.status.select_new') }}</option>
                  @foreach($this->statuses as $handle => $status)
                    <option value="{{ $handle }}">{{ $status['label'] }}</option>
                  @endforeach
                </x-hub::input.select>
            </x-hub::input.group>
            @if(!$newStatus)
                <x-hub::alert>
                    {{ __('adminhub::components.orders.status.no_status_selected_alert') }}
                </x-hub::alert>
            @else
                <x-hub::input.group
                    :label="__('adminhub::components.orders.status.additional-content.label')"
                    :instructions="__('adminhub::components.orders.status.additional-content.instructions')"
                    for="additionalContent"
                    :error="$errors->first('additionalContent')"
                >
                    <x-hub::input.textarea wire:model="additionalContent" rows="4" />
                </x-hub::input.group>

                <x-hub::input.group
                    :label="__('adminhub::components.orders.status.mailers.label')"
                    :instructions="__('adminhub::components.orders.status.mailers.instructions')"
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
                            <x-hub::button size="sm" theme="gray" type="button" wire:click="$set('previewTemplate', '{{ $key }}')">
                                {{ __('adminhub::global.preview') }}
                            </x-hub::button>
                        </div>
                    </label>
                @empty
                    <x-hub::alert level="danger">
                        {{ __('adminhub::components.orders.status.mailers.empty') }}
                    </x-hub::alert>
                @endforelse
                </x-hub::input.group>

                <x-hub::input.group
                    :label="__('adminhub::components.orders.status.notifications.label')"
                    :instructions="__('adminhub::components.orders.status.notifications.instructions')"
                    for="additionalContent"
                    :error="$errors->first('additionalContent')"
                >
                @forelse($this->availableNotifications as $notification)
                @empty
                    <x-hub::alert level="danger">
                        {{ __('adminhub::components.orders.status.notifications.empty') }}
                    </x-hub::alert>
                @endforelse
                </x-hub::input.group>

                @if(count($this->selectedMailers))
                    <div class="space-y-4">
                        <x-hub::input.group :label="__('adminhub::components.orders.status.email_addresses.label')" for="email_addresses" :instructions="__('adminhub::components.orders.status.email_addresses.instructions')">
                            @foreach($this->availableEmailAddresses as $key => $email)
                            <label wire:key="email_{{ $key }}" class="flex items-center justify-between border rounded shadow-sm py-2 px-3">
                                <div class="flex items-center space-x-4">
                                    <x-hub::input.checkbox wire:model="emailAddresses" :value="$email['address']"/>
                                    <span class="text-sm">{{ $email['address'] }} - <span class="text-gray-500">{{ $email['type'] }}</span></span>
                                </div>
                            </label>
                            @endforeach
                        </x-hub::input.group>

                        <x-hub::input.group :label="__('adminhub::components.orders.status.additional_email.label')" for="additional_email" :instructions="__('adminhub::components.orders.status.additional_email.instructions')">
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
              {{ __('adminhub::components.orders.status.update_btn') }}
            </x-hub::button>
          </x-slot>
    </x-hub::slideover>

    <x-hub::slideover :title="__('adminhub::components.orders.status.preview.title')" wire:model="previewTemplate" class="!max-w-4xl">
        <div class="space-y-4">
            <x-hub::alert>
                {{ __('adminhub::components.orders.status.preview.alert') }}
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
                    x-ref="iframe"
                ></iframe>
            </div>
        </div>
    </x-hub::slideover>
</div>
