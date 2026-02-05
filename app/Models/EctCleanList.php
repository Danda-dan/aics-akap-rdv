<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EctCleanList extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'extension',
        'birth_day',
        'birth_month',
        'birth_year',
        'province',
        'city_municipality',
        'barangay',
        'purok',
        'file_source',
        'date_processed',
    ];
}
