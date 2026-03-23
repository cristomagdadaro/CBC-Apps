<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that form update page loads with requirements
     */
    public function test_form_update_loads_with_requirements()
    {
        // Create a form with requirements
        $this->seed(\Database\Seeders\FormSeeder::class);

        $form = Form::latest()->first();

        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user, 'sanctum');

        // Access the form update page
        $response = $this->get(route('forms.update', ['event_id' => $form->event_id]));

        $response->assertStatus(200);
        
        // With Inertia, we need to check the props directly
        $props = $response->viewData('page')['props'];
        
        $this->assertArrayHasKey('data', $props);
        $data = $props['data'];
        
        // Verify data structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('requirements', $data);
        
        $requirements = $data['requirements'];
        $this->assertEquals(3, count($requirements));

        // Verify requirements are ordered by step_order
        $this->assertEquals('preregistration', $requirements[0]['form_type']);
        $this->assertEquals(1, $requirements[0]['step_order']);
        $this->assertEquals('registration', $requirements[1]['form_type']);
        $this->assertEquals(2, $requirements[1]['step_order']);
        $this->assertEquals('feedback', $requirements[2]['form_type']);
        $this->assertEquals(3, $requirements[2]['step_order']);
    }
}
