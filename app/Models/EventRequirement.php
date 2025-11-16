<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

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
        'open_from',
        'open_to',
    ];

    protected $casts = [
        'id' => 'string',
        'is_required' => 'boolean',
        'config' => 'array',
        'open_from' => 'datetime',
        'open_to' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'event_id', 'event_id');
    }

    /**
     * Determine if this requirement/form is currently open based on its window.
     */
    public function isOpen(): bool
    {
        $now = Carbon::now();

        if ($this->open_from && $now->lt($this->open_from)) {
            return false;
        }

        if ($this->open_to && $now->gt($this->open_to)) {
            return false;
        }

        return true;
    }
}
