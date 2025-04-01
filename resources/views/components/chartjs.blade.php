<div {!! $attributes->merge(['class' => 'chart-container']) !!}>
    <canvas id="{{ $chart->name }}"></canvas>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('{{ $chart->name }}').getContext('2d');
            new Chart(ctx, @json($chart->toArray()));
        });
    </script>
@endpush
