<?php

namespace Tests\Feature\Research;

use App\Enums\Role as RoleEnum;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Models\User;
use App\Services\Research\ResearchAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class ResearchAccessTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_project_pages_are_visible_only_to_creator_or_project_members(): void
    {
        $creator = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $projectLeader = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $studyStaff = $this->createUserWithRole(RoleEnum::RESEARCH_SUPERVISOR->value);
        $outsider = $this->createUserWithRole(RoleEnum::RESEARCHER->value);

        $project = $this->createProjectWithStudyMembers($creator, $projectLeader, $studyStaff);

        $this->actingAs($creator)
            ->get(route('research.projects.show', $project))
            ->assertOk();

        $this->actingAs($projectLeader)
            ->get(route('research.projects.show', $project))
            ->assertOk();

        $this->actingAs($studyStaff)
            ->get(route('research.projects.show', $project))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('research.projects.show', $project))
            ->assertForbidden();
    }

    public function test_project_updates_are_blocked_for_non_members_with_research_permissions(): void
    {
        $creator = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $projectLeader = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $outsider = $this->createUserWithRole(RoleEnum::RESEARCHER->value);

        $project = $this->createProjectWithStudyMembers($creator, $projectLeader);

        Sanctum::actingAs($outsider);

        $this->putJson(route('api.research.projects.update', $project), [
            'title' => 'Unauthorized change',
            'commodity' => $project->commodity,
            'duration_start' => optional($project->duration_start)->format('Y-m-d'),
            'duration_end' => optional($project->duration_end)->format('Y-m-d'),
            'overall_budget' => $project->overall_budget,
            'objective' => $project->objective,
            'funding_agency' => $project->funding_agency,
            'funding_code' => $project->funding_code,
            'project_leader_id' => (string) $projectLeader->id,
        ])->assertForbidden();
    }

    public function test_project_and_study_people_fields_only_accept_research_users(): void
    {
        $creator = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $nonResearchUser = User::factory()->create();

        Sanctum::actingAs($creator);

        $this->postJson(route('api.research.projects.store'), [
            'title' => 'Role Validation Project',
            'commodity' => 'Rice',
            'project_leader_id' => (string) $nonResearchUser->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['project_leader_id']);

        $project = ResearchProject::factory()->create([
            'created_by' => $creator->id,
            'last_updated_by' => $creator->id,
            'project_leader' => $this->personPayload($creator),
        ]);

        app(ResearchAccessService::class)->syncProjectMembers($project);

        $this->postJson(route('api.research.studies.store'), [
            'project_id' => $project->id,
            'title' => 'Role Validation Study',
            'study_leader_id' => (string) $creator->id,
            'supervisor_id' => (string) $nonResearchUser->id,
            'staff_member_ids' => [(string) $nonResearchUser->id],
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'supervisor_id',
                'staff_member_ids.0',
            ]);
    }

    public function test_sample_inventory_only_lists_samples_from_member_projects(): void
    {
        $member = $this->createUserWithRole(RoleEnum::RESEARCHER->value);
        $outsider = $this->createUserWithRole(RoleEnum::RESEARCHER->value);

        $accessibleProject = $this->createProjectWithStudyMembers($member, $member);
        $hiddenProject = $this->createProjectWithStudyMembers($outsider, $outsider);

        $accessibleSample = $this->createSampleForProject($accessibleProject, 'RI00010001Q', 'Accessible Sample');
        $this->createSampleForProject($hiddenProject, 'RI00010002Q', 'Hidden Sample');

        Sanctum::actingAs($member);

        $this->getJson(route('api.research.samples.inventory.index'))
            ->assertOk()
            ->assertJsonFragment([
                'uid' => $accessibleSample->uid,
                'accession_name' => $accessibleSample->accession_name,
            ])
            ->assertJsonMissing([
                'uid' => 'RI00010002Q',
            ]);
    }

    private function createProjectWithStudyMembers(User $creator, ?User $projectLeader = null, ?User $studyStaff = null): ResearchProject
    {
        $project = ResearchProject::factory()->create([
            'created_by' => $creator->id,
            'last_updated_by' => $creator->id,
            'project_leader' => $projectLeader ? $this->personPayload($projectLeader) : null,
        ]);

        ResearchStudy::factory()->create([
            'project_id' => $project->id,
            'created_by' => $creator->id,
            'last_updated_by' => $creator->id,
            'study_leader' => $projectLeader ? $this->personPayload($projectLeader) : null,
            'supervisor' => $projectLeader ? $this->personPayload($projectLeader) : null,
            'staff_members' => $studyStaff ? [$this->personPayload($studyStaff)] : [],
        ]);

        app(ResearchAccessService::class)->syncProjectMembers($project);

        return $project->fresh();
    }

    private function createSampleForProject(ResearchProject $project, string $uid, string $accessionName): ResearchSample
    {
        $study = ResearchStudy::factory()->create([
            'project_id' => $project->id,
            'created_by' => $project->created_by,
            'last_updated_by' => $project->last_updated_by,
        ]);

        $experiment = ResearchExperiment::factory()->create([
            'study_id' => $study->id,
            'created_by' => $project->created_by,
            'last_updated_by' => $project->last_updated_by,
        ]);

        return ResearchSample::factory()->create([
            'experiment_id' => $experiment->id,
            'uid' => $uid,
            'accession_name' => $accessionName,
            'created_by' => $project->created_by,
            'last_updated_by' => $project->last_updated_by,
        ]);
    }

    private function personPayload(User $user): array
    {
        return app(ResearchAccessService::class)->personPayload((string) $user->id) ?? [
            'id' => (string) $user->id,
            'name' => $user->name,
            'position' => 'Research Member',
            'email' => $user->email,
        ];
    }
}
