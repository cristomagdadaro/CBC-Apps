<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends BaseModel
{
    use HasFactory;

    protected $table = 'participants';

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'sex',
        'age',
        'organization',
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
