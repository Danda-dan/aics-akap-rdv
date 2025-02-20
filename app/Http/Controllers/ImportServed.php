<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImportServed extends Controller
{
    public function importForm(Request $request)
    {

        $request->validate([
            'served' => 'required|file|mimes:xlsx,xls|max:51200', // Max size 50MB
        ]);
        
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

            $output = shell_exec("python $scriptPath $request_name $request_file $program");

            $data = json_decode($output, true);

            // dd($data);
            $this->clearTemporaryFile();
            
            if($data["message"]){
                // Return a success response
                return redirect()->route('import-served')->with('success', 'Data saved successfully!');
            } else {
                // Return an error response
                return back()->with('error', 'An error occurred.');
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
