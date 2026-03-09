<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AicsServedDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReceiveAssistanceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assistance_data'                  => 'required|array|min:1',
            'assistance_data.*.first_name'     => 'required|string|max:50',
            'assistance_data.*.last_name'      => 'required|string|max:50',
            'assistance_data.*.control_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $records = $request->input('assistance_data');
        $insertedCount = 0;
        $skippedCount  = 0;

        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                // Skip duplicates: same control_number + first_name + last_name + event_type
                $exists = AicsServedDatabase::where('control_number', $record['control_number'] ?? '')
                    ->where('first_name', $record['first_name'] ?? '')
                    ->where('last_name', $record['last_name'] ?? '')
                    ->where('event_type', $record['event_type'] ?? '')
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                AicsServedDatabase::create([
                    'control_number'      => $record['control_number'] ?? '',
                    'first_name'          => $record['first_name'] ?? '',
                    'middle_name'         => $record['middle_name'] ?? null,
                    'last_name'           => $record['last_name'] ?? '',
                    'extension_name'      => $record['extension_name'] ?? null,
                    'birth_day'           => $record['birth_day'] ?? '',
                    'birth_month'         => $record['birth_month'] ?? '',
                    'birth_year'          => $record['birth_year'] ?? '',
                    'province'            => $record['province'] ?? null,
                    'city_municipality'   => $record['city_municipality'] ?? null,
                    'date_last_served'    => $record['date_last_served'] ?? null,
                    'last_served_location'=> $record['last_served_location'] ?? null,
                    'program'             => $record['program'] ?? null,
                    'event_type'          => $record['event_type'] ?? null,
                    'partners'            => $record['partners'] ?? null,
                    'charging'            => $record['charging'] ?? null,
                    'sdo_incharge'        => $record['sdo_incharge'] ?? null,
                    'other_remarks'       => $record['other_remarks'] ?? null,
                    'file_source'         => $record['file_source'] ?? null,
                ]);

                $insertedCount++;
            }

            DB::commit();

            return response()->json([
                'status'   => 'success',
                'message'  => "Inserted {$insertedCount} record(s). Skipped {$skippedCount} duplicate(s).",
                'inserted' => $insertedCount,
                'skipped'  => $skippedCount,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
