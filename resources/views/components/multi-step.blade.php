<!-- resources/views/components/multi-step-form.blade.php -->
<div x-data="{ step: {{ session('step', 1) }}, maxSteps: {{ count($steps) }} }" class="w-full max-w-2xl mx-auto">
    <!-- Step Indicator -->
    <div class="flex justify-between mb-3">
        @foreach ($steps as $index => $step)
            <div class="flex-1 text-center">
                <div :class="step === {{ $index + 1 }} ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600'"
                    class="rounded-full h-10 w-10 flex items-center justify-center mx-auto mb-2">
                    {{ $index + 1 }}
                </div>
                <span class="text-sm font-semibold">{{ $step }}</span>
            </div>
        @endforeach
    </div>

    <!-- Form Steps -->
    <form x-data="{ isLoading: false, onStep: 3, mode: '{{ old('mode', '') }}', entry_type: '{{ old('entry_type', 'Crossmatch') }}' }" 
        x-init="$watch('isLoading', value => window.scriptRunning = value)" 
        x-on:submit="if (step === onStep) { isLoading = true }"
        method="POST" action="{{ route('form.submit') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="current_step" x-bind:value="step">
        <!-- Step 1 -->
        <div x-show="step === 1" class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <!-- Header + Radio Buttons -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">Step 1: Initial Details</h2>

                <div class="flex items-center gap-4 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="entry_type" x-model="entry_type" value="Crossmatch" checked>
                        Crossmatch
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="entry_type" x-model="entry_type" value="Force Entry">
                        Force Entry
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <select
                    id="mode"
                    name="mode"
                    x-model="mode"
                    required
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                >
                    <option value="">-- Select Payout Type --</option>
                    <option value="sdo" {{ old('mode') == 'sdo' ? 'selected' : '' }}>
                        SDO
                    </option>
                    <option value="hybrid" {{ old('mode') == 'hybrid' ? 'selected' : '' }}>
                        Hybrid
                    </option>
                </select>
            </div>

            @error('mode')
                <span class="text-red-500 text-sm mb-4 block">
                    {{ $message }}
                </span>
            @enderror

            <!-- Always required -->
            <input type="text" name="requesting_partner" value="{{ old('requesting_partner') }}"
                placeholder="Enter Requesting Partner"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full"
                required>
            @error('requesting_partner') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <!-- Disabled & not required when SDO -->
            <input type="text" name="sdo" value="{{ old('sdo') }}"
                placeholder="Enter SDO"
                :required="mode === 'sdo'"
                :disabled="mode === 'hybrid'"
                :class="[mode === 'hybrid' ? 'bg-gray-200 cursor-not-allowed' : '']"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full">
            @error('sdo') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="check_number" value="{{ old('check_number') }}"
                placeholder="Enter Check Number"
                :required="mode === 'sdo'"
                :disabled="mode === 'hybrid'"
                :class="[mode === 'hybrid' ? 'bg-gray-200 cursor-not-allowed' : '']"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full">
            @error('check_number') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <div class="mb-4 flex items-center gap-4">
                <label for="check_date_issued"
                    class="w-40 text-sm font-medium text-gray-700">
                    Check Date Issued
                </label>

                <input
                    id="check_date_issued"
                    type="date"
                    name="check_date_issued"
                    value="{{ old('check_date_issued') }}"
                    :required="mode === 'sdo'"
                    :disabled="mode === 'hybrid'"
                    :class="[mode === 'hybrid' ? 'bg-gray-200 cursor-not-allowed' : '']"
                    class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
            </div>

            @error('check_date_issued')
                <span class="text-red-500 text-sm mb-4 block ml-44">
                    {{ $message }}
                </span>
            @enderror

            <input type="text" name="payout_site" value="{{ old('payout_site') }}"
                placeholder="Enter Payout Site" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" required>
            @error('payout_site') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="poo_rdv_focal" value="{{ old('poo_rdv_focal') }}"
                placeholder="Enter POO RDV Focal" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" required>
            @error('poo_rdv_focal') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="reason" value="{{ old('reason') }}" :required="entry_type === 'Force Entry'" :disabled="entry_type === 'Crossmatch'"
                placeholder="Justification for Force Entry" 
                :class="[entry_type === 'Crossmatch' ? 'bg-gray-200 cursor-not-allowed' : '']"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full">
            @error('reason') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror
        </div>

        <!-- Step 2 -->
        <div x-show="step === 2" x-data="{ hasUploadedFile: {{ session('uploaded_request_file_name') ? 'true' : 'false' }} }"
            class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <h2 class="text-xl font-bold mb-4">Step 2: Import Raw File</h2>

            <!-- Large CSV Icon -->
            <div class="flex justify-center mb-6">
                <svg class="w-48 h-48 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 2h7l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                    <text x="6" y="17" font-size="6" fill="white">CSV</text>
                </svg>
            </div>

            <input type="file" name="request" id="request" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" 
                :required="step === 2 && !hasUploadedFile">
        
            @error('request') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            @if(session('uploaded_request_file_name'))
                <p class="mt-2">Uploaded File: {{ session('uploaded_request_file_name') }}</p>
                <p class="text-gray-500">You can upload a new file if you wish to replace this one.</p>
            @endif
        </div>

        <!-- Step 3 -->
        <div x-show="step === 3">
            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
                <h2 class="text-2xl font-semibold text-center mb-2">Review Your Information</h2>

                <div class="mb-2">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Step 1 Details</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Processing Type:</span> {{ session('entry_type') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Payout Type:</span> {{ strtoupper(session('payout_mode')) ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Requesting Partner:</span> {{ session('requesting_partner') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Special Disbursing Officer:</span> {{ session('sdo') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Check Number:</span> {{ session('check_number') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Check Date Issued:</span> {{ session('check_date_issued') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Payout Site:</span> {{ session('payout_site') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">POO RDV Focal:</span> {{ session('poo_rdv_focal') ?? 'N/A' }}</p>
                        <p class="text-gray-600"><span class="font-semibold">Justification:</span> {{ session('reason') ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Step 2 Details</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        <p class="text-gray-600"><span class="font-semibold">Raw List:</span> 
                            @if(session('uploaded_request_file_name'))
                                <a href="{{ route('file.show', 
                                [
                                    'path' => basename(session('uploaded_request_file_path')),
                                    'name' => basename(session('uploaded_request_file_name')),
                                ]) }}" target="_blank" class="text-blue-500 underline">
                                    {{ session('uploaded_request_file_name') }}
                                </a>
                            @else
                                No document uploaded.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div x-show="step === 4">
            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
                <h2 class="text-2xl font-semibold text-center mb-6">Done processing</h2>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Verification Result</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        @if(session('result'))
                            <div class="alert alert-success">
                                {{ session('result') }}
                            </div>
                        @endif
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Raw List:</span> {{ session('master_list') }} </p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Clean List:</span> {{ session('clean_list') }} </p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Identified Served:</span> {{ session('served_list') }} </p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Identified Duplicate:</span> {{ session('duplicate_list') }} </p>
                        <p class="text-gray-600"><span class="font-semibold">Invalid:</span> {{ session('invalid_list') }} </p>
                    </div>
                </div>
                
                <input type="hidden" name="master_list" value="{{ session('master_list') }}">
                <input type="hidden" name="clean_list" value="{{ session('clean_list') }}">
                <input type="hidden" name="served_list" value="{{ session('served_list') }}">
                <input type="hidden" name="duplicate_list" value="{{ session('duplicate_list') }}">
                <input type="hidden" name="invalid_list" value="{{ session('invalid_list') }}">
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-4 flex justify-between">
            <button type="button" @click="step--" x-show="step > 1"
                class="px-4 py-2 bg-gray-500 text-white rounded">Back
            </button>
            
            <button type="submit" x-show="step < maxSteps"
                class="px-4 py-2 bg-blue-500 text-white rounded">
                <span x-text="step === ( maxSteps - 1 ) ? 'Verify' : 'Next'"></span>
            </button>
            
            <button type="submit" x-show="step === maxSteps"
                class="px-4 py-2 bg-green-500 text-white rounded">Download & Save
            </button>
        </div>

        <!-- Loading Modal -->
        <div 
            x-show="isLoading" 
            style="display: none;" 
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            x-cloak
        >
            <div class="bg-white p-6 rounded shadow-lg text-center">
                <p class="text-lg font-semibold text-gray-700">Processing your request, please wait...</p>
                <!-- Optional spinner -->
                <div class="mt-4 flex justify-center">
                    <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div x-data="{ show: false, message: '', type: 'success' }"
            x-show="show"
            x-init="
                @if (session('success'))
                    show = true;
                    message = '{{ session('success') }}';
                    type = 'success';
                    setTimeout(() => show = false, 3000);
                @elseif (session('error'))
                    show = true;
                    message = '{{ session('error') }}';
                    type = 'error';
                    setTimeout(() => show = false, 3000);
                @endif
            "
            class="fixed inset-0 flex items-center justify-center text-black px-4 py-3 rounded-lg max-w-sm w-full mx-auto">
            <div class="flex items-center bg-white border border-gray-300 rounded-lg p-4 shadow-lg">
                <svg x-show="type === 'success'" class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"></path>
                </svg>
                <svg x-show="type === 'error'" class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-black font-semibold" x-text="message"></span>
            </div>
        </div>
    </form>
</div>
<script>
    // Global variable to track deduplication status
    window.scriptRunning = false;
    
    let count = 0;
    // Listen for page reload
    window.addEventListener('beforeunload', function (e) {
        if (window.scriptRunning && count > 0) {
            e.preventDefault();
            e.returnValue = 'A script is still running. Are you sure you want to leave?';

        } else {
            // Reset the count if no script is running
            count++;
        }
    });
</script>
@if(session('download_file'))
<script>
    window.onload = function() {
        window.open("{{ route('download.files') }}", "_blank");

        setTimeout(function() {
            window.location.href = "{{ route('home') }}";
        }, 1000);
    }
</script>
@endif
