<?php

namespace Tests\Feature;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormUpdateRequirementsSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that updating requirements via form submission persists changes to max_slots
     */
    public function test_updating_requirement_max_slots_persists()
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $form = Form::latest()->first();
        $form->update(['is_suspended' => false]);
        $requirement = $form->requirements()->first();

        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user, 'sanctum');

        $newMaxSlots = 999;

        $updatedRequirements = $form->requirements->map(function($req) use ($requirement, $newMaxSlots) {
            $data = [
                'event_id' => $req->event_id,
                'form_type' => $req->form_type,
                'step_type' => $req->step_type,
                'step_order' => $req->step_order,
                'is_enabled' => $req->is_enabled,
                'open_from' => $req->open_from?->format('Y-m-d'),
                'open_to' => $req->open_to?->format('Y-m-d'),
                'is_required' => $req->is_required,
                'max_slots' => $req->max_slots,
                'config' => [],
                'visibility_rules' => $req->visibility_rules,
                'completion_rules' => $req->completion_rules,
            ];
            
            if ($req->id === $requirement->id) {
                $data['max_slots'] = $newMaxSlots;
            }
            
            return $data;
        })->toArray();

        $response = $this->putJson(route('api.form.put', $form->event_id), [
            'event_id' => $form->event_id,
            'title' => $form->title,
            'description' => $form->description,
            'details' => $form->details,
            'date_from' => $form->date_from?->format('Y-m-d'),
            'date_to' => $form->date_to?->format('Y-m-d'),
            'time_from' => $form->time_from,
            'time_to' => $form->time_to,
            'venue' => $form->venue,
            'is_suspended' => $form->is_suspended,
            'requirements' => $updatedRequirements,
            'style_tokens' => $form->style_tokens,
        ]);

        $response->assertStatus(200);

        $updatedReq = EventSubform::find($requirement->id);
        $this->assertEquals($newMaxSlots, $updatedReq->max_slots);
    }
}
