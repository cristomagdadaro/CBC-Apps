<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class EventScanLog extends BaseModel
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'event_scan_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'event_id',
        'registration_id',
        'scan_type',
        'status',
        'scanned_by',
        'scanned_at',
        'payload_hash',
        'signature',
        'terminal_id',
        'reason',
        'meta',
    ];

    protected $casts = [
        'id' => 'string',
        'scanned_at' => 'datetime',
        'meta' => 'array',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
