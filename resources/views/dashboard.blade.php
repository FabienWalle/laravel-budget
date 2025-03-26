<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                    @if($transactions->isEmpty())
                        <div class="mt-4 p-4 bg-yellow-100 dark:bg-yellow-900 rounded">
                            Aucune dépense à afficher.
                        </div>
                    @else
                        <div class="mt-8">
                            <h3 class="text-lg font-medium mb-4">Répartition des dépenses</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2 bg-white dark:bg-gray-700 p-4 rounded-lg">
                                    <div style="height: 400px;">
                                        <x-chartjs-component :chart="$chart" />
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="font-medium mb-3">Détails des dépenses</h4>
                                    <div class="mb-2">
                                        <span class="font-medium">Total dépensé :</span>
                                        <span class="float-right">{{ number_format($total, 2, ',', ' ') }} €</span>
                                    </div>

                                    <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>

                                    @foreach($transactions as $item)
                                        <div class="mb-2 text-sm">
                                            <span>{{ $item['category'] }} :</span>
                                            <span class="float-right">
                                                {{ number_format($item['amount'], 2, ',', ' ') }} € ({{ $item['percentage'] }}%)
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @unless($transactions->isEmpty())
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @endpush
    @endunless
</x-app-layout>
