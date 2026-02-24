<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CleanListController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('aics_clean_list')
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
                'requesting_partner',
                'payout_site',
                'sdo',
                'poo_rdv_focal',
                'force_entry',
                'date_processed',
                'created_at'
            );

        // Unified search (name + location)
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('payout_site', 'like', "%{$search}%")

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
                "STR_TO_DATE(date_processed, '%m-%d-%Y') >= ?",
                [$request->date_from . ' 00:00']
            );
        }

        if ($request->filled('date_to')) {
            $query->whereRaw(
                "STR_TO_DATE(date_processed, '%m-%d-%Y') <= ?",
                [$request->date_to . ' 23:59']
            );
        }

        // Pagination (10 per page)
        $clean_list = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString(); // preserve filters

        return view('clean-list', compact('clean_list'));
    }
}
