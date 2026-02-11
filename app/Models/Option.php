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

    /**
     * Get all options keyed storage_locations
     */
    public static function getStorageLocations()
    {
        return json_decode(static::getByKey('storage_locations'), true) ?? [];
    }

    /**
     * Get all options group by fes
     */
    public static function getRequestTypes()
    {
        $temp = static::getByGroup('fes');
        $newTemp = [];

        foreach ($temp as $value) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $newTemp = array_merge($newTemp, $decoded);
            }
        }

        return $newTemp;
    }

    /**
     * Get all options keyed stock_levels
     */
    public static function getStockLevels()
    {
        return json_decode(static::getByKey('stock_levels'), true) ?? [];
    }

    /**
     * Get all options keyed event_halls
     */
    public static function getEventHalls()
    {
        return json_decode(static::getByKey('event_halls'), true) ?? [];
    }

    /**
     * Get all options keyed laboratories
     */
    public static function getLaboratories()
    {
        return json_decode(static::getByKey('laboratories'), true) ?? [];
    }

    /**
     * Get all vehicle from the transactions table join with items table and category_id of 8 for vehicles
     */
    public static function getVehicles()
    {
        return Transaction::join('items', 'transactions.item_id', '=', 'items.id')
            ->where('items.category_id', 8)
            ->selectRaw('items.description as name, concat(items.brand, " (", items.description, ")") as label')
            ->get();
    }

    public static function getApprovingOfficers()
    {
        return json_decode(static::getByKey('approving_officers'), true) ?? null;
    }

    public static function getEventResponseNotificationEmail()
    {
        return json_decode(static::getByKey('event_response_notification_email'), true) ?? [];
    }
    
    public static function getCenterChief()
    {
        return static::getByKey('center_chief') ?? 'Default Center Chief';
    }

    public static function getSexOptions()
    {
        $value = static::getByKey('sex');

        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

}
