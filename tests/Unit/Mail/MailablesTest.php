<?php

namespace Tests\Unit\Mail;

use App\Enums\Inventory as InventoryEnum;
use App\Enums\Subform;
use App\Mail\EventSubformResponseNotification;
use App\Mail\GeneratedCertificateMail;
use App\Mail\LaboratoryEquipmentLogOverdueMail;
use App\Mail\OutgoingTransactionNotification;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Category;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Item;
use App\Models\Participant;
use App\Models\Personnel;
use App\Models\Registration;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_certificate_mail_renders_recipient_and_event_details(): void
    {
        $form = Form::factory()->create([
            'event_id' => 'EVT1',
            'title' => 'Advanced Rice Workshop',
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]);

        $attachmentPath = tempnam(sys_get_temp_dir(), 'cert_') . '.pdf';
        file_put_contents($attachmentPath, '%PDF-fake');

        $html = (new GeneratedCertificateMail($attachmentPath, 'certificate.pdf', $form->event_id))
            ->withRecipientName('Jane Doe')
            ->render();

        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('Advanced Rice Workshop', $html);
        $this->assertStringContainsString('EVT1', $html);

        @unlink($attachmentPath);
    }

    public function test_outgoing_transaction_notification_renders_item_and_remaining_quantity(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'name' => 'Buffer Solution',
            'brand' => 'CBC Lab',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-MAIL']);
        $user = User::factory()->create();

        Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-02-200001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 10,
            'unit_price' => 1,
            'unit' => 'pc',
            'total_cost' => 10,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Incoming stock',
        ]);

        $outgoing = Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-02-200001',
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => -4,
            'unit_price' => 1,
            'unit' => 'pc',
            'total_cost' => -4,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Issued to laboratory',
        ]);

        $html = (new OutgoingTransactionNotification($outgoing))->render();

        $this->assertStringContainsString('Buffer Solution', $html);
        $this->assertStringContainsString('6 pc', $html);
        $this->assertStringContainsString('Issued to laboratory', $html);
    }

    public function test_laboratory_equipment_log_overdue_mail_renders_equipment_link_and_ignore_notice(): void
    {
        $category = Category::factory()->create([
            'name' => 'Laboratory Equipment',
        ]);
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'name' => 'PCR Machine',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'fname' => 'Maria',
            'lname' => 'Santos',
            'email' => 'maria.santos@example.test',
        ]);

        $log = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'overdue',
            'started_at' => now()->subHours(3),
            'end_use_at' => now()->subHour(),
            'active_lock' => false,
        ]);

        $html = (new LaboratoryEquipmentLogOverdueMail(
            $log->load(['personnel', 'equipment']),
            'laboratory',
        ))->render();

        $this->assertStringContainsString('PCR Machine', $html);
        $this->assertStringContainsString(route('laboratory.equipments.show', ['equipment_id' => $item->id]), $html);
        $this->assertStringContainsString('you may ignore this notice', $html);
    }

    public function test_event_subform_response_notification_renders_response_details(): void
    {
        DB::table('loc_cities')->insert([
            'id' => 1,
            'city' => 'Science City of Muñoz',
            'province' => 'Nueva Ecija',
            'region' => 'REGION III',
            'latitude' => 15.7161,
            'longitude' => 120.9031,
        ]);

        $form = Form::factory()->create([
            'event_id' => 'EVT2',
            'title' => 'Seed Systems Seminar',
        ]);

        $subform = EventSubform::factory()->create([
            'event_id' => $form->event_id,
        ]);

        $participant = Participant::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
        ]);

        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        $response = EventSubformResponse::query()->create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => Subform::REGISTRATION->value,
            'response_data' => [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@example.com',
            ],
            'submitted_at' => now(),
        ]);

        $response->setRelation('formParent', tap($subform, fn ($model) => $model->setRelation('form', $form)));

        $html = (new EventSubformResponseNotification($response))->render();

        $this->assertStringContainsString('Seed Systems Seminar', $html);
        $this->assertStringContainsString('Juan Dela Cruz', $html);
        $this->assertStringContainsString(Subform::REGISTRATION->value, $html);
    }
}
