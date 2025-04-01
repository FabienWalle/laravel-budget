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

                    @if(empty($transactions))
                        <div class="mt-4 p-4 bg-yellow-100 dark:bg-yellow-900 rounded">
                            Aucune dépense à afficher.
                        </div>
                    @else
                        <div class="mt-8">
                            <h3 class="text-lg font-medium mb-4">Répartition des dépenses</h3>

                            @include('dashboard.partials.date-filters')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @include('dashboard.partials.chart')
                                @include('dashboard.partials.transactions-list')
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
