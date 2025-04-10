<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'position',
        'phone',
        'address',
        'email',
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
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }
}
