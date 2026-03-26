<?php

namespace App\Services\Research;

use App\Enums\Role;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ResearchAccessService
{
    public function allowedRoleNames(): array
    {
        return [
            Role::RESEARCHER->value,
            Role::RESEARCH_SUPERVISOR->value,
        ];
    }

    public function assignableUsersQuery(): Builder
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->with([
                'roles' => fn ($query) => $query
                    ->select(['roles.id', 'roles.name', 'roles.label'])
                    ->whereIn('name', $this->allowedRoleNames()),
            ])
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $this->allowedRoleNames()))
            ->orderBy('name');
    }

    public function memberOptions(): array
    {
        return $this->assignableUsersQuery()
            ->get()
            ->map(fn (User $user) => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'position' => $this->primaryResearchRoleLabel($user),
            ])
            ->values()
            ->all();
    }

    public function isAssignableUser(?string $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->assignableUsersQuery()
            ->whereKey($userId)
            ->exists();
    }

    public function personPayload(?string $userId): ?array
    {
        if (! $userId) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->assignableUsersQuery()
            ->whereKey($userId)
            ->first();

        if (! $user) {
            return null;
        }

        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'position' => $this->primaryResearchRoleLabel($user),
        ];
    }

    public function peoplePayload(array $userIds): array
    {
        return collect($userIds)
            ->filter()
            ->unique()
            ->map(fn ($userId) => $this->personPayload((string) $userId))
            ->filter()
            ->values()
            ->all();
    }

    public function syncProjectMembers(ResearchProject $project): void
    {
        $project->loadMissing([
            'studies:id,project_id,study_leader,staff_members,supervisor,created_by',
        ]);

        $memberIds = collect([$project->created_by])
            ->merge($this->extractMemberIds($project->project_leader))
            ->merge(
                $project->studies->flatMap(function (ResearchStudy $study) {
                    return collect([$study->created_by])
                        ->merge($this->extractMemberIds($study->study_leader))
                        ->merge($this->extractMemberIds($study->supervisor))
                        ->merge($this->extractMemberIds($study->staff_members));
                })
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        $project->members()->sync($memberIds);
    }

    public function visibleProjectsQuery(?User $user): Builder
    {
        $query = ResearchProject::query();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->isAdministrator($user)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($user) {
            $subQuery->where('created_by', $user->id)
                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));
        });
    }

    public function visibleProjectIdsQuery(?User $user): Builder
    {
        return $this->visibleProjectsQuery($user)->select('research_projects.id');
    }

    public function canAccessProject(?User $user, ResearchProject $project): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        if ((string) $project->created_by === (string) $user->id) {
            return true;
        }

        return $project->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function canAccessStudy(?User $user, ResearchStudy $study): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $study->loadMissing('project.members');

        return $this->canAccessProject($user, $study->project);
    }

    public function canAccessExperiment(?User $user, ResearchExperiment $experiment): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $experiment->loadMissing('study.project.members');

        return $this->canAccessProject($user, $experiment->study->project);
    }

    public function canAccessSample(?User $user, ResearchSample $sample): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $sample->loadMissing('experiment.study.project.members');

        return $this->canAccessProject($user, $sample->experiment->study->project);
    }

    public function canAccessRecord(?User $user, ResearchMonitoringRecord $record): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $record->loadMissing('sample.experiment.study.project.members');

        return $this->canAccessProject($user, $record->sample->experiment->study->project);
    }

    protected function extractMemberIds(array|null $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        if (array_key_exists('id', $value)) {
            return array_filter([(string) ($value['id'] ?? null)]);
        }

        return collect($value)
            ->filter(fn ($member) => is_array($member) && ! empty($member['id']))
            ->map(fn ($member) => (string) $member['id'])
            ->unique()
            ->values()
            ->all();
    }

    protected function primaryResearchRoleLabel(User $user): string
    {
        $roles = $user->relationLoaded('roles')
            ? $user->roles
            : $user->roles()
                ->whereIn('name', $this->allowedRoleNames())
                ->get(['roles.id', 'roles.name', 'roles.label']);

        foreach ([Role::RESEARCH_SUPERVISOR->value, Role::RESEARCHER->value] as $roleName) {
            $role = $roles->firstWhere('name', $roleName);

            if ($role) {
                return $role->label ?: str($roleName)->replace('_', ' ')->title()->toString();
            }
        }

        return 'Research Member';
    }

    protected function isAdministrator(?User $user): bool
    {
        return (bool) $user && ($user->is_admin || $user->hasRole(Role::ADMIN->value));
    }
}
