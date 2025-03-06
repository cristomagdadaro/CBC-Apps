<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'id',
        'event_id',
        'participant_id',
        'pretest_finished',
        'posttest_finished',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
