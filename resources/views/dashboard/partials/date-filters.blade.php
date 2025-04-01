<div class="mb-6 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
    <form id="dateFilterForm" method="GET" action="{{ route('dashboard') }}">
        <fieldset class="flex flex-wrap items-center gap-4">
            <legend class="sr-only">Filtres de date</legend>

            <div class="flex items-center">
                <input type="radio" id="filterAll" name="filter" value="all"
                       {{ $currentFilter === 'all' ? 'checked' : '' }} class="mr-2">
                <label for="filterAll" class="cursor-pointer px-2">Toutes les dates</label>
            </div>

            <div class="flex items-center">
                <input type="radio" id="yearSelect" name="filter" value="year"
                       {{ $currentFilter === 'year' ? 'checked' : '' }} class="mr-2">
                <label for="yearSelect" class="cursor-pointer px-2">Année</label>
                <select name="year" id="yearSelect" class="dark:bg-gray-800 rounded" aria-labelledby="filterYearRadio">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}"
                            {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center">
                <input type="radio" id="filterMonth" name="filter" value="month"
                       {{ $currentFilter === 'month' ? 'checked' : '' }} class="mr-2">
                <label for="filterMonth" class="cursor-pointer px-2">Mois</label>
                <select name="monthYear" id="filterMonth" class="dark:bg-gray-800 rounded"
                        aria-labelledby="filterMonthRadio">
                    <option value="">-- Sélectionnez --</option>
                    @foreach($availableYears->sortDesc() as $year)
                        @foreach(($availableMonths[$year] ?? [])->sort() as $month)
                            <option value="{{ $year }}-{{ $month }}"
                                {{ $selectedYear == $year && $selectedMonth == $month ? 'selected' : '' }}>
                                {{
                                    \Carbon\Carbon::createFromDate($year, $month, 1)
                                        ->locale('fr')
                                        ->isoFormat('MMMM YYYY')
                                }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                Filtrer
            </button>

            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 transition-colors">
                Réinitialiser
            </a>
        </fieldset>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('dateFilterForm');
            const yearSelect = document.getElementById('yearSelect');
            const monthYearSelect = document.getElementById('monthYearSelect');

            [yearSelect, monthYearSelect].forEach(select => {
                select.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
                select.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const filterType = formData.get('filter').toString();
                const params = new URLSearchParams();

                if (filterType === 'year') {
                    const year = formData.get('year').toString();
                    if (!year) {
                        alert('Veuillez sélectionner une année');
                        return;
                    }
                    params.set('filter', 'year');
                    params.set('year', year);
                } else if (filterType === 'month') {
                    const monthYear = formData.get('monthYear').toString();
                    if (!monthYear) {
                        alert('Veuillez sélectionner un mois');
                        return;
                    }
                    params.set('filter', 'month');
                    const [year, month] = monthYear.split('-');
                    params.set('year', year);
                    params.set('month', month);
                } else {
                    params.set('filter', 'all');
                }

                window.location.href = `${form.action}?${params.toString()}`;
            });
        });
    </script>
@endpush
