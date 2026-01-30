<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpWord\TemplateProcessor;

class ForceEntryController extends Controller
{
    public function importFile(Request $request)
    {

        $request->validate([
            'force_entry' => 'required|file|mimes:csv|max:51200', // Max size 50MB
        ]);

        $name = $request->user()->name ? $request->user()->name : 'None';
        $program = $request->user()->program ? $request->user()->program->name : 'None';
        $user_type = $request->user()->user_type ? $request->user()->user_type : 'None';
        $poo = $request->user()->poo ? $request->user()->poo : 'None';
        $position = $request->user()->position ? $request->user()->position->name : 'None';
        $division_chief = $request->user()->program ? $request->user()->program->division_chief : 'None';

        // dd($request);
        if ($request->hasFile('force_entry')) {
            $file = $request->file('force_entry');
            $filePath = $file->store('temp');

            $request->session()->put('uploaded_force_entry_file_path', $filePath);
            $request->session()->put('uploaded_force_entry_file_name', $file->getClientOriginalName());

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

            $name = escapeshellarg($name);

            $request_name = escapeshellarg(session('uploaded_force_entry_file_name'));
            $request_file = escapeshellarg(session('uploaded_force_entry_file_path'));
            
            $script_path = base_path('storage/scripts/fuzzy_match.py');
            $documentsPath = rtrim(getenv('USERPROFILE') ?: getenv('HOME'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Documents';

            set_time_limit(0); // Unlimited execution time
            
            try {
                // $output = shell_exec("python $scriptPath $request_name $request_file");
                $file = "FORCE_ENTRY";
                $output = shell_exec("python $script_path $request_name $request_file $program $user_type $poo $documentsPath $file $name");

                if ($output === null) {
                    Log::error("Python script execution failed.");
                    // return back()->with('error', 'Python script execution failed.');
                    return redirect()->back()->with('error', 'Something went wrong.');
                }

                $data = json_decode($output, true);

                if (!$data || $data === null) {
                    Log::error("Invalid JSON response from Python script: " . $output);
                    // return back()->with('error', 'Invalid response from the Python script.');
                    return redirect()->back()->with('error', 'Something went wrong.');
                }

                if(isset($data['status']) && $data['status'] == 'error'){
                    // dd($data);
                    return redirect()->back()->with('error', $data['message']);
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

                $this->clearTemporaryFile();

                return redirect()->back()->with('success', 'Done importing force entry data.');
            } else {
                return redirect()->back()->with('error', 'Something went wrong.');
            }
        } else {
            // Return an error response
            return back()->with('error', 'An error occurred.');
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
                'uploaded_no_show_file_path', 
                'uploaded_no_show_file_name',
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
}
