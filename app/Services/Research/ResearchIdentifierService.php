<?php

namespace App\Services\Research;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;

class ResearchIdentifierService
{
    protected const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function nextProjectCode(): string
    {
        return $this->nextDatedCode(ResearchProject::class, 'PRJ');
    }

    public function nextStudyCode(): string
    {
        return $this->nextDatedCode(ResearchStudy::class, 'STU');
    }

    public function nextExperimentCode(): string
    {
        return $this->nextDatedCode(ResearchExperiment::class, 'EXP');
    }

    public function nextSampleIdentity(ResearchExperiment $experiment, ?string $commodity = null): array
    {
        $sequence = (int) ResearchSample::withTrashed()
            ->where('experiment_id', $experiment->id)
            ->max('sequence_number');

        $sequence++;

        $prefix = $this->commodityPrefix($commodity ?: $experiment->commodity ?: 'XX');
        $raw = $prefix
            . $this->padBase36((int) $experiment->id, 4)
            . $this->padBase36($sequence, 4);

        return [
            'uid' => $raw . $this->checksum($raw),
            'sequence_number' => $sequence,
        ];
    }

    protected function nextDatedCode(string $modelClass, string $prefix): string
    {
        $query = method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $year = now()->year;
        $sequence = (int) $query->whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    protected function commodityPrefix(string $commodity): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/', '', $commodity));
        $clean = substr($clean, 0, 2);

        return str_pad($clean ?: 'XX', 2, 'X');
    }

    protected function checksum(string $value): string
    {
        $total = 0;
        foreach (str_split($value) as $character) {
            $total += ord($character);
        }

        return self::ALPHABET[$total % strlen(self::ALPHABET)];
    }

    protected function padBase36(int $value, int $length): string
    {
        return str_pad(strtoupper(base_convert(max($value, 0), 10, 36)), $length, '0', STR_PAD_LEFT);
    }
}
