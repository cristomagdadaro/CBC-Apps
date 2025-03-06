<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends BaseModel
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

    public function event()
    {
        return $this->hasOne(Form::class , 'event_id', 'event_id');
    }

    public function participant()
    {
        return $this->hasOne(Participant::class, 'id', 'participant_id');
    }
}
