<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RequestFormPivot extends BaseModel
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

    protected $table = 'request_forms_pivot';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'requester_id',
        'form_id',
        'agreed_clause_1',
        'agreed_clause_2',
        'agreed_clause_3',
    ];

    protected array $searchable = [
        'id',
        'request_status',
        'requester.name',
        'requester.affiliation',
        'requester.position',
        'request_form.project_title',
        'request_form.request_purpose',
    ];

    protected $casts = [
        'id' => 'string',
        'agreed_clause_1' => 'boolean',
        'agreed_clause_2' => 'boolean',
        'agreed_clause_3' => 'boolean',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class, 'requester_id', 'id');
    }

    public function request_form(): BelongsTo
    {
        return $this->belongsTo(UseRequestForm::class, 'form_id', 'id');
    }
}
