<?php

namespace App\Repositories;

use App\Models\GoLinkRedirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoLinkRepo extends AbstractRepoService
{
    public function __construct(GoLinkRedirect $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $payload = $this->normalizePayload($data);

        return parent::create($payload);
    }

    public function update(int|string $id, array $data): Model
    {
        $payload = $this->normalizePayload($data, (int) $id);

        return parent::update($id, $payload);
    }

    public function findForRedirect(string $slug): ?GoLinkRedirect
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->first();
    }

    public function incrementClicks(GoLinkRedirect $goLink): void
    {
        DB::connection($goLink->getConnectionName())
            ->table($goLink->getTable())
            ->where('id', $goLink->id)
            ->update([
                'clicks' => DB::raw('clicks + 1'),
            ]);

        $goLink->clicks = (int) $goLink->clicks + 1;
    }

    public function generatePublicUrl(string $slug): string
    {
        return rtrim((string) config('golink.public_base_url'), '/') . '/go/' . $slug;
    }

    private function normalizePayload(array $data, ?int $id = null): array
    {
        $slug = $this->normalizeSlug($data['slug'] ?? null);

        if ($slug === null) {
            $slug = $this->generateUniqueSlug($id);
        }

        $expires = $data['expires'] ?? null;
        $publicUrl = $this->generatePublicUrl($slug);

        return [
            'slug' => $slug,
            'target_url' => trim((string) ($data['target_url'] ?? '')),
            'expires' => filled($expires) ? Carbon::parse($expires) : null,
            'status' => isset($data['status']) ? (int) ((bool) $data['status']) : 1,
            'og_title' => $this->nullableTrimmed($data['og_title'] ?? null),
            'og_description' => $this->nullableTrimmed($data['og_description'] ?? null),
            'og_image' => $this->nullableTrimmed($data['og_image'] ?? null),
            'qr_code' => 'https://quickchart.io/chart?cht=qr&chs=500x500&chl=' . urlencode($publicUrl) . '&choe=UTF-8',
            'is_public' => isset($data['is_public']) ? (int) ((bool) $data['is_public']) : 0,
        ];
    }

    private function nullableTrimmed(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeSlug(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $slug = preg_replace('/[^A-Za-z0-9_-]/', '', trim($value));

        return $slug === '' ? null : $slug;
    }

    private function generateUniqueSlug(?int $ignoreId = null, int $length = 9): string
    {
        do {
            $slug = Str::random($length);

            $exists = $this->model->newQuery()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists();
        } while ($exists);

        return $slug;
    }
}
