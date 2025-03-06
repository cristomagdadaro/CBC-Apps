<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends BaseModel
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
        'has_preregistration',
    ];

    protected $casts = [
        'id' => 'string',
        'date_from' => 'datetime',
        'date_to' => 'datetime',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
