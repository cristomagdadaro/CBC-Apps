<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Repositories\FormRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormStyleCustomizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_forms_include_persisted_style_tokens(): void
    {
        $styleTokens = [
            'form-background' => ['mode' => 'color', 'value' => '#f3f4f6'],
            'form-header-box' => ['mode' => 'image', 'value' => '/storage/banners/header.png'],
            'form-time-from' => ['mode' => 'color', 'value' => '#1f2937'],
            'form-time-to' => ['mode' => 'color', 'value' => '#1f2937'],
            'form-time-between' => ['mode' => 'color', 'value' => '#f97316'],
        ];

        $form = Form::factory()->create([
            'style_tokens' => $styleTokens,
        ]);

        $repo = new FormRepo(new Form());

        $fetched = $repo->getGuestFormByEventId($form->event_id);

        $this->assertNotNull($fetched);
        $this->assertSame($styleTokens, $fetched->style_tokens);
    }
}
