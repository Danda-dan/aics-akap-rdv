<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EctServedDatabase extends Model
{
    protected $fillable = [
        'payroll_number',
        'first_name',
        'middle_name',
        'last_name',
        'extension',
        'barangay',
        'lgu',
        'sdo',
        'date_paid',
        'source',
    ]
}
