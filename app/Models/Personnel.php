<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Personnel extends BaseModel
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'position',
        'phone',
        'address',
        'email',
        'employee_id',
    ];

    protected array $searchable  = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'position',
        'phone',
        'address',
        'email',
        'employee_id',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }
}
