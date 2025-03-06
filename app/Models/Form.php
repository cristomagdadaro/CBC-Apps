<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected   $table = 'forms';
    protected $fillable = [
        'id',
        'event_id',
        'title',
        'description',
        'details',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'venue',
        'has_pretest',
        'has_posttest',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
