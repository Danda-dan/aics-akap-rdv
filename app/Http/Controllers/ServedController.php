<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServedController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('aics_served_database')
            ->select(
                'id',
                'control_number',
                'first_name',
                'middle_name',
                'last_name',
                'extension_name',
                'birth_day',
                'birth_month',
                'birth_year',
                'last_served_location',
                'date_last_served',
                'event_type',
                'program',
                'charging',
                'created_at'
            );

        // Unified search (name + location)
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('last_served_location', 'like', "%{$search}%")

                // full name search: first + last
                ->orWhereRaw(
                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                    ["%{$search}%"]
                )

                // full name search: first + middle + last
                ->orWhereRaw(
                    "CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?",
                    ["%{$search}%"]
                );
            });
        }

        // Filter by date_last_served
        if ($request->filled('date_from')) {
            $query->whereRaw(
                "STR_TO_DATE(date_last_served, '%m-%d-%Y') >= ?",
                [$request->date_from . ' 00:00']
            );
        }

        if ($request->filled('date_to')) {
            $query->whereRaw(
                "STR_TO_DATE(date_last_served, '%m-%d-%Y') <= ?",
                [$request->date_to . ' 23:59']
            );
        }

        // Pagination (10 per page)
        $served_benes = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString(); // preserve filters

        return view('served', compact('served_benes'));
    }
}
