<div class="space-y-2">

    <span>
        {{ __('lunarpanel::components.activity-log.partials.orders.address', [
            'type' => __("lunarpanel::components.activity-log.partials.orders.{$type}"),
        ]) }}
    </span>

    <table class="table-auto">
        <tbody>
            @foreach($diff as $field => $value)
                <tr>
                    @php
                        $key = "lunarpanel::order.form.{$field}.label";
                        if(($label = __($key)) == $key){
                            $label = str($field)->studly()->snake(' ')->ucfirst();
                        }                    
                    @endphp
                    
                    <td>
                        <span class="text-xs">{{ $label }}</span>
                    </td>
                    
                    <td class="pl-2">
                        <div class="flex">
                        <x-filament::badge color="gray">
                            {{ $value['old'] ?? '-' }}
                        </x-filament::badge>
                
                        @svg('heroicon-m-chevron-right', [
                            'class' => 'w-4'
                        ])
                        
                        <x-filament::badge color="success">
                            {{ $value['new'] ?? '-' }}
                        </x-filament::badge>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>