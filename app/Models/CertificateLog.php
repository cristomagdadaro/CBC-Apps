<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CertificateLog extends BaseModel
{
    use HasFactory;

    protected $table = 'certificate_logs';

    protected $fillable = [
        'filename',
        'recipient_email',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    protected array $searchable = [
        'filename',
        'recipient_email',
        'status',
        'error_message',
        'processed_at',
    ];
}
