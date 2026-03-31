<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class NotificationLog extends BaseModel
{
    use HasFactory;
    use HasUuids;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'domain',
        'event_key',
        'notifiable_type',
        'notifiable_id',
        'recipient_email',
        'channel',
        'status',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_reason',
        'payload_meta',
        'correlation_id',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'payload_meta' => 'array',
    ];
}
