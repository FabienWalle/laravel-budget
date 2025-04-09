<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Analyse des dépenses
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <form method="GET" action="{{ route('analytics') }}" class="mb-6">
                    <div class="flex items-center gap-4">
                        <label class="dark:text-white" for="categorySelect">Catégorie</label>
                        <select name="category" id="categorySelect"
                                                                    class="dark:bg-gray-700 rounded-md border-gray-300 dark:border-gray-600">
                            <option value="">Toutes catégories</option>
                            @foreach($availableCategories as $category)
                                <option value="{{ $category }}"
                                    {{ $selectedCategory == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                            Appliquer
                        </button>
                    </div>
                </form>

                <div class="chart-container" style="position: relative; height:400px; width:100%">
                    {!! $chart->render() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
