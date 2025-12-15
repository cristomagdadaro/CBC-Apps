<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    protected array $searchable = [
        'name',
        'description',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id', 'id');
    }
}
