<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PollenAnalysis extends BaseModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'image_path',
        'pollen_count',
        'inference_time_ms',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
