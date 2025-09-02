<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestFormPivot extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'request_forms_pivot';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'requester_id',
        'form_id',
        'request_status',
        'agreed_clause_1',
        'agreed_clause_2',
        'agreed_clause_3',
        'disapproved_remarks',
        'approval_constraint',
        'approved_by',
    ];

    protected array $searchable = [
        'id',
        'requester_id',
        'form_id',
        'request_status',
        'agreed_clause_1',
        'agreed_clause_2',
        'agreed_clause_3',
        'disapproved_remarks',
        'approved_by',
    ];

    protected $casts = [
        'id' => 'string',
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
