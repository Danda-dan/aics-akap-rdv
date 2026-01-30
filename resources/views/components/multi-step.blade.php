<!-- resources/views/components/multi-step-form.blade.php -->
<div x-data="{ step: {{ session('step', 1) }}, maxSteps: {{ count($steps) }} }" class="w-full max-w-2xl mx-auto mt-10">
    <!-- Step Indicator -->
    <div class="flex justify-between mb-8">
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
    <form x-data="{ isLoading: false, onStep: 3 }" x-init="$watch('isLoading', value => window.scriptRunning = value)" 
        x-on:submit="if (step === onStep) { isLoading = true }"
        method="POST" action="{{ route('form.submit') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="current_step" x-bind:value="step">
        <!-- Step 1 -->
        <div x-show="step === 1" class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <h2 class="text-xl font-bold mb-4">Step 1: Initial Details</h2>

            <input type="text" name="activity_title" value="{{ old('activity_title') }}" placeholder="Enter Activity Title" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('activity_title') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="stakeholder" value="{{ old('stakeholder') }}" placeholder="Enter Stakeholder" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('stakeholder') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="focal_person" value="{{ old('focal_person') }}" placeholder="Enter Focal Person" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('focal_person') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Enter Contact Person" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('contact_person') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="Enter Contact Email" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('contact_email') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="Enter Contact Number. Ex: 09123456789" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 1">
            @error('contact_number') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror
        </div>

        <!-- Step 2 -->
        <div x-show="step === 2" x-data="{ hasUploadedFile: {{ session('uploaded_request_file_name') ? 'true' : 'false' }} }"
            class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <h2 class="text-xl font-bold mb-4">Step 2: Import Raw File</h2>
            <!-- <input type="text" name="phone" placeholder="Phone Number" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" :required="step === 2">
            @error('phone') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror -->

            <input type="file" name="request" id="request" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" 
                :required="step === 2 && !hasUploadedFile">
        
            @error('request') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror

            @if(session('uploaded_request_file_name'))
                <p class="mt-2">Uploaded File: {{ session('uploaded_request_file_name') }}</p>
                <p class="text-gray-500">You can upload a new file if you wish to replace this one.</p>
            @endif
        </div>

        <!-- Step 3 -->
        <div x-show="step === 333" x-data="{ isChecked: false, hasUploadedFile: {{ session('uploaded_clean_file_name') ? 'true' : 'false' }} }"
            class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <h2 class="text-xl font-bold mb-4">Step 3: Filter Clean List by Date Range</h2>
            
            <div class="mb-4">
                <label for="start_date" class="block text-sm font-medium text-gray-600 mb-1">Start Date:</label>
                <input 
                    type="date" 
                    id="start_date" 
                    name="start_date" 
                    value="{{ session('start_date') }}" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div class="mb-4">
                <label for="end_date" class="block text-sm font-medium text-gray-600 mb-1">End Date:</label>
                <input 
                    type="date" 
                    id="end_date" 
                    name="end_date" 
                    value="{{ session('end_date') }}" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
                
            @error('clean') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror
        </div>

        <!-- Step 4 -->
        <!-- <div x-show="step === 4" x-data="{ hasUploadedFile: {{ session('uploaded_served_file_name') ? 'true' : 'false' }} }"
            class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
            <h2 class="text-xl font-bold mb-4">Step 4: Import Served Database</h2>
            <input type="file" name="served" placeholder="Served Database File" 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full" 
                :required="step === 4 && !hasUploadedFile">

            @error('served') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror
            
            @if(session('uploaded_served_file_name'))
                <p class="mt-2">Uploaded File: {{ session('uploaded_served_file_name') }}</p>
                <p class="text-gray-500">You can upload a new file if you wish to replace this one.</p>
            @endif
        </div> -->

        <!-- Step 5 -->
        <div x-show="step === 3">
            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
                <h2 class="text-2xl font-semibold text-center mb-6">Review Your Information</h2>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Step 1 Details</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Activity Title:</span> {{ session('activity_title') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Stakeholder:</span> {{ session('stakeholder') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Focal Person:</span> {{ session('focal_person') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Contact Person:</span> {{ session('contact_person') ?? 'N/A' }}</p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Contact Email:</span> {{ session('contact_email') ?? 'N/A' }}</p>
                        <p class="text-gray-600"><span class="font-semibold">Contact Number:</span> {{ session('contact_number') ?? 'N/A' }}</p>
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

                <!-- <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Step 3 Details</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Clean List Date Range</span></p>
                        <p class="text-gray-600 mb-2"><span class="font-semibold">Start Date:</span> {{ date('M-d-Y', strtotime(session('start_date'))) ?? 'N/A' }}</p>
                        <p class="text-gray-600"><span class="font-semibold">End Date:</span> {{ date('M-d-Y', strtotime(session('end_date'))) ?? 'N/A' }}</p>
                    </div>
                </div> -->

                <!-- <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Step 4 Details</h3>
                    <div class="bg-gray-50 p-4 rounded-md shadow-inner">
                        <p class="text-gray-600"><span class="font-semibold">Served Database:</span> 
                            @if(session('uploaded_served_file_name'))
                                <a href="{{ route('file.show', 
                                [
                                    'path' => basename(session('uploaded_served_file_path')),
                                    'name' => basename(session('uploaded_served_file_name')),
                                ]) }}" target="_blank" class="text-blue-500 underline">
                                    {{ session('uploaded_served_file_name') }}
                                </a>
                            @else
                                No document uploaded.
                            @endif
                        </p>
                    </div>
                </div> -->
            </div>
        </div>

        <!-- Step 6 -->
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
        <div class="mt-8 flex justify-between">
            <button type="button" @click="step--" x-show="step > 1"
                class="px-4 py-2 bg-gray-500 text-white rounded">Back
            </button>
            
            <button type="submit" x-show="step < maxSteps"
                class="px-4 py-2 bg-blue-500 text-white rounded">
                <span x-text="step === ( maxSteps - 1 ) ? 'Verify' : 'Next'"></span>
            </button>
            
            <button type="submit" x-show="step === maxSteps"
                class="px-4 py-2 bg-green-500 text-white rounded">Save
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


        <!-- @if (session('error'))
            <div class="alert alert-danger text-2xl mt-5">
                {{ session('error') }}
            </div>
        @endif -->
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

            // Attempt to stop the Python script
            // navigator.sendBeacon('/stop-deduplication');
            // fetch('/stop-deduplication', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            //     },
            //     body: JSON.stringify({}),
            //     keepalive: true
            // });
        } else {
            // Reset the count if no script is running
            count++;
        }
    });

    // let pollingInterval = null;

    // function checkStatus() {
    //     fetch("{{ route('dedup.status') }}")
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.status === 'done') {
    //                 clearInterval(pollingInterval); // stop checking
    //                 console.log('Process complete:', data);

    //                 // You can now update the UI or redirect the user
    //                 document.getElementById("status-message").innerText = "Deduplication completed!";
    //                 // Optionally reload or fetch more data
    //             } else {
    //                 console.log('Still processing...');
    //             }
    //         })
    //         .catch(error => {
    //             console.error("Error checking status:", error);
    //         });
    // }

    // Start polling every 3 seconds
    // document.addEventListener('DOMContentLoaded', function () {
    //     if (window.scriptRunning) {
    //         pollingInterval = setInterval(checkStatus, 3000);
    //     }
    // });
</script>