<?php

namespace App\Http\Requests\Research\Concerns;

use App\Services\Research\ResearchAccessService;
use Illuminate\Validation\Validator;

trait ValidatesResearchMemberSelections
{
    protected function validateResearchUserSelection(Validator $validator, string $field, string $message): void
    {
        $userId = $this->input($field);

        if (! $userId) {
            return;
        }

        if (! $this->researchAccessService()->isAssignableUser((string) $userId)) {
            $validator->errors()->add($field, $message);
        }
    }

    protected function validateResearchUserSelections(Validator $validator, string $field, string $message): void
    {
        $userIds = $this->input($field, []);

        if (! is_array($userIds)) {
            return;
        }

        foreach ($userIds as $index => $userId) {
            if (! $userId) {
                continue;
            }

            if (! $this->researchAccessService()->isAssignableUser((string) $userId)) {
                $validator->errors()->add("{$field}.{$index}", $message);
            }
        }
    }

    protected function researchAccessService(): ResearchAccessService
    {
        return app(ResearchAccessService::class);
    }
}
