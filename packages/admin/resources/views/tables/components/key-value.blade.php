<div class="w-full mt-2">
    @if(filled($values = $getState()))
        <div class="overflow-hidden overflow-x-auto ring-1 ring-inset ring-gray-950/10 dark:ring-white/10 rounded bg-white/70 dark:bg-white/5">
            <table class="min-w-full text-xs divide-y divide-gray-950/10 dark:divide-white/10">
                <tbody class="divide-y divide-gray-950/10 dark:divide-white/10">
                    @foreach($getState() as $key => $value)
                        <tr class="divide-x divide-gray-950/10 dark:divide-white/10">
                            <td class="p-2 font-medium whitespace-nowrap">
                                @php
                                    $langKey = "lunarpanel::order.infolist.{$key}.label";
                                    $label = __($langKey);
                                @endphp
                                {{ $label == $langKey ? $key : $label }}
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif (($placeholder = $getPlaceholder()) !== null)
        <x-filament-tables::columns.placeholder>
            {{ $placeholder }}
        </x-filament-tables::columns.placeholder>
    @endif
</div>