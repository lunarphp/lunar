<div class="space-y-4">


    <header class="flex items-center justify-between">
        <div>
            <strong>{{ __('adminhub::components.discounts.buy_x_get_y.qualify_products.title') }}</strong>
            <p class="text-sm text-gray-600">{{ __('adminhub::components.discounts.buy_x_get_y.qualify_products.description') }}</p>
        </div>
        <div>
            @livewire('hub.components.product-search', [
                'existing' => $this->conditions,
                'ref' => 'discount-conditions',
                'showBtn' => true,
            ])
        </div>
    </header>

    <div class="space-y-1">
        @if($errors->first('selectedConditions'))
            <x-hub::alert level="danger">
                {{ __('adminhub::components.discounts.buy_x_get_y.error.at_least_one') }}
            </x-hub::alert>
        @endif
        @if(!$this->purchasableConditions->count())
            <div class="text-sm text-gray-700 border p-4 rounded bg-gray-50">
                {{ __('adminhub::components.discounts.buy_x_get_y.no_selected_products') }}
            </div>
        @endif

        @foreach($this->purchasableConditions as $product)
            <div
                wire:key="condition_product_{{ $product->id }}"
                class="rounded border px-3 py-2 flex items-center"
            >
                @if($thumbnail = $product->thumbnail)
                <div>
                    <img class="w-8 rounded" src="{{ $thumbnail->getUrl('small') }}">
                </div>
                @endif
                <div class="grow ml-4">
                    {{ $product->translateAttribute('name') }}
                </div>
                <div>
                    <button type="button" wire:click="removeCondition({{ $product->id }})">
                        <x-hub::icon ref="trash" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2">
        <x-hub::input.group for="min_qty" :error="$errors->first('discount.data.min_qty')" label="{{__('adminhub::components.discounts.buy_x_get_y.qualify_products.product_quantity.label')}}" instructions="{{__('adminhub::components.discounts.buy_x_get_y.qualify_products.product_quantity.instructions')}}">
            <x-hub::input.text type="number" id="min_qty" wire:model="discount.data.min_qty" />
        </x-hub::input.group>
    </div>

    <header class="flex items-center justify-between">
        <div>
            <strong>{{ __('adminhub::components.discounts.buy_x_get_y.product_rewards.title') }}</strong>
            <p class="text-sm text-gray-600">{{ __('adminhub::components.discounts.buy_x_get_y.product_rewards.description') }}</p>
        </div>
        <div>
            @livewire('hub.components.product-search', [
                'existing' => $this->rewards,
                'ref' => 'discount-rewards',
                'showBtn' => true,
            ])
        </div>
    </header>

    @if($errors->first('selectedRewards'))
        <x-hub::alert level="danger">
            {{ __('adminhub::components.discounts.buy_x_get_y.error.at_least_one') }}
        </x-hub::alert>
    @endif

    @if(!$this->purchasableRewards->count())
        <div class="text-sm text-gray-600 border p-4 rounded bg-gray-50">
            {{ __('adminhub::components.discounts.buy_x_get_y.no_selected_products') }}
        </div>
    @endif

    <div class="space-y-1">
        @foreach($this->purchasableRewards as $product)
            <div wire:key="reward_product_{{ $product->id }}" class="rounded border px-3 py-2 flex items-center">
                @if($thumbnail = $product->thumbnail)
                <div>
                    <img class="w-8 rounded" src="{{ $thumbnail->getUrl('small') }}">
                </div>
                @endif
                <div class="grow ml-4">
                    {{ $product->translateAttribute('name') }}
                </div>
                <div>
                    <button type="button" wire:click="removeReward({{ $product->id }})">
                        <x-hub::icon ref="trash" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <x-hub::alert>
        {{ __('adminhub::components.discounts.buy_x_get_y.product_rewards.notice') }}
    </x-hub::alert>

    <div class="grid grid-cols-2 gap-4">
        <x-hub::input.group for="reward_qty" :error="$errors->first('discount.data.reward_qty')" label="{{__('adminhub::components.discounts.buy_x_get_y.product_rewards.reward_qty.label')}}" instructions="{{__('adminhub::components.discounts.buy_x_get_y.product_rewards.reward_qty.instructions')}}">
            <x-hub::input.text type="number" wire:model="discount.data.reward_qty" />
        </x-hub::input.group>

        <x-hub::input.group for="max_reward_qty" label="{{__('adminhub::components.discounts.buy_x_get_y.product_rewards.max_reward_qty.label')}}" :error="$errors->first('discount.data.max_reward_qty')" instructions="{{__('adminhub::components.discounts.buy_x_get_y.product_rewards.max_reward_qty.instructions')}}">
            <x-hub::input.text type="number" wire:model="discount.data.max_reward_qty" />
        </x-hub::input.group>
    </div>
</div>
