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
