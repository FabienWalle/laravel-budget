<div class="mb-6 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
    <form id="dateFilterForm" method="GET" action="{{ route('dashboard') }}">
        <div class="flex flex-wrap items-center gap-4">
            <!-- Filtre Toutes dates -->
            <div class="flex items-center">
                <input type="radio" id="filterAll" name="filter" value="all"
                       {{ $currentFilter === 'all' ? 'checked' : '' }} class="mr-2">
                <label for="filterAll" class="cursor-pointer">Toutes les dates</label>
            </div>

            <!-- Filtre Année -->
            <div class="flex items-center">
                <input type="radio" id="filterYear" name="filter" value="year"
                       {{ $currentFilter === 'year' ? 'checked' : '' }} class="mr-2">
                <label for="filterYear" class="cursor-pointer mr-2">Année</label>
                <select name="year" id="yearSelect" class="dark:bg-gray-800 rounded"
                    {{ $currentFilter !== 'year' ? 'disabled' : '' }}>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}"
                            {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtre Mois -->
            <div class="flex items-center">
                <input type="radio" id="filterMonth" name="filter" value="month"
                       {{ $currentFilter === 'month' ? 'checked' : '' }} class="mr-2">
                <label for="filterMonth" class="cursor-pointer mr-2">Mois</label>
                <select name="year" id="monthYearSelect" class="dark:bg-gray-800 rounded mr-2"
                    {{ $currentFilter !== 'month' ? 'disabled' : '' }}>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}"
                            {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
                <select name="month" id="monthSelect" class="dark:bg-gray-800 rounded"
                    {{ $currentFilter !== 'month' ? 'disabled' : '' }}>
                    @if($currentFilter === 'month' && isset($availableMonths[$selectedYear]))
                        @foreach($availableMonths[$selectedYear] as $month)
                            <option value="{{ $month }}"
                                {{ $selectedMonth == $month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Bouton Appliquer -->
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                Appliquer
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('dateFilterForm');
            const filterAll = document.getElementById('filterAll');
            const filterYear = document.getElementById('filterYear');
            const filterMonth = document.getElementById('filterMonth');
            const yearSelect = document.getElementById('yearSelect');
            const monthYearSelect = document.getElementById('monthYearSelect');
            const monthSelect = document.getElementById('monthSelect');

            // Gestion du changement de filtre
            [filterAll, filterYear, filterMonth].forEach(input => {
                input.addEventListener('change', function() {
                    yearSelect.disabled = this.value !== 'year';
                    monthYearSelect.disabled = this.value !== 'month';
                    monthSelect.disabled = this.value !== 'month';
                });
            });

            // Chargement des mois quand l'année change
            monthYearSelect?.addEventListener('change', function() {
                const year = this.value;

                fetch(`/dashboard/get-months?year=${year}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur réseau');
                        return response.json();
                    })
                    .then(months => {
                        monthSelect.innerHTML = '';

                        months.forEach(month => {
                            const date = new Date();
                            date.setMonth(month - 1);
                            const monthName = date.toLocaleString('fr-FR', { month: 'long' });
                            const option = new Option(
                                monthName.charAt(0).toUpperCase() + monthName.slice(1),
                                month
                            );
                            monthSelect.add(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                    });
            });
        });
    </script>
@endpush
