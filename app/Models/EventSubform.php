<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSubform extends BaseModel
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

    protected $table = 'event_subforms';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'event_id',
        'form_type',
        'form_type_template_id',
        'step_type',
        'step_order',
        'is_enabled',
        'open_from',
        'open_to',
        'is_required',
        'max_slots',
        'config',
        'field_schema',
        'visibility_rules',
        'completion_rules',
    ];

    protected $casts = [
        'id' => 'string',
        'is_required' => 'boolean',
        'max_slots' => 'integer',
        'config' => 'array',
        'field_schema' => 'array',
        'is_enabled' => 'boolean',
        'open_from' => 'datetime:Y-m-d H:i:s',
        'open_to' => 'datetime:Y-m-d H:i:s',
        'visibility_rules' => 'array',
        'completion_rules' => 'array',
    ];

    protected array $searchable = [
        'event_id',
        'form_type',
        'max_slots',
        'config'
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'event_id', 'event_id');
    }

    /**
     * Form type template this subform is based on
     */
    public function template()
    {
        return $this->belongsTo(FormTypeTemplate::class, 'form_type_template_id');
    }

    /**
     * Get the resolved field schema (from field_schema column or template)
     */
    public function getResolvedFieldSchemaAttribute(): array
    {
        // First check if we have a custom field_schema
        if (!empty($this->field_schema)) {
            return $this->field_schema;
        }

        // Fall back to template's schema
        if ($this->template) {
            return $this->template->field_schema;
        }

        // Fall back to legacy config lookup
        return $this->getLegacyFieldSchema();
    }

    /**
     * Get field schema from legacy config/subformtypes.php
     */
    protected function getLegacyFieldSchema(): array
    {
        $legacyRules = config('subformtypes.' . $this->form_type, []);
        
        if (empty($legacyRules)) {
            return [];
        }

        $schema = [];
        $order = 0;

        foreach ($legacyRules as $fieldKey => $rule) {
            $schema[] = [
                'field_key' => $fieldKey,
                'field_type' => $this->inferFieldTypeFromRule($rule),
                'label' => $this->generateLabelFromKey($fieldKey),
                'placeholder' => null,
                'validation_rules' => ['required' => str_contains($rule, 'required')],
                'options' => $this->extractOptionsFromRule($rule),
                'display_config' => [],
                'field_config' => [],
                'sort_order' => $order++,
                'is_system' => true,
            ];
        }

        return $schema;
    }

    /**
     * Infer field type from Laravel validation rule
     */
    protected function inferFieldTypeFromRule(string $rule): string
    {
        if (str_contains($rule, 'email')) return 'email';
        if (str_contains($rule, 'integer') || str_contains($rule, 'numeric')) return 'number';
        if (str_contains($rule, 'boolean')) return 'checkbox';
        if (str_contains($rule, 'date')) return 'date';
        if (str_contains($rule, 'file') || str_contains($rule, 'image')) return 'file';
        if (str_contains($rule, 'in:')) return 'select';
        if (str_contains($rule, 'exists:loc_cities')) return 'address';
        return 'text';
    }

    /**
     * Generate human-readable label from field key
     */
    protected function generateLabelFromKey(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Extract options from 'in:opt1,opt2,opt3' rule
     */
    protected function extractOptionsFromRule(string $rule): array
    {
        if (preg_match('/in:([^|]+)/', $rule, $matches)) {
            $values = explode(',', $matches[1]);
            return array_map(fn($v) => ['value' => trim($v), 'label' => trim($v)], $values);
        }
        return [];
    }

    /**
     * Determine if this requirement/form is currently open based on its window.
     */
    public function isOpen(): bool
    {
        $now = Carbon::now();

        $openFrom = $this->open_from ?? data_get($this->config, 'open_from');
        $openTo = $this->open_to ?? data_get($this->config, 'open_to');

        if ($openFrom && $now->lt(Carbon::parse($openFrom))) {
            return false;
        }

        if ($openTo && $now->gt(Carbon::parse($openTo))) {
            return false;
        }

        return true;
    }

    public function subformResponses()
    {
        return $this->hasMany(EventSubformResponse::class, 'form_parent_id', 'id');
    }

    public function registrations()
    {
        return $this->hasManyThrough(
            Registration::class,
            EventSubformResponse::class,
            'form_parent_id', // event_subform_responses.form_parent_id
            'id',              // registrations.id
            'id',              // event_subforms.id
            'participant_id'   // event_subform_responses.participant_id
        );
    }

    public function responses()
    {
        return $this->hasMany(EventSubformResponse::class, 'form_parent_id', 'id')->with('participant');
    }

    // get all participants who submitted responses for this requirement
    public function participants()
    {
        return $this->hasManyThrough(
            Participant::class,
            EventSubformResponse::class,
            'form_parent_id', // event_subform_responses.form_parent_id
            'id',              // participants.id
            'id',              // event_subforms.id
            'participant_id'   // event_subform_responses.participant_id
        );
    }
}
