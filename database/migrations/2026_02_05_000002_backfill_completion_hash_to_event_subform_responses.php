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
        // Backfill completion_hash for existing EventSubformResponse records
        $responses = DB::table('event_subform_responses')
            ->whereNull('completion_hash')
            ->get();

        foreach ($responses as $response) {
            // Skip records with missing critical fields (participant_id or form_parent_id)
            if (!$response->participant_id || !$response->form_parent_id) {
                continue;
            }

            $hash = $this->buildCompletionHash(
                $response->participant_id,
                $response->form_parent_id,
                is_string($response->response_data) 
                    ? json_decode($response->response_data, true) ?? []
                    : $response->response_data ?? []
            );

            DB::table('event_subform_responses')
                ->where('id', $response->id)
                ->update(['completion_hash' => $hash]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear backfilled hashes
        DB::table('event_subform_responses')
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
