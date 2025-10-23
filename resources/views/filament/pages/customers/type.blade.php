<x-filament-panels::page>
    <div class="flex flex-wrap gap-2">
        <div class="w-32 px-3 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
            <div class="text-xs mb-1">
                {{ __("পাবো") }}
            </div>
            <div class="text-sm font-semibold tracking-tight text-success-600 dark:text-success-400 mb-1">
                {{ format_number($this->summary->positive_balance) ?? 0 }}
            </div>
            <div class="text-xs tracking-tight text-success-600 dark:text-success-400 mb-1">
                {{ __("গ্রাহকঃ ") . $this->summary->positive_customers }}
            </div>
        </div>
        <div class="w-32 px-3 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
            <div class="text-xs mb-1">
                {{ __("পাবে") }}
            </div>
            <div class="text-sm font-semibold tracking-tight text-success-600 dark:text-success-400 mb-1">
                {{ format_number($this->summary->negative_balance) ?? 0 }}
            </div>
            <div class="text-xs tracking-tight text-success-600 dark:text-success-400 mb-1">
                {{ __("গ্রাহকঃ ") . $this->summary->negative_customers }}
            </div>
        </div>
    </div>

    {{-- টেবিল যোগ করুন --}}
        {{ $this->table }}
</x-filament-panels::page>
