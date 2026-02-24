<x-app-layout>
    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Daily/Consolidated Served Beneficiaries') }}
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

                        <button class="bg-blue-600 px-4 py-2 border rounded">
                            Filter
                        </button>

                        <a href="{{ route('served.benes') }}" class="px-4 py-2 border rounded">
                            Reset
                        </a>
                    </form>

                    <div class="w-full mx-auto">
                        <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Control #</th>
                                    <th class="px-4 py-2 text-left">Name</th>
                                    <th class="px-4 py-2 text-left">Birthday</th>
                                    <th class="px-4 py-2 text-left">Date Served</th>
                                    <th class="px-4 py-2 text-left">Location</th>
                                    <th class="px-4 py-2 text-left">Program</th>
                                    <th class="px-4 py-2 text-left">Event Type</th>
                                    <th class="px-4 py-2 text-left">Charging</th>
                                    <th class="px-4 py-2 text-left">Date Uploaded</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($served_benes as $bene)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ $bene->control_number }}</td>
                                        <td class="px-4 py-2">{{ $bene->first_name }} {{ $bene->middle_name }} {{ $bene->last_name }} {{ $bene->extension_name }}</td>
                                        <td class="px-4 py-2">{{ $bene->birth_month }}/{{ $bene->birth_day }}/{{ $bene->birth_year }}</td>
                                        <td class="px-4 py-2">{{ $bene->date_last_served }}</td>
                                        <td class="px-4 py-2">{{ $bene->last_served_location }}</td>
                                        <td class="px-4 py-2">{{ $bene->program }}</td>
                                        <td class="px-4 py-2">{{ $bene->event_type }}</td>
                                        <td class="px-4 py-2">{{ $bene->charging }}</td>
                                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($bene->created_at)->format('m/d/Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $served_benes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>