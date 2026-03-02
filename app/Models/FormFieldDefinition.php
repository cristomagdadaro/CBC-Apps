<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormFieldDefinition extends BaseModel
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

    protected $table = 'form_field_definitions';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'form_type_template_id',
        'field_key',
        'field_type',
        'label',
        'placeholder',
        'description',
        'validation_rules',
        'options',
        'display_config',
        'field_config',
        'sort_order',
        'is_system',
    ];

    protected $casts = [
        'id' => 'string',
        'validation_rules' => 'array',
        'options' => 'array',
        'display_config' => 'array',
        'field_config' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
    ];

    protected array $searchable = [
        'field_key',
        'field_type',
        'label',
    ];

    /**
     * Template this field belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTypeTemplate::class, 'form_type_template_id');
    }

    /**
     * Scope: Filter by field type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('field_type', $type);
    }

    /**
     * Scope: System fields only
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: Required fields only
     */
    public function scopeRequired($query)
    {
        return $query->whereJsonContains('validation_rules->required', true);
    }

    /**
     * Get Laravel validation rule string from this field's configuration
     */
    public function getLaravelValidationRule(): array
    {
        $rules = [];
        $config = $this->validation_rules ?? [];

        if (!empty($config['required'])) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-based rules
        switch ($this->field_type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'time':
                $rules[] = 'date_format:H:i';
                break;
            case 'datetime':
                $rules[] = 'date_format:Y-m-d H:i';
                break;
            case 'file':
                $rules[] = 'file';
                if (!empty($config['max_size'])) {
                    $rules[] = 'max:' . $config['max_size'];
                }
                if (!empty($config['accept'])) {
                    $rules[] = 'mimes:' . str_replace('.', '', $config['accept']);
                }
                break;
            case 'checkbox':
            case 'checkbox_agreement':
                $rules[] = 'boolean';
                break;
            case 'checkbox_group':
            case 'checkbox_grid':
                $rules[] = 'array';
                break;
            case 'select':
            case 'radio':
            case 'radio_grid':
                if (!empty($this->options)) {
                    $values = collect($this->options)->pluck('value')->implode(',');
                    $rules[] = 'in:' . $values;
                }
                break;
            case 'likert':
            case 'likert_scale':
            case 'linear_scale':
                $rules[] = 'integer';
                $fieldConfig = $this->field_config ?? [];
                $min = $fieldConfig['min'] ?? 1;
                $max = $fieldConfig['max'] ?? 5;
                $rules[] = "between:{$min},{$max}";
                break;
            case 'location_city':
            case 'location_province':
            case 'location_region':
                $rules[] = 'string';
                break;
            case 'section_header':
            case 'paragraph':
                // These are non-input fields, so they shouldn't be validated
                return [];
            default:
                $rules[] = 'string';
        }

        // Generic constraints
        if (isset($config['min'])) {
            $rules[] = 'min:' . $config['min'];
        }
        if (isset($config['max'])) {
            $rules[] = 'max:' . $config['max'];
        }
        if (!empty($config['pattern'])) {
            $rules[] = 'regex:' . $config['pattern'];
        }

        return $rules;
    }

    /**
     * Field type constants
     */
    public const FIELD_TYPES = [
        'text' => ['label' => 'Short Answer', 'has_options' => false, 'icon' => 'text'],
        'textarea' => ['label' => 'Paragraph', 'has_options' => false, 'icon' => 'paragraph'],
        'number' => ['label' => 'Number', 'has_options' => false, 'icon' => 'number'],
        'email' => ['label' => 'Email', 'has_options' => false, 'icon' => 'email'],
        'phone' => ['label' => 'Phone', 'has_options' => false, 'icon' => 'phone'],
        'select' => ['label' => 'Dropdown', 'has_options' => true, 'icon' => 'dropdown'],
        'radio' => ['label' => 'Multiple Choice', 'has_options' => true, 'icon' => 'radio'],
        'checkbox' => ['label' => 'Checkbox', 'has_options' => false, 'icon' => 'checkbox'],
        'checkbox_group' => ['label' => 'Checkbox Group', 'has_options' => true, 'icon' => 'checkboxes'],
        'checkbox_agreement' => ['label' => 'Agreement Checkbox', 'has_options' => false, 'icon' => 'checkbox'],
        'checkboxes' => ['label' => 'Checkboxes (Multiple)', 'has_options' => true, 'icon' => 'checkboxes'],
        'date' => ['label' => 'Date', 'has_options' => false, 'icon' => 'calendar'],
        'time' => ['label' => 'Time', 'has_options' => false, 'icon' => 'clock'],
        'datetime' => ['label' => 'Date & Time', 'has_options' => false, 'icon' => 'datetimepicker'],
        'file' => ['label' => 'File Upload', 'has_options' => false, 'icon' => 'upload'],
        'likert' => ['label' => 'Likert Scale', 'has_options' => true, 'icon' => 'scale'],
        'likert_scale' => ['label' => 'Likert Scale', 'has_options' => true, 'icon' => 'scale'],
        'linear_scale' => ['label' => 'Linear Scale', 'has_options' => false, 'icon' => 'slider'],
        'rating' => ['label' => 'Star Rating', 'has_options' => false, 'icon' => 'star'],
        'checkbox_grid' => ['label' => 'Checkbox Grid', 'has_options' => true, 'icon' => 'grid'],
        'radio_grid' => ['label' => 'Radio Grid', 'has_options' => true, 'icon' => 'grid'],
        'address' => ['label' => 'Address (PH)', 'has_options' => false, 'icon' => 'location'],
        'location_city' => ['label' => 'City Selection', 'has_options' => false, 'icon' => 'location'],
        'location_province' => ['label' => 'Province Selection', 'has_options' => false, 'icon' => 'location'],
        'location_region' => ['label' => 'Region Selection', 'has_options' => false, 'icon' => 'location'],
        'multiple_choice_grid' => ['label' => 'Multiple Choice Grid', 'has_options' => true, 'icon' => 'grid'],
        'section_header' => ['label' => 'Section Title', 'has_options' => false, 'icon' => 'heading'],
        'paragraph' => ['label' => 'Paragraph Text', 'has_options' => false, 'icon' => 'richtext'],
        'rich_text' => ['label' => 'Rich Text Editor', 'has_options' => false, 'icon' => 'richtext'],
    ];

    /**
     * Get all available field types
     */
    public static function getFieldTypes(): array
    {
        return self::FIELD_TYPES;
    }
}
