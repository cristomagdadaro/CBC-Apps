<?php

namespace App\Http\Requests\Concerns;

trait InteractsWithFormStyles
{
    protected function formStyleKeys(): array
    {
        return [
            'form-background',
            'form-background-text-color',
            'form-header-box',
            'form-header-box-text-color',
            'form-time-from',
            'form-time-from-text-color',
            'form-time-to',
            'form-time-to-text-color',
            'form-time-between',
            'form-time-between-text-color',
            'form-text-shadow',
        ];
    }

    protected function styleTokenRules(): array
    {
        $rules = [
            'style_tokens' => ['nullable', 'array'],
        ];

        $colorAndShadowKeys = ['form-background-text-color', 'form-header-box-text-color', 'form-time-from-text-color', 'form-time-to-text-color', 'form-time-between-text-color', 'form-text-shadow'];
        
        foreach ($this->formStyleKeys() as $key) {
            $rules["style_tokens.$key"] = ['nullable', 'array'];
            
            if (in_array($key, $colorAndShadowKeys)) {
                // Text colors and shadows only need a value
                $rules["style_tokens.$key.value"] = ['nullable', 'string', 'max:2048'];
            } else {
                // Background keys need mode and value
                $rules["style_tokens.$key.mode"] = ['nullable', 'in:color,image'];
                $rules["style_tokens.$key.value"] = ['nullable', 'string', 'max:2048'];
            }
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
        $colorAndShadowKeys = ['form-background-text-color', 'form-header-box-text-color', 'form-time-from-text-color', 'form-time-to-text-color', 'form-time-between-text-color', 'form-text-shadow'];

        foreach ($this->formStyleKeys() as $key) {
            $token = $tokens[$key] ?? [];
            $value = $token['value'] ?? null;

            if (in_array($key, $colorAndShadowKeys)) {
                // Text colors and shadows only need a value
                if (!$value || (is_string($value) && trim($value) === '')) {
                    $normalized[$key] = ['value' => null];
                    continue;
                }
                $hasConfiguredValue = true;
                $normalized[$key] = ['value' => trim($value)];
            } else {
                // Background keys need mode and value
                $mode = $token['mode'] ?? null;
                if (!$mode || !$value || (is_string($value) && trim($value) === '')) {
                    $normalized[$key] = ['mode' => null, 'value' => null];
                    continue;
                }
                $hasConfiguredValue = true;
                $normalized[$key] = [
                    'mode' => $mode,
                    'value' => trim($value),
                ];
            }
        }

        return $hasConfiguredValue ? $normalized : null;
    }
}
