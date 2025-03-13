<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpWord\TemplateProcessor;

use App\Models\User;

class MultiStep extends Controller
{
    public function submitForm(Request $request)
    {
        // Handle form submission logic here
        // e.g., validate and save data to the database

        // Step-based validation rules
        $step = $request->input('current_step');
        $rules = $this->getValidationRulesForStep($request, $step);

        $program = $request->user()->program ? $request->user()->program->name : 'None';
        $usertype = $request->user()->usertype ? $request->user()->usertype : 'None';
        $poo = $request->user()->poo ? $request->user()->poo : 'None';
        $position = $request->user()->position ? $request->user()->position->name : 'None';
        $division_chief = $request->user()->program ? $request->user()->program->division_chief : 'None';

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // if ($step === "3") {
        //     dd($request);
        // }

        if ($validator->fails()) {
            // Redirect back to the current step with validation errors
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('step', $step); // Retain the current step
        }

        // Move to the next step
        $nextStep = $step + 1;

        if ($step === "1") {
            // Store the file temporarily
            $request->session()->put('activity_title', $request->input('activity_title'));
            $request->session()->put('stakeholder', $request->input('stakeholder'));
            $request->session()->put('focal_person', $request->input('focal_person'));
            $request->session()->put('contact_person', $request->input('contact_person'));
            $request->session()->put('contact_email', $request->input('contact_email'));
            $request->session()->put('contact_number', $request->input('contact_number'));
        }
        
        // Save the uploaded file if this is the file step (e.g., Step 2)
        if ($step === "2" && $request->file('request')) {
            $path = $request->file('request')->store('temp');
            $request->session()->put('uploaded_request_file_path', $path);
            $request->session()->put('uploaded_request_file_name', $request->file('request')->getClientOriginalName());
        }

        // if ($step === "333") {
        //     $request->session()->put('start_date', $request->input('start_date'));
        //     $request->session()->put('end_date', $request->input('end_date'));
        // }

        // if ($step === "4" && $request->file('served')) {
        //     // Store the file temporarily
        //     $path = $request->file('served')->store('temp');
        //     $request->session()->put('uploaded_served_file_path', $path);
        //     $request->session()->put('uploaded_served_file_name', $request->file('served')->getClientOriginalName());
        // }

        if ($step === "3") {
            // dd($request->user()->program->name);
            $current_date = date('M-d-Y');

            // Path to your template
            $templatePath = public_path('templates\Reporting Template.docx');

            // Create a new TemplateProcessor instance
            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('stakeholder', session('stakeholder'));
            $templateProcessor->setValue('focal_person', session('focal_person'));
            $templateProcessor->setValue('file_name', session('uploaded_request_file_name'));
            $templateProcessor->setValue('date_received', $current_date);
            $templateProcessor->setValue('contact_person', session('contact_person'));
            $templateProcessor->setValue('contact_email', session('contact_email'));
            $templateProcessor->setValue('contact_number', session('contact_number'));
            $templateProcessor->setValue('prepared_by', $request->user()->name);
            $templateProcessor->setValue('position', $position);
            $templateProcessor->setValue('division_chief', $division_chief);

            $request_name = escapeshellarg(session('uploaded_request_file_name'));
            $request_file = escapeshellarg(session('uploaded_request_file_path'));
            // $start_date = escapeshellarg(session('start_date'));
            // $end_date = escapeshellarg(session('end_date'));
            
            $scriptPath = base_path('storage/scripts/fuzzy_match.py');

            set_time_limit(0); // Unlimited execution time

            try {
                $output = shell_exec("python $scriptPath $request_name $request_file $program $usertype $poo");

                if ($output === null) {
                    Log::error("Python script execution failed.");
                    // return back()->with('error', 'Python script execution failed.');
                    return back()->with('error', 'Something went wrong.');
                }

                $data = json_decode($output, true);

                if (!$data || $data === null) {
                    Log::error("Invalid JSON response from Python script: " . $output);
                    // return back()->with('error', 'Invalid response from the Python script.');
                    return back()->with('error', 'Invalid response from the Python script.');
                }
    
                if(isset($data['status']) && $data['status'] == 'error'){
                    // dd($data);
                    return back()->with('error', $data['message']);
                }
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            if ($data) {
                $currentDateTime = date('m-d-Y His');
                $fileName = "RDV Summary of Results {$currentDateTime}.docx";
                
                $total = $data['duplicate_list'] + $data['invalid_list'] + $data['served_list'];
                $templateProcessor->setValue('possible_duplicates', $data['duplicate_list'] > 0 ? $data['duplicate_list'] : 'None');
                $templateProcessor->setValue('invalid_records', $data['invalid_list'] > 0 ? $data['invalid_list'] : 'None');
                $templateProcessor->setValue('served_individuals', $data['served_list'] > 0 ? $data['served_list'] : 'None');
                $templateProcessor->setValue('total_valid', $data['clean_list'] > 0 ? $data['clean_list'] : 'None');
                $templateProcessor->setValue('overall_total', $total > 0 ? $total : 'None');

                $request->session()->put('master_list', $data['master_list']);
                $request->session()->put('clean_list', $data['clean_list']);
                $request->session()->put('invalid_list', $data['invalid_list']);
                $request->session()->put('duplicate_list', $data['duplicate_list']);
                $request->session()->put('served_list', $data['served_list']);

                // Get the path to the Documents folder
                $documentsPath = rtrim(getenv('USERPROFILE') ?: getenv('HOME'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Documents';

                // Output file path in the Documents folder
                $outputPath = $documentsPath . DIRECTORY_SEPARATOR . $fileName;

                // Save the file
                $templateProcessor->saveAs($outputPath);

                return redirect()->back()
                    ->with('step', $nextStep)
                    ->with('master_list', $data['master_list'])
                    ->with('clean_list', $data['clean_list'])
                    ->with('invalid_list', $data['invalid_list'])
                    ->with('duplicate_list', $data['duplicate_list'])
                    ->with('served_list', $data['served_list'])
                    ->withInput();
            } else {
                return redirect()->back()->with('step', $nextStep)->withInput();
            }
        }
    
        // Redirect to the next step if validation passes, or complete the form if it's the final step
        if ($nextStep < 4) {
            return redirect()->back()->with('step', $nextStep)->withInput();
        }

        // dd(session()->all());
        
        if ($step === "4") {
            try {
                if ($program == 'ECT') {
                    
                    DB::table('ect_requests')->insert([
                        'stakeholder' => session('stakeholder'),
                        'focal_person' => session('focal_person'),
                        'file_name' => session('uploaded_request_file_name'),
                        'date_received' => date('Y-m-d'),
                        'contact_person' => session('contact_person'),
                        'contact_email' => session('contact_email'),
                        'contact_number' => session('contact_number'),
                        'raw_list' => $request->input('master_list'),
                        'possible_duplicates' => $request->input('duplicate_list'),
                        'invalid_records' => $request->input('invalid_list'),
                        'served_individuals' => $request->input('served_list'),
                        'total_valid' => $request->input('clean_list'),
                        'prepared_by' => $request->user()->name,
                        'division_chief' => $division_chief
                    ]);
                }

                if ($program == 'AICS') {
                    
                    DB::table('aics_requests')->insert([
                        'activity_title' => session('activity_title'),
                        'stakeholder' => NULL,
                        'focal_person' => session('focal_person'),
                        'file_name' => session('uploaded_request_file_name'),
                        'date_received' => date('Y-m-d'),
                        'contact_person' => session('contact_person'),
                        'contact_email' => session('contact_email'),
                        'contact_number' => session('contact_number'),
                        'raw_list' => $request->input('master_list'),
                        'possible_duplicates' => $request->input('duplicate_list'),
                        'invalid_records' => $request->input('invalid_list'),
                        'served_individuals' => $request->input('served_list'),
                        'total_valid' => $request->input('clean_list'),
                        'prepared_by' => $request->user()->name,
                        'division_chief' => $division_chief
                    ]);
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
                DB::rollback();
                return back()->with('error', 'Database error');
            }
        }

        $this->clearTemporaryFile();
        return redirect()->back()->with('success', 'Form submitted successfully!');
    }

    public function complete(Request $request)
    {

    }

    private function getValidationRulesForStep($request, $step)
    {
        switch ($step) {
            case 1:
                return [
                    'activity_title' => 'required|string|max:255',
                    'stakeholder' => 'required|string|max:255',
                    'focal_person' => 'required|string|max:255',
                    'contact_person' => 'required|string|max:255',
                    'contact_email' => 'required|email|max:255',
                    'contact_number' => 'required|string|max:255',
                ];
            case 2:
                return [
                    'request' => $request->has('request') ? 'required|file|mimes:xlsx|max:2048' : 'nullable|file|mimes:xlsx|max:2048',
                ];
            // case 2:
            //     return [
            //         'start_date' => 'required|date',
            //         'end_date' => 'required|date|after_or_equal:start_date',
            //     ];
            case 3;
                return [];
            default:
                return [];
        }
    }

    public function clearTemporaryFile()
    {
        // Define the path to the temporary directory
        $tempDirectory = 'temp';

        // Check if the directory exists and delete all files within it
        if (Storage::exists($tempDirectory)) {
            Storage::deleteDirectory($tempDirectory);
            // Optionally, recreate the directory if needed
            Storage::makeDirectory($tempDirectory);
        }

        // session()->flush();
        
        session()->forget(
            [
                'uploaded_request_file_path', 
                'uploaded_request_file_name',
                // 'start_date', 
                // 'end_date',
                'activity_title',
                'stakeholder',
                'focal_person',
                'contact_person',
                'contact_email',
                'contact_number',
            ]
        );
    }

    public function show($path, $name)
    {
        // Construct the full path in storage
        $fullPath = 'temp/' . $path;

        if (Storage::exists($fullPath)) {
            return Storage::download($fullPath, $name);
        }

        abort(404); // File not found
    }
}
