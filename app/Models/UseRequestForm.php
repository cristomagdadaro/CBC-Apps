<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UseRequestForm extends Model
{
    use HasFactory;

    protected $table = 'use_request_forms';

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'request_type',
        'request_details',
        'request_purpose',
        'project_title',
        'date_of_use',
        'time_of_use',
        'labs_to_use',
        'equipments_to_use',
        'consumables_to_use',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
