<?php

namespace Tests\Feature\EventAttendance;

use App\Models\Form;
use App\Repositories\FormRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormStyleCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    /** @test */
    public function guest_forms_include_persisted_style_tokens(): void
    {
        $styleTokens = [
            'form-background' => ['mode' => 'color', 'value' => '#f3f4f6'],
            'form-background-text-color' => ['mode' => 'color', 'value' => '#000000'],
            'form-header-box' => ['mode' => 'image', 'value' => '/storage/banners/header.png'],
            'form-header-box-text-color' => ['mode' => 'color', 'value' => '#ffffff'],
            'form-time-from' => ['mode' => 'color', 'value' => '#1f2937'],
            'form-time-from-text-color' => ['mode' => 'color', 'value' => '#ffffff'],
            'form-time-to' => ['mode' => 'color', 'value' => '#1f2937'],
            'form-time-to-text-color' => ['mode' => 'color', 'value' => '#ffffff'],
            'form-time-between' => ['mode' => 'color', 'value' => '#f97316'],
            'form-time-between-text-color' => ['mode' => 'color', 'value' => '#ffffff'],
            'form-text-shadow' => ['mode' => 'color', 'value' => 'rgba(0, 0, 0, 0.25)'],
        ];

        $form = Form::factory()->create([
            'style_tokens' => $styleTokens,
        ]);

        $repo = new FormRepo(new Form());

        $fetched = $repo->getGuestFormByEventId($form->event_id);

        $this->assertNotNull($fetched);
        $this->assertEqualsCanonicalizing($styleTokens, $fetched->style_tokens);
    }
}
