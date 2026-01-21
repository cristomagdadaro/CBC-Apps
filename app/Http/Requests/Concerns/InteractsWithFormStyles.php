<?php

namespace App\Http\Requests\Concerns;

trait InteractsWithFormStyles
{
    protected function formStyleKeys(): array
    {
        return [
            'form-background',
            'form-header-box',
            'form-time-from',
            'form-time-to',
            'form-time-between',
        ];
    }

    protected function styleTokenRules(): array
    {
        $rules = [
            'style_tokens' => ['nullable', 'array'],
        ];

        foreach ($this->formStyleKeys() as $key) {
            $rules["style_tokens.$key"] = ['nullable', 'array'];
            $rules["style_tokens.$key.mode"] = ['nullable', 'in:color,image'];
            $rules["style_tokens.$key.value"] = ['nullable', 'string', 'max:2048'];
        }

        return $rules;
    }

    protected function normalizeStyleTokens(mixed $tokens): ?array
    {
        if (!is_array($tokens)) {
            return null;
        }

        $normalized = [];
        $hasConfiguredValue = false;

        foreach ($this->formStyleKeys() as $key) {
            $token = $tokens[$key] ?? [];
            $mode = $token['mode'] ?? null;
            $value = $token['value'] ?? null;

            if (!$mode || !$value) {
                $normalized[$key] = ['mode' => null, 'value' => null];
                continue;
            }

            $hasConfiguredValue = true;
            $normalized[$key] = [
                'mode' => $mode,
                'value' => $value,
            ];
        }

        return $hasConfiguredValue ? $normalized : null;
    }
}
