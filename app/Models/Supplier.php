<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Supplier extends BaseModel
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'description',
    ];

    protected array $searchable  = [
        'name',
        'email',
        'phone',
        'address',
        'description',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }
}
