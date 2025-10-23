<x-filament-panels::page>

    <div>
        <div class="flex justify-between">
            <div>
                <x-filament::button icon="heroicon-o-arrow-left" size="sm" color="danger" tag="a"
                    href="{{ route($route['current'], ['date' => $date['prev_date']]) }}">

                </x-filament::button>
            </div>
            <div class="text-sm font-bold text-center">দৈনিক হিসাব (ফিড)</div>
            <div>
                <x-filament::button icon="heroicon-o-arrow-right" size="sm" color="success" tag="a"
                    href="{{ route($route['current'], ['date' => $date['next_date']]) }}">

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
                        <th class="border border-gray-300 px-4 py-1">বস্তা</th>
                        <th class="border border-gray-300 px-4 py-1">বিবরণ</th>
                        <th class="border border-gray-300 px-4 py-1">বস্তা</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $odd = 'bg-white dark:bg-gray-800';
                        $even = 'bg-gray-50 dark:bg-gray-900';
                    @endphp
                    @forelse ($stocks as $stock)
                        <tr class="{{ $loop->odd ? $odd : $even }} border-b border-gray-300">
                            <td class="border border-gray-300 px-1 py-1">
                                @if ($stock['stock_in_id'])
                                    <x-filament::icon-button icon="heroicon-m-pencil-square" color="success"
                                        icon-size="sm" :href="route($route['stock_in_edit'], [
                                            'record' => $stock['stock_in_id'],
                                        ])" tag="a" class="inline-flex" />
                                    <x-filament::link :href="route($route['product_detail'], [
                                        'product_id' => $stock['stock_in_p_id'],
                                    ])" color="" weight="thin">
                                        {{ $stock['stock_in_p_name'] }}
                                    </x-filament::link>
                                    <br>
                                    <x-filament::link :href="route($route['customer_detail'], [
                                        'customer_id' => $stock['stock_in_c_id'],
                                    ])" color="" weight="thin">
                                        {{ $stock['stock_in_c_name'] }}
                                    </x-filament::link>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $stock['stock_in_quantity'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1">
                                @if ($stock['stock_out_id'])
                                    <x-filament::icon-button icon="heroicon-m-pencil-square" color="success"
                                        icon-size="sm" :href="route($route['stock_out_edit'], [
                                            'record' => $stock['stock_out_id'],
                                        ])" tag="a" class="inline-flex" />
                                    <x-filament::link :href="route($route['product_detail'], [
                                        'product_id' => $stock['stock_out_p_id'],
                                    ])" color="" weight="thin">
                                        {{ $stock['stock_out_p_name'] }}
                                    </x-filament::link>
                                    <br>
                                    <x-filament::link :href="route($route['customer_detail'], [
                                        'customer_id' => $stock['stock_out_c_id'],
                                    ])" color="" weight="thin">
                                        {{ $stock['stock_out_c_name'] }}
                                    </x-filament::link>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $stock['stock_out_quantity'] }}
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-gray-800 border-b border-gray-300">
                            <td class="border border-gray-300 px-1 py-1 text-center" colspan="4">
                                There is no data
                            </td>
                        </tr>
                    @endforelse
                    @if (!empty($stocks))
                        <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-300 font-black">
                            <td class="border border-gray-300 px-1 py-1 text-center">
                                {{ __('মোট') }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $sum['stock_in_total'] }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-center">
                                {{ __('মোট') }}
                            </td>
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                {{ $sum['stock_out_total'] }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>

</x-filament-panels::page>
