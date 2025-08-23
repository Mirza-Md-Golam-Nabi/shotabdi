<x-filament-panels::page>

    <div>
        <div class="flex justify-between">
            <div>
                <x-filament::button icon="heroicon-o-arrow-left" size="sm" color="danger" tag="a"
                    href="{{ route('filament.admin.pages.stock-out-calculation-feed', ['date' => $date['prev_date']]) }}">

                </x-filament::button>
            </div>
            <div class="text-sm font-bold text-center">দৈনিক হিসাব (ফিড)</div>
            <div>
                <x-filament::button icon="heroicon-o-arrow-right" size="sm" color="success" tag="a"
                    href="{{ route('filament.admin.pages.stock-out-calculation-feed', ['date' => $date['next_date']]) }}">

                </x-filament::button>
            </div>
        </div>

        <div class="flex justify-between items-center text-sm font-medium mb-2">
            <div>তারিখঃ {{ $date['bn_date'] }}</div>
            <div>বারঃ {{ $date['bn_day'] }}</div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                        <th colspan="2" class="border border-gray-300 px-4 py-1 text-center">জমা</th>
                        <th colspan="2" class="border border-gray-300 px-4 py-1 text-center">খরচ</th>
                    </tr>
                    <tr class="text-center">
                        <th class="border border-gray-300 px-4 py-1">বিবরণ</th>
                        <th class="border border-gray-300 px-4 py-1">টাকা</th>
                        <th class="border border-gray-300 px-4 py-1">বিবরণ</th>
                        <th class="border border-gray-300 px-4 py-1">বস্তা</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $odd = 'bg-white dark:bg-gray-800';
                        $even = 'bg-gray-50 dark:bg-gray-900';
                    @endphp
                    @forelse ($transactions as $tran)
                        <tr class="{{ $loop->odd ? $odd : $even }} border-b border-gray-300">
                            <td class="border border-gray-300 px-1 py-1">
                                @if ($tran['tran_stock_out_id'])
                                    <x-filament::icon-button icon="heroicon-m-pencil-square" color="success"
                                        icon-size="sm" :href="route('filament.admin.resources.stock-outs.edit', [
                                            'record' => $tran['tran_stock_out_id'],
                                        ])" tag="a" class="inline-flex" />
                                @endif
                                {{ $tran['tran_name'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $tran['tran_amount'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1">
                                @if ($tran['stock_out_id'])
                                    <x-filament::icon-button icon="heroicon-m-pencil-square" color="success"
                                        icon-size="sm" :href="route('filament.admin.resources.stock-outs.edit', [
                                            'record' => $tran['stock_out_id'],
                                        ])" tag="a" class="inline-flex" />
                                @endif
                                {{ $tran['stock_out_name'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $tran['stock_out_quantity'] }}
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-gray-800 border-b border-gray-300">
                            <td class="border border-gray-300 px-1 py-1 text-center" colspan="4">
                                There is no data
                            </td>
                        </tr>
                    @endforelse
                    @if (!empty($transactions))
                        <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-300 font-black">
                            <td class="border border-gray-300 px-1 py-1 text-center">
                                {{ __('মোট') }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $sum['total_amount'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-center">
                                {{ __('মোট') }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $sum['total_quantity'] }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>

</x-filament-panels::page>
