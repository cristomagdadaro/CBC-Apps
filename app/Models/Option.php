<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Option extends BaseModel
{
    use HasFactory, Auditable, HasUuids;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    protected $table = 'options';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
        'type',
        'group',
        'options',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    protected array $searchable = [
        'key',
        'label',
        'description',
        'group',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }

    /**
     * Scope to filter options by group
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter options by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get option value by key
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first()?->value;
    }

    /**
     * Get multiple options by group as key => value pairs
     */
    public static function getByGroup($group)
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
