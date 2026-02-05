<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill completion_hash for existing ParticipantStepState records
        $states = DB::table('participant_step_states')
            ->whereNull('completion_hash')
            ->get();

        foreach ($states as $state) {
            // Skip records with missing critical fields
            if (!$state->participant_id || !$state->event_subform_id) {
                continue;
            }

            $payload = is_string($state->meta)
                ? json_decode($state->meta, true) ?? []
                : $state->meta ?? [];

            $hash = $this->buildCompletionHash(
                $state->participant_id,
                $state->event_subform_id,
                $payload
            );

            if ($hash) {
                DB::table('participant_step_states')
                    ->where('id', $state->id)
                    ->update(['completion_hash' => $hash]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear backfilled hashes
        DB::table('participant_step_states')
            ->whereNotNull('completion_hash')
            ->update(['completion_hash' => null]);
    }

    /**
     * Build completion hash matching EventWorkflowService logic
     */
    private function buildCompletionHash(?string $participantId, ?string $stepId, array $payload): ?string
    {
        // Return null if critical data is missing
        if (!$participantId || !$stepId) {
            return null;
        }
        return hash('sha256', $participantId . '|' . $stepId . '|' . json_encode($payload));
    }
};
