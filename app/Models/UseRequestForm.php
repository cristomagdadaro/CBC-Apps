<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Traits\Auditable;
use App\Repositories\OptionRepo;

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
        'request_type' => 'array',
        'labs_to_use' => 'array',
        'equipments_to_use' => 'array',
        'consumables_to_use' => 'array',
    ];

    protected $appends = [
        'equipments_labels',
        'laboratories_labels',
        'consumables_labels',
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

    public function equipments(): Collection
    {
        $resolved = $this->getResolvedLabels('resolved_equipments_labels');

        if ($resolved !== null) {
            return collect($resolved);
        }

        $ids = $this->normalizeSelectionValues($this->equipments_to_use);

        if (empty($ids)) {
            return collect();
        }

        $items = Item::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'description', 'brand'])
            ->keyBy('id');

        return collect($ids)
            ->map(function ($id) use ($items) {
                $item = $items->get($id);

                if (!$item) {
                    return null;
                }

                return trim($item->name . ($item->brand ? " - {$item->brand}" : '') . ($item->description ? " ({$item->description})" : ''));
            })
            ->filter()
            ->values();
    }

    public function laboratories(): Collection
    {
        $resolved = $this->getResolvedLabels('resolved_laboratories_labels');

        if ($resolved !== null) {
            return collect($resolved);
        }

        $ids = $this->normalizeSelectionValues($this->labs_to_use);

        if (empty($ids)) {
            return collect();
        }

        $allLabs = app(OptionRepo::class)
            ->getLaboratories()
            ->mapWithKeys(fn ($lab) => [(string) ($lab['value'] ?? '') => $lab['label'] ?? null]);

        return collect($ids)
            ->map(function ($id) use ($allLabs) {
                return $allLabs->get((string) $id);
            })
            ->filter()
            ->values();
    }

    //for consumables as well
    public function consumables(): Collection
    {
        $resolved = $this->getResolvedLabels('resolved_consumables_labels');

        if ($resolved !== null) {
            return collect($resolved);
        }

        $ids = $this->normalizeSelectionValues($this->consumables_to_use);

        if (empty($ids)) {
            return collect();
        }

        $items = Item::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'description', 'brand'])
            ->keyBy('id');

        return collect($ids)
            ->map(function ($id) use ($items) {
                $item = $items->get($id);

                if (!$item) {
                    return null;
                }

                return trim($item->name . ($item->brand ? " - {$item->brand}" : '') . ($item->description ? " ({$item->description})" : ''));
            })
            ->filter()
            ->values();
    }

    public function getEquipmentsLabelsAttribute(): array
    {
        return $this->equipments()->all();
    }

    public function getLaboratoriesLabelsAttribute(): array
    {
        return $this->laboratories()->all();
    }

    public function getConsumablesLabelsAttribute(): array
    {
        return $this->consumables()->all();
    }

    public function setResolvedSelectionLabels(array $labels): self
    {
        $this->setAttribute('resolved_equipments_labels', array_values(array_filter($labels['equipments'] ?? [])));
        $this->setAttribute('resolved_laboratories_labels', array_values(array_filter($labels['laboratories'] ?? [])));
        $this->setAttribute('resolved_consumables_labels', array_values(array_filter($labels['consumables'] ?? [])));

        return $this;
    }

    private function normalizeSelectionValues(mixed $values): array
    {
        return collect((array) $values)
            ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
            ->map(fn ($value) => mb_substr(trim((string) $value), 0, 191))
            ->unique()
            ->values()
            ->all();
    }

    private function getResolvedLabels(string $attribute): ?array
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return null;
        }

        $value = $this->attributes[$attribute];

        return is_array($value) ? $value : null;
    }
}
