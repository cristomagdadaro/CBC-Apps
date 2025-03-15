<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends BaseModel
{
    use HasFactory;

    protected $table = 'registrations';
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Set the primary key as a string
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

    public function form():BelongsTo
    {
        return $this->belongsTo(Form::class, 'event_id', 'event_id');
    }

    public function participant():BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id', 'id');
    }
}
