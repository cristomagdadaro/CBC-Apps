<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class UseRequestForm extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'use_request_forms';

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'request_type',
        'request_details',
        'request_purpose',
        'project_title',
        'date_of_use',
        'date_of_use_end',
        'time_of_use',
        'time_of_use_end',
        'labs_to_use',
        'equipments_to_use',
        'consumables_to_use',
    ];

    protected $casts = [
        'id' => 'string',
        'request_type'     => 'array',
        'labs_to_use'       => 'array',
        'equipments_to_use' => 'array',
        'consumables_to_use'=> 'array',
    ];

    protected array $searchable = [
        'id',
        'request_type',
        'request_details',
        'request_purpose',
        'project_title',
        'date_of_use',
        'date_of_use_end',
        'time_of_use',
        'time_of_use_end',
    ];
    
    public function equipments()
    {
        
    }

    public function laboratories()
    {
        
    }
}
