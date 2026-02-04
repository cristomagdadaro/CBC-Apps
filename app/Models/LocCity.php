<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocCity extends Model
{
    protected $table = 'loc_cities';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'city',
        'province',
        'region',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
