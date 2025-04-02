<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Paramétrage des transactions') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
        @foreach ($transactions as $short_description => $group)
            <details class="mb-4 border border-gray-300 dark:border-gray-600 rounded-md">
                <summary class="p-3 cursor-pointer bg-gray-100 dark:bg-gray-700 rounded-t-md">
                    <strong>{{ $short_description }}</strong> ({{ count($group) }} transactions)
                </summary>
                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-b-md">
                    <ul class="space-y-2">
                        @foreach ($group as $transaction)
                            <li class="p-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $transaction->operation_date->format('d/m/Y') }}
                                </span>
                                - <span class="font-semibold">{{ number_format($transaction->amount, 2, ',', ' ') }} €</span>
                                <br>
                                <span class="text-gray-700 dark:text-gray-300 text-sm">
                                    {{ $transaction->description }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endforeach
    </div>
</x-app-layout>
