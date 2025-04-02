<form method="POST" action="{{ route('transactions.update-category') }}" class="flex justify-between space-x-2 w-full items-center">
    @csrf
    @method('PUT')

    @foreach($transactionIds as $id)
        <input type="hidden" name="transaction_ids[]" value="{{ $id }}">
    @endforeach

    <label for="new_custom_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Nouvelle catégorie :
    </label>
    <input type="text" id="new_custom_category" name="new_custom_category" required
           class="grow rounded-md border-gray-300 dark:border-gray-600 shadow-sm
                  focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 max-h-[5vh]">

    <button type="submit" class="bg-blue-900 hover:bg-gray-100 hover:text-gray-800 text-white font-semibold py-2 px-4 max-h-[5vh] border border-gray-400 rounded shadow flex items-center">
        Mettre à jour
    </button>
</form>
