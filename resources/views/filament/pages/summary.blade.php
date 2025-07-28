<x-filament::page>
    <div class="flex flex-wrap gap-2">
        @foreach ($groupedTransactions as $item)
            <a href="{{ route('filament.admin.pages.daily-calculation', ['date' => $item->date]) }}"
                class="block sm:w-64 px-6 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">

                <div class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">
                    {{ $item->en_date }}
                </div>
                <div class="text-xs text-gray-700 dark:text-gray-400">
                    {{ $item->bn_day }}
                </div>
            </a>
        @endforeach
    </div>
</x-filament::page>
