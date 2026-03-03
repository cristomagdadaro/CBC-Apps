<?php

use App\Services\EventWorkflowFeatureService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $toggles = [
            [
                'key' => EventWorkflowFeatureService::KEY_EVENT_WORKFLOW,
                'value' => 'true',
                'label' => 'Event Workflow Enabled',
                'description' => 'Enable or disable event workflow sequence logic.',
                'type' => 'boolean',
                'group' => 'forms',
                'options' => null,
            ],
            [
                'key' => EventWorkflowFeatureService::KEY_PARTICIPANT_WORKFLOW,
                'value' => 'true',
                'label' => 'Participant Workflow Enabled',
                'description' => 'Enable or disable participant-dependent step progression logic.',
                'type' => 'boolean',
                'group' => 'forms',
                'options' => null,
            ],
            [
                'key' => EventWorkflowFeatureService::KEY_PARTICIPANT_VERIFICATION,
                'value' => 'true',
                'label' => 'Participant Verification Enabled',
                'description' => 'Enable or disable participant verification requirements in event forms.',
                'type' => 'boolean',
                'group' => 'forms',
                'options' => null,
            ],
        ];

        foreach ($toggles as $toggle) {
            $existing = DB::table('options')->where('key', $toggle['key'])->first();

            if ($existing) {
                DB::table('options')
                    ->where('id', $existing->id)
                    ->update([
                        'value' => $toggle['value'],
                        'label' => $toggle['label'],
                        'description' => $toggle['description'],
                        'type' => $toggle['type'],
                        'group' => $toggle['group'],
                        'options' => $toggle['options'],
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('options')->insert([
                'id' => (string) Str::uuid(),
                'key' => $toggle['key'],
                'value' => $toggle['value'],
                'label' => $toggle['label'],
                'description' => $toggle['description'],
                'type' => $toggle['type'],
                'group' => $toggle['group'],
                'options' => $toggle['options'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')
            ->whereIn('key', [
                EventWorkflowFeatureService::KEY_EVENT_WORKFLOW,
                EventWorkflowFeatureService::KEY_PARTICIPANT_WORKFLOW,
                EventWorkflowFeatureService::KEY_PARTICIPANT_VERIFICATION,
            ])
            ->delete();
    }
};
