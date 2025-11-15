<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRequirement extends BaseModel
{
    use HasFactory;

    protected $table = 'event_requirements';

    protected $fillable = [
        'id',
        'event_id',
        'form_type',
        'is_required',
        'config',
    ];

    protected $casts = [
        'id' => 'string',
        'is_required' => 'boolean',
        'config' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'event_id', 'event_id');
    }
}

