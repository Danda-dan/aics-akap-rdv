<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EctRequest extends Model
{
    protected $fillable = [
        'stakeholder',
        'focal_person',
        'file_name',
        'date_received',
        'possible_duplicates',
        'invalid_records',
        'served_individuals',
        'total_valid',
        'contact_person',
        'contact_number',
        'contact_email',
        'prepaid_by',
        'division_chief',
    ];

}
