<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class GoLinkRedirect extends BaseModel
{
    protected $connection = 'corpowp';

    protected $table = 'wp_brm_redirects';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'target_url',
        'clicks',
        'created',
        'expires',
        'status',
        'og_image',
        'og_title',
        'og_description',
        'qr_code',
        'is_public',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'created' => 'datetime',
        'expires' => 'datetime',
        'status' => 'boolean',
        'is_public' => 'boolean',
    ];

    protected $appends = [
        'public_url',
        'is_expired',
    ];

    protected array $searchable = [
        'slug',
        'target_url',
        'og_title',
        'og_description',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }


    public function getSearchable(): array
    {
        return $this->searchable;
    }

    public function getPublicUrlAttribute(): string
    {
        return rtrim((string) config('golink.public_base_url'), '/') . '/go/' . $this->slug;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->expires instanceof CarbonInterface) {
            return false;
        }

        return $this->expires->isPast();
    }
}
