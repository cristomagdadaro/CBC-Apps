<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Requester extends Model
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    
    protected $table = 'requesters';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
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
