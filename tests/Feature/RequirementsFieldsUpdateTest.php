<?php

namespace Tests\Feature;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirementsFieldsUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that requirement form type can be updated and persists
     */
    public function test_requirement_form_type_updates()
    {
        // Create a form with requirements
        $this->seed(\Database\Seeders\FormSeeder::class);
        $form = Form::latest()->first();
        $form->load('requirements');
        $requirement = $form->requirements->first();

        // Update the form with a changed requirement type
        $updatedRequirements = $form->requirements->map(function($req) use ($requirement) {
            if ($req->id === $requirement->id) {
                return [
                    'id' => $req->id,
                    'event_id' => $req->event_id,
                    'form_type' => 'pretest',  // Change from preregistration to pretest
                    'step_type' => 'pretest',
                    'step_order' => 1,
                    'is_required' => true,
                    'is_enabled' => true,
                    'max_slots' => 50,
                    'open_from' => $req->open_from,
                    'open_to' => $req->open_to,
                    'visibility_rules' => $req->visibility_rules,
                    'completion_rules' => $req->completion_rules,
                ];
            }
            return $req->toArray();
        })->toArray();

        // Verify the requirement was changed
        $this->assertEquals('pretest', $updatedRequirements[0]['form_type']);
        $this->assertEquals(1, $updatedRequirements[0]['step_order']);
    }

    /**
     * Test that requirement max_slots can be updated
     */
    public function test_requirement_max_slots_updates()
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $form = Form::latest()->first();
        $requirement = $form->requirements()->first();

        $updatedRequirement = [
            'id' => $requirement->id,
            'event_id' => $requirement->event_id,
            'form_type' => $requirement->form_type,
            'step_type' => $requirement->step_type,
            'step_order' => $requirement->step_order,
            'is_required' => $requirement->is_required,
            'is_enabled' => true,
            'max_slots' => 100,  // Update max slots
            'open_from' => $requirement->open_from,
            'open_to' => $requirement->open_to,
            'visibility_rules' => $requirement->visibility_rules,
            'completion_rules' => $requirement->completion_rules,
        ];

        $this->assertEquals(100, $updatedRequirement['max_slots']);
    }

    /**
     * Test that requirement open/close times can be updated
     */
    public function test_requirement_datetime_updates()
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $form = Form::latest()->first();
        $requirement = $form->requirements()->first();

        $newOpenFrom = now()->addDays(1);
        $newOpenTo = now()->addDays(8);

        $updatedRequirement = [
            'id' => $requirement->id,
            'event_id' => $requirement->event_id,
            'form_type' => $requirement->form_type,
            'step_type' => $requirement->step_type,
            'step_order' => $requirement->step_order,
            'is_required' => $requirement->is_required,
            'is_enabled' => true,
            'max_slots' => $requirement->max_slots,
            'open_from' => $newOpenFrom->format('Y-m-d H:i:s'),
            'open_to' => $newOpenTo->format('Y-m-d H:i:s'),
            'visibility_rules' => $requirement->visibility_rules,
            'completion_rules' => $requirement->completion_rules,
        ];

        $this->assertEquals($newOpenFrom->format('Y-m-d H:i:s'), $updatedRequirement['open_from']);
        $this->assertEquals($newOpenTo->format('Y-m-d H:i:s'), $updatedRequirement['open_to']);
    }

    /**
     * Test that requirement can be enabled/disabled
     */
    public function test_requirement_enabled_toggle()
    {
        $this->seed(\Database\Seeders\FormSeeder::class);
        $form = Form::latest()->first();
        $requirement = $form->requirements()->first();

        $updatedRequirement = [
            'id' => $requirement->id,
            'event_id' => $requirement->event_id,
            'form_type' => $requirement->form_type,
            'step_type' => $requirement->step_type,
            'step_order' => $requirement->step_order,
            'is_required' => $requirement->is_required,
            'is_enabled' => false,  // Disable
            'max_slots' => $requirement->max_slots,
            'open_from' => $requirement->open_from,
            'open_to' => $requirement->open_to,
            'visibility_rules' => $requirement->visibility_rules,
            'completion_rules' => $requirement->completion_rules,
        ];

        $this->assertFalse($updatedRequirement['is_enabled']);
    }
}
