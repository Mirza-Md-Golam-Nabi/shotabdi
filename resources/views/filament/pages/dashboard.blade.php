<x-filament-panels::page>
    <div class="flex flex-wrap gap-2">

        @foreach ($customer_stats as $stat)
            @php
                $colorClass = $stat->type->filamentColor();
            @endphp

            <a href="{{ route('filament.admin.pages.customer-list', ['type'=>$stat->type]) }}">
                <div
                    class="w-32 px-3 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <div class="text-xs {{ $colorClass }} mb-1">
                        {{ $stat->type->bangla() }}
                    </div>
                    <div class="text-sm font-semibold tracking-tight text-success-600 dark:text-success-400 mb-1">
                        {{ format_number($stat->total_balance) ?? 0 }}
                    </div>
                    <div class="text-xs {{ $colorClass }}">
                        মোট গ্রাহকঃ {{ $stat->total_count ?? 0 }}
                    </div>
                </div>
            </a>
        @endforeach

    </div>
</x-filament-panels::page>
