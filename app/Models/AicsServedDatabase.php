<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AicsServedDatabase extends Model
{
    protected $table = 'aics_served_database';

    protected $fillable = [
        'control_number',
        'first_name',
        'middle_name',
        'last_name',
        'extension_name',
        'birth_day',
        'birth_month',
        'birth_year',
        'province',
        'city_municipality',
        'date_last_served',
        'last_served_location',
        'program',
        'event_type',
        'partners',
        'charging',
        'sdo_incharge',
        'other_remarks',
        'file_source',
    ];

}
