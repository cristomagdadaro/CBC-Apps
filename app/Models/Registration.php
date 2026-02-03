<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Registration extends BaseModel
{
    use HasFactory, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'registrations';
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Set the primary key as a string
    protected $fillable = [
        'id',
        'event_subform_id',
        'participant_id',
        'attendance_type',
        'checked_in_at',
        'checked_in_by',
        'checkin_source',
    ];

    protected $casts = [
        'id' => 'string',
        'checked_in_at' => 'datetime',
    ];

    public function form():BelongsTo
    {
        return $this->belongsTo(Form::class, 'event_subform_id', 'event_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
