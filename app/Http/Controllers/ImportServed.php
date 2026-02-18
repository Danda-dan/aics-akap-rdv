<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ImportServed extends Controller
{
    public function importForm(Request $request)
    {
        $request->validate([
            'served' => 'required|file|mimes:csv,txt|max:51200', // Max size 50MB
        ]);
        
        $name = $request->user()->name ? $request->user()->name : 'None';
        $program = $request->user()->program ? $request->user()->program->name : 'None';

        // dd($request);
        
        if ($request->hasFile('served')) {
            $file = $request->file('served');
            $filePath = $file->store('temp');

            $request->session()->put('uploaded_served_file_path', $filePath);
            $request->session()->put('uploaded_served_file_name', $file->getClientOriginalName());

            // dd(session('uploaded_served_file_name'));

            $request_name = escapeshellarg(session('uploaded_served_file_name'));
            $request_file = escapeshellarg(session('uploaded_served_file_path'));
            
            $scriptPath = base_path('storage/scripts/import_served_database.py');

            set_time_limit(0); // Unlimited execution time

            try{
                $name = escapeshellarg($name);
                $output = shell_exec("python $scriptPath $request_name $request_file $program $name");

                if ($output === null) {
                    Log::error("Python script execution failed.");
                    // return back()->with('error', 'Python script execution failed.');
                    return redirect()->back()->with('error', 'Error processing request.');
                }

                $data = json_decode($output, true);

                if (!$data || $data === null) {
                    Log::error("Invalid JSON response from Python script: " . $output);
                    // return back()->with('error', 'Invalid response from the Python script.');
                    return redirect()->back()->with('error', 'Error processing request.');
                }
    
                // dd($data);
                $this->clearTemporaryFile();
                
                if(isset($data['message']) && $data["message"]){
                    // Return a success response
                    // dd($data);
                    return redirect()->route('import-files')->with('success', 'Data saved successfully!');
                } elseif (isset($data['message']) && !$data["message"]) {
                    if(isset($data['invalid_date']) && $data["invalid_date"]){
                        $invalidRows = $data['invalid_rows'] ?? [];
                        // dd($invalidRows);
                        // $invalidRowsString = implode(', ', $invalidRows);
                        Log::error("Invalid birthdate values");
                        return back()->with('error', 'Invalid birthdate values. Please check the file and try again.');
                    } else {
                        $missingColumns = $data['missing_cols'] ?? [];
                        $missingColumnsString = implode(', ', $missingColumns);
                        Log::error("Wrong file template: Missing columns/data: " . $missingColumnsString);
                        return back()->with('error', 'Wrong file template: Missing columns/data: ' . $missingColumnsString);
                    }
                    
                } else {
                    // Return an error response
                    Log::error("Unexpected response from Python script: " . $output);
                    return back()->with('error', 'Error processing request.');
                }
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
        } else {
            // Return an error response
            return back()->with('error', 'Error processing request.');
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
                'uploaded_served_file_path', 
                'uploaded_served_file_name',
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
