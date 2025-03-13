<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ForceEntryController extends Controller
{
    public function importFile(Request $request)
    {

        $request->validate([
            'force_entry' => 'required|file|mimes:csv|max:51200', // Max size 50MB
        ]);

        // dd($request);
        if ($request->hasFile('force_entry')) {
            $file = $request->file('force_entry');
            $filePath = $file->store('temp');

            $request->session()->put('uploaded_force_entry_file_path', $filePath);
            $request->session()->put('uploaded_force_entry_file_name', $file->getClientOriginalName());

            // dd(session('uploaded_force_entry_file_name'));

            $request_name = escapeshellarg(session('uploaded_force_entry_file_name'));
            $request_file = escapeshellarg(session('uploaded_force_entry_file_path'));
            
            $scriptPath = base_path('storage/scripts/remove_no_show.py');

            set_time_limit(0); // Unlimited execution time
            
            try {
                $output = shell_exec("python $scriptPath $request_name $request_file");

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

                // dd($data);
                $this->clearTemporaryFile();
                
                if(isset($data['message']) && $data["message"]){
                    // Return a success response
                    return redirect()->route('no-show')->with('success', 'No Show Clients removed from the clean list successfully!');
                } else {
                    // Return an error response
                    return back()->with('error', 'An error occurred.');
                }
            } catch (\Exception $e) {
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
