<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requester extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requesters';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'affiliation',
        'position',
        'email',
        'phone',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    protected array $searchable = [
        'name',
        'affiliation',
        'position',
        'email',
        'phone',
    ];
}
