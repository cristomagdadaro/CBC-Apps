<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Participant extends BaseModel
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

    protected $table = 'participants';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'sex',
        'age',
        'organization',
        'designation',
        'is_ip',
        'is_pwd',
        'city_address',
        'province_address',
        'country_address',
        'agreed_tc',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'participant_id', 'id');
    }
}
