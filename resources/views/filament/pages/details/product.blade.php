<x-filament-panels::page>

    <div>
        <div class="flex justify-between items-center text-sm font-medium mb-2">
            <div>
                প্রোডাক্ট নামঃ {{ $product['name'] }}
            </div>
            <div>
                স্টকঃ {{ "{$product['stock']} {$product['unit']}" }}
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr class="text-center">
                        <th class="border border-gray-300 px-4 py-1">তারিখ</th>
                        <th class="border border-gray-300 px-4 py-1">বিবরণ</th>
                        <th class="border border-gray-300 px-4 py-1">হিসাব</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $odd = 'bg-white dark:bg-gray-800';
                        $even = 'bg-gray-50 dark:bg-gray-900';
                    @endphp
                    @forelse ($stocks as $stock)
                        <tr class="{{ $loop->odd ? $odd : $even }} border-b border-gray-300">
                            <td class="border px-1 py-1 text-center">
                                {{ $stock->date }}
                            </td>
                            <td class="border px-1 py-1 text-left">
                                <x-filament::link :href="route($product['route'], [
                                    'customer_id' => $stock->customer_id,
                                ])" color="" weight="thin">
                                    {{ $stock->customer->name }}
                                </x-filament::link>
                            </td>
                            <td class="border px-1 py-1 text-right">
                                {{ "{$stock->display_quantity} {$stock->unit}" }}
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-gray-800 border-b border-gray-300">
                            <td class="border px-1 py-1 text-center" colspan="4">
                                There is no data
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</x-filament-panels::page>
