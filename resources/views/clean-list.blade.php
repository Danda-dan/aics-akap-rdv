<x-app-layout>
    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white min-h-96 overflow-y-auto shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Clean List/No Match Beneficiaries') }}
                    </h2>
                    <form method="GET" class="mt-5 mb-4 flex gap-2">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search name or location"
                            value="{{ request('search') }}"
                            class="border px-3 py-2 rounded w-full"
                        >

                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="border px-3 py-2 rounded"
                        >

                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="border px-3 py-2 rounded"
                        >

                        <button class="bg-blue-600 text-blue px-4 py-2 border rounded">
                            Filter
                        </button>

                        <a href="{{ route('clean.list') }}" class="px-4 py-2 border rounded">
                            Reset
                        </a>
                    </form>

                    <div class="w-full mx-auto">
                        <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Control #</th>
                                    <th class="px-4 py-2 text-left w-64">Name</th>
                                    <th class="px-4 py-2 text-left">Birthday</th>
                                    <th class="px-4 py-2 text-left">Date Processed</th>
                                    <th class="px-4 py-2 text-left">RDV Focal</th>
                                    <th class="px-4 py-2 text-left">SDO</th>
                                    <th class="px-4 py-2 text-left">Payout Site</th>
                                    <th class="px-4 py-2 text-left">Forced Entry</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($clean_list as $bene)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ $bene->control_number }}</td>
                                        <td class="px-4 py-2">{{ $bene->first_name }} {{ $bene->middle_name }} {{ $bene->last_name }} {{ $bene->extension_name }}</td>
                                        <td class="px-4 py-2">{{ $bene->birth_month }}/{{ $bene->birth_day }}/{{ $bene->birth_year }}</td>
                                        <td class="px-4 py-2">{{ $bene->date_processed }}</td>
                                        <td class="px-4 py-2">{{ $bene->poo_rdv_focal }}</td>
                                        <td class="px-4 py-2">{{ $bene->sdo }}</td>
                                        <td class="px-4 py-2">{{ $bene->payout_site }}</td>
                                        <td class="px-4 py-2">{{ $bene->force_entry }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $clean_list->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>