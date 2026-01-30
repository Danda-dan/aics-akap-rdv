<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Force Entry Upload') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="w-full max-w-2xl mx-auto mt-10">
                        <form method="POST" action="{{ route('upload.csv') }}" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="current_step" value="sample">

                            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-md">
                                <h2 class="text-xl font-bold mb-4">Import Force Entry</h2>

                                <input type="file" name="force_entry" id="force_entry" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-4 w-full">
                            
                                @error('force_entry') <span class="text-red-500 text-sm mb-4">{{ $message }}</span> @enderror
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="mt-8 flex justify-between">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Import
                                    <!-- <span x-text="step === ( maxSteps - 1 ) ? 'Verify' : 'Next'"></span> -->
                                </button>
                                
                                <!-- <button type="submit" x-show="step === maxSteps"
                                    class="px-4 py-2 bg-green-500 text-white rounded">Done
                                </button> -->
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
                                    <div class="mt-4">
                                        <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- @if (session('success'))
                                <div class="alert alert-success text-2xl mt-5">
                                    {{ session('success') }}
                                </div>
                            @endif -->

                            <div x-data="{ show: false, message: '', type: 'success' }"
                                x-show="show"
                                x-init="
                                    @if (session('success'))
                                        show = true;
                                        message = '{{ session('success') }}';
                                        type = 'success';
                                        setTimeout(() => show = false, 8000);
                                    @elseif (session('error'))
                                        show = true;
                                        message = '{{ session('error') }}';
                                        type = 'error';
                                        setTimeout(() => show = false, 8000);
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>