<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTypeTemplate extends BaseModel
{
    use HasFactory, SoftDeletes, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'form_type_templates';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'slug',
        'name',
        'description',
        'icon',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'is_system' => 'boolean',
    ];

    protected array $searchable = [
        'slug',
        'name',
        'description',
    ];

    /**
     * Field definitions belonging to this template
     */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(FormFieldDefinition::class, 'form_type_template_id')
            ->orderBy('sort_order');
    }

    /**
     * User who created this template (null for system templates)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Event subforms using this template
     */
    public function eventSubforms(): HasMany
    {
        return $this->hasMany(EventSubform::class, 'form_type_template_id');
    }

    /**
     * Scope: System templates only
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: Custom (user-created) templates only
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Get the field schema array for this template
     */
    public function getFieldSchemaAttribute(): array
    {
        return $this->fieldDefinitions->map(function ($field) {
            return [
                'field_key' => $field->field_key,
                'field_type' => $field->field_type,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'description' => $field->description,
                'validation_rules' => $field->validation_rules ?? [],
                'options' => $field->options ?? [],
                'display_config' => $field->display_config ?? [],
                'field_config' => $field->field_config ?? [],
                'sort_order' => $field->sort_order,
                'is_system' => $field->is_system,
            ];
        })->toArray();
    }
}
