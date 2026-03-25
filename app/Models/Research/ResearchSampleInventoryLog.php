<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchSampleInventoryLog extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'sample_id',
        'action',
        'barcode_value',
        'qr_payload',
        'context',
        'performed_by',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(ResearchSample::class, 'sample_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }
}
