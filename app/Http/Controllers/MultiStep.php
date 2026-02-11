<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpWord\TemplateProcessor;

use Symfony\Component\Process\Process;

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

        $name = $request->user()->name ? $request->user()->name : 'None';
        $program = $request->user()->program ? $request->user()->program->name : 'None';
        $user_type = $request->user()->user_type ? $request->user()->user_type : 'None';
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
            $request->session()->put('payout_mode', $request->input('mode'));
            $request->session()->put('requesting_partner', $request->input('requesting_partner'));
            $request->session()->put('sdo', $request->input('sdo'));
            $request->session()->put('check_number', $request->input('check_number'));
            $request->session()->put('check_date_issued', $request->input('check_date_issued'));
            $request->session()->put('payout_site', $request->input('payout_site'));
            $request->session()->put('poo_rdv_focal', $request->input('poo_rdv_focal'));
            $request->session()->put('entry_type', $request->input('entry_type'));
            $request->session()->put('reason', $request->input('reason'));
        }
        
        // Save the uploaded file if this is the file step (e.g., Step 2)
        if ($step === "2" && $request->file('request')) {
            $path = $request->file('request')->store('temp');
            $request->session()->put('uploaded_request_file_path', $path);
            $request->session()->put('uploaded_request_file_name', $request->file('request')->getClientOriginalName());
        }

        if ($step === "3") {
            // dd($request->user()->program->name);
            $current_date = date('M-d-Y');

            // Path to your template
            $templatePath = public_path('templates\Reporting Template.docx');

            // Create a new TemplateProcessor instance
            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('requesting_partner', session('requesting_partner'));
            $templateProcessor->setValue('sdo', session('sdo'));
            $templateProcessor->setValue('file_name', session('uploaded_request_file_name'));
            $templateProcessor->setValue('date_received', $current_date);
            $templateProcessor->setValue('poo_rdv_focal', session('poo_rdv_focal'));
            $templateProcessor->setValue('check_number', session('check_number'));
            $templateProcessor->setValue('check_date_issued', session('check_date_issued'));
            $templateProcessor->setValue('payout_site', session('payout_site'));
            $templateProcessor->setValue('prepared_by', $request->user()->name);
            $templateProcessor->setValue('position', $position);
            $templateProcessor->setValue('division_chief', $division_chief);

            $name = escapeshellarg($name);

            $request_name = escapeshellarg(session('uploaded_request_file_name'));
            $request_file = escapeshellarg(session('uploaded_request_file_path'));

            $payout_mode = escapeshellarg(session('payout_mode'));
            $requesting_partner = escapeshellarg(session('requesting_partner'));
            $sdo = escapeshellarg(session('sdo'));
            $check_number = escapeshellarg(session('check_number'));
            $check_date_issued = escapeshellarg(session('check_date_issued'));
            $payout_site = escapeshellarg(session('payout_site'));
            $poo_rdv_focal = escapeshellarg(session('poo_rdv_focal'));
            $entry_type = escapeshellarg(session('entry_type'));
            $reason = escapeshellarg(session('reason'));
            
            $script_path = base_path('storage\scripts\fuzzy_match.py');

            // Get the path to the Documents folder
            $documentsPath = rtrim(getenv('USERPROFILE') ?: getenv('HOME'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Documents';

            set_time_limit(0); // Unlimited execution time

            try {
                // dd("Starting Python script execution...");
                // $output = shell_exec("python $script_path $request_name $request_file $program $user_type $poo");
                $process = new Process(['where', 'python']);
                $process->run();
                $output = trim($process->getOutput());
                $paths = preg_split('/\r\n|\r|\n/', $output);

                // Get the first path only
                $pythonPath = $paths[0] ?? null;
                // dd($pythonPath);

                $args = array_map(fn($arg) => trim($arg, "\""), [
                    $pythonPath,
                    $script_path,
                    $request_name,
                    $request_file,
                    $program,
                    $user_type,
                    $poo,
                    $documentsPath,
                    $entry_type,
                    $name,
                    $requesting_partner,
                    $sdo,
                    $check_number,
                    $check_date_issued,
                    $payout_site,
                    $poo_rdv_focal,
                    $payout_mode,
                    $reason
                ]);

                $process = new Process($args);
                $process->setTimeout(null);
                $process->run(); // run() if sync/blocking

                // dd("Process executed." . $process->getOutput());

                if (!$process->isSuccessful()) {
                    Log::error("Error from Python script: " . $process->getErrorOutput());
                    throw new \RuntimeException($process->getErrorOutput());
                }

                $output = $process->getOutput();

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

                // Output file path in the Documents folder
                $outputPath = $documentsPath . DIRECTORY_SEPARATOR . $fileName;

                // Save the file
                $templateProcessor->saveAs($outputPath);

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
                            'raw_list' => $data['master_list'],
                            'possible_duplicates' => $data['duplicate_list'],
                            'invalid_records' => $data['invalid_list'],
                            'served_individuals' => $data['served_list'],
                            'total_valid' => $data['clean_list'],
                            'prepared_by' => $request->user()->name,
                            'division_chief' => $division_chief
                        ]);
                    }

                    if ($program == 'AICS') {
                        
                        DB::table('aics_requests')->insert([
                            'requesting_partner' => session('requesting_partner'),
                            'payout_mode' => session('payout_mode'),
                            'sdo' => session('sdo'),
                            'check_number' => session('check_number'),
                            'file_name' => session('uploaded_request_file_name'),
                            'date_received' => date('Y-m-d'),
                            'check_date_issued' => session('check_date_issued'),
                            'payout_site' => session('payout_site'),
                            'poo_rdv_focal' => session('poo_rdv_focal'),
                            'raw_list' => $data['master_list'],
                            'possible_duplicates' => $data['duplicate_list'],
                            'invalid_records' => $data['invalid_list'],
                            'served_individuals' => $data['served_list'],
                            'total_valid' => $data['clean_list'],
                            'prepared_by' => $request->user()->name,
                            'division_chief' => $division_chief
                        ]);
                    }
                } catch (\Exception $e) {
                    // dd($e->getMessage());
                    DB::rollback();
                    return back()->with('error', 'Database error');
                }

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

        // $this->clearTemporaryFile();
        return redirect()->back()->with('success', 'Result was saved successfully.');
    }

    private function getValidationRulesForStep($request, $step)
    {
        switch ($step) {
            case 1:
                return [
                    'requesting_partner' => 'required|string|max:255',
                    'sdo'    => $request->mode === 'sdo' ? 'required|string' : 'nullable',
                    'check_number'   => $request->mode === 'sdo' ? 'required|string' : 'nullable',
                    'check_date_issued' => $request->mode === 'sdo' ? 'required' : 'nullable',
                    'payout_site'  => 'required|string|max:255',
                    'poo_rdv_focal' => 'required|string|max:255',
                ];
            case 2:
                return [
                    'request' => $request->has('request') ? 'required|file|mimes:csv,txt|max:51200' : 'nullable|file|mimes:csv|max:51200',
                ];
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
        
        session()->forget(
            [
                'uploaded_request_file_path', 
                'uploaded_request_file_name',
                'requesting_partner',
                'check_number',
                'check_date_issued',
                'sdo',
                'payout_site',
                'poo_rdv_focal',
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

    public function stopDeduplication()
    {
        $script_path = base_path('storage/scripts/fuzzy_match.py');

        // dd("Stopping deduplication process...");
        // Log::error("Entered stopDeduplication.");
        $args = array_map(fn($arg) => trim($arg, "\""), [
            'python',
            $script_path,
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        $process = new Process($args);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error("Error from Python script: " . $process->getErrorOutput());
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output = $process->getOutput();

        if ($output === null) {
            Log::error("Python script execution failed.");
            // return back()->with('error', 'Python script execution failed.');
            // return back()->with('error', 'Something went wrong.');
            return response()->json(['error' => 'Something went wrong.']);
        }

        $data = json_decode($output, true);

        if (!$data || $data === null) {
            Log::error("Invalid JSON response from Python script: " . $output);

            return response()->json(['error' => 'Invalid response from the Python script.']);
        }
    
        if(isset($data['status']) && $data['status'] == 'error'){
            // dd($data);
            return back()->with('error', $data['message']);
        }

        if ($data) {
            return response()->json(['error' => 'Deduplication process has been stopped successfully!']);
        }

        return response()->json(['status' => 'no-process']);
    }

    public function checkDeduplicationStatus()
    {
        $file = storage_path('app/output.json');
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return response()->json($data);
        }

        return response()->json(['status' => 'processing']);
    }

}
