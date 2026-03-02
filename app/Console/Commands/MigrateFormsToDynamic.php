<?php

namespace App\Console\Commands;

use App\Models\FormTypeTemplate;
use App\Models\FormFieldDefinition;
use App\Models\EventSubform;
use App\Enums\Subform;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateFormsToDynamic extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'forms:migrate-to-dynamic 
                            {--dry-run : Show what would be created without actually creating}
                            {--force : Overwrite existing templates}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing config/subformtypes.php entries to database-driven templates';

    /**
     * Field metadata for generating better labels and configurations
     */
    protected array $fieldMetadata = [
        'name' => ['label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Enter your full name'],
        'email' => ['label' => 'Email Address', 'type' => 'email', 'placeholder' => 'your.email@example.com'],
        'phone' => ['label' => 'Phone Number', 'type' => 'phone', 'placeholder' => 'e.g., 09123456789'],
        'sex' => ['label' => 'Sex', 'type' => 'radio', 'options' => ['Male', 'Female', 'Prefer not to say']],
        'age' => ['label' => 'Age', 'type' => 'number', 'placeholder' => 'Your age'],
        'organization' => ['label' => 'Organization/Institution', 'type' => 'text', 'placeholder' => 'Your organization or school'],
        'designation' => ['label' => 'Designation/Position', 'type' => 'text', 'placeholder' => 'Your position or role'],
        'is_ip' => ['label' => 'Are you an Indigenous Person?', 'type' => 'checkbox'],
        'is_pwd' => ['label' => 'Are you a Person with Disability?', 'type' => 'checkbox'],
        'region_address' => ['label' => 'Region', 'type' => 'location_region'],
        'province_address' => ['label' => 'Province', 'type' => 'location_province'],
        'city_address' => ['label' => 'City', 'type' => 'location_city'],
        'country_address' => ['label' => 'Country', 'type' => 'text', 'placeholder' => 'Philippines'],
        'attendance_type' => ['label' => 'Attendance Type', 'type' => 'radio', 'options' => ['Online', 'In-person']],
        'agreed_tc' => ['label' => 'I hereby certify that the information provided is true, correct, and complete. I authorize the Department of Agriculture – Crop Biotechnology Center (DA-CBC) to collect, process, store, update, and manage my personal data in accordance with Republic Act No. 10173 (Data Privacy Act of 2012) for legitimate purposes related to its programs and web applications.', 'type' => 'checkbox_agreement'],
        'agreed_updates' => ['label' => 'I consent to receive official updates, announcements, and program-related communications from the DA–Crop Biotechnology Center through my registered email address, mobile number, and/or messaging applications.', 'type' => 'checkbox_updates'],
        'join_quiz_bee' => ['label' => 'Do you want to join the Biotech Quiz Bee tomorrow (March 6, 2026)?', 'type' => 'checkbox'],
        'team_name' => ['label' => 'Team Name', 'type' => 'text', 'placeholder' => 'Enter your team name'],
        'coach_name' => ['label' => 'Coach Name', 'type' => 'text', 'placeholder' => 'Coach full name'],
        'coach_email' => ['label' => 'Coach Email', 'type' => 'email', 'placeholder' => 'coach.email@example.com'],
        'coach_phone' => ['label' => 'Coach Phone', 'type' => 'phone', 'placeholder' => 'Coach phone number'],
        'proof_of_enrollment' => ['label' => 'Proof of Enrollment (PDF)', 'type' => 'file'],
        'clarity_objective' => ['label' => 'Clarity of Objectives', 'type' => 'likert_scale', 'description' => 'Rate from 1 (Poor) to 5 (Excellent)'],
        'time_allotment' => ['label' => 'Time Allotment', 'type' => 'likert_scale'],
        'attainment_objective' => ['label' => 'Attainment of Objectives', 'type' => 'likert_scale'],
        'relevance_usefulness' => ['label' => 'Relevance & Usefulness', 'type' => 'likert_scale'],
        'overall_quality_content' => ['label' => 'Overall Quality of Content', 'type' => 'likert_scale'],
        'overall_quality_resource_persons' => ['label' => 'Overall Quality of Resource Persons', 'type' => 'likert_scale'],
        'time_management_organization' => ['label' => 'Time Management & Organization', 'type' => 'likert_scale'],
        'support_staff' => ['label' => 'Support Staff', 'type' => 'likert_scale'],
        'overall_quality_activity_admin' => ['label' => 'Overall Quality of Activity Administration', 'type' => 'likert_scale'],
        'knowledge_gain' => ['label' => 'Knowledge Gain', 'type' => 'likert_scale'],
        'comments_event_coordination' => ['label' => 'Comments on Event Coordination', 'type' => 'textarea', 'placeholder' => 'Your feedback...'],
        'other_topics' => ['label' => 'Other Topics You Want to Learn', 'type' => 'textarea', 'placeholder' => 'Suggest topics...'],
        'score' => ['label' => 'Score', 'type' => 'number'],
        'remarks' => ['label' => 'Remarks', 'type' => 'textarea'],
    ];

    /**
     * Template names for each subform type
     */
    protected array $templateNames = [
        'preregistration' => ['name' => 'Pre-registration Form', 'icon' => 'clipboard-document-check', 'description' => 'Standard pre-registration form for event sign-ups'],
        'registration' => ['name' => 'Registration Form', 'icon' => 'identification', 'description' => 'Full registration form for confirmed participants'],
        'preregistration_biotech' => ['name' => 'Pre-registration (Biotech)', 'icon' => 'beaker', 'description' => 'Pre-registration form with Quiz Bee option'],
        'preregistration_quizbee' => ['name' => 'Quiz Bee Registration', 'icon' => 'trophy', 'description' => 'Team registration form for Quiz Bee competitions'],
        'feedback' => ['name' => 'Feedback Form', 'icon' => 'chat-bubble-bottom-center-text', 'description' => 'Post-event feedback and evaluation form'],
        'posttest' => ['name' => 'Post-Test Form', 'icon' => 'document-check', 'description' => 'Post-activity assessment form'],
        'pretest' => ['name' => 'Pre-Test Form', 'icon' => 'document-text', 'description' => 'Pre-activity assessment form'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $subformtypes = config('subformtypes');

        if (!$subformtypes) {
            $this->error('Could not load config/subformtypes.php');
            return 1;
        }

        $this->info('Found ' . count($subformtypes) . ' form types to migrate');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($subformtypes as $slug => $rules) {
            $existingTemplate = FormTypeTemplate::where('slug', $slug)->first();

            if ($this->option('dry-run')) {
                $this->info(($existingTemplate ? "Would update template" : "Would create template") . ": {$slug}");
                $this->displayFieldsTable($slug, $rules);
                continue;
            }

            if ($existingTemplate && !$this->option('force')) {
                $this->warn("⏭️  Skipping '{$slug}' schema sync - template exists (use --force to refresh fields)");
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($slug, $rules, $existingTemplate) {
                    $templateMeta = $this->templateNames[$slug] ?? [
                        'name' => ucwords(str_replace('_', ' ', $slug)),
                        'icon' => 'document',
                        'description' => null,
                    ];

                    $template = $existingTemplate;
                    if (!$template) {
                        $template = FormTypeTemplate::create([
                            'slug' => $slug,
                            'name' => $templateMeta['name'],
                            'description' => $templateMeta['description'],
                            'icon' => $templateMeta['icon'],
                            'is_system' => true,
                            'created_by' => null,
                        ]);
                    } else {
                        $template->update([
                            'name' => $templateMeta['name'],
                            'description' => $templateMeta['description'],
                            'icon' => $templateMeta['icon'],
                        ]);
                    }

                    $template->fieldDefinitions()->delete();

                    $sortOrder = 0;
                    foreach ($rules as $fieldKey => $validationRule) {
                        $fieldMeta = $this->fieldMetadata[$fieldKey] ?? [];
                        $inferredType = $this->inferFieldType($fieldKey, $validationRule);

                        FormFieldDefinition::create([
                            'form_type_template_id' => $template->id,
                            'field_key' => $fieldKey,
                            'field_type' => $fieldMeta['type'] ?? $inferredType,
                            'label' => $fieldMeta['label'] ?? $this->generateLabel($fieldKey),
                            'placeholder' => $fieldMeta['placeholder'] ?? null,
                            'description' => $fieldMeta['description'] ?? null,
                            'validation_rules' => $this->parseValidationRules($validationRule),
                            'options' => isset($fieldMeta['options']) ? array_map(fn($o) => ['value' => $o, 'label' => $o], $fieldMeta['options']) : $this->extractOptionsFromRule($validationRule),
                            'display_config' => [],
                            'field_config' => [],
                            'sort_order' => $sortOrder++,
                            'is_system' => true,
                        ]);
                    }
                });

                if ($existingTemplate) {
                    $this->info("🔄 Updated template: {$slug}");
                    $updated++;
                } else {
                    $this->info("✅ Created template: {$slug}");
                    $created++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to create {$slug}: " . $e->getMessage());
            }
        }

        $linked = $this->linkEventSubformsToTemplates((bool) $this->option('dry-run'), (bool) $this->option('force'));

        $this->newLine();
        if (!$this->option('dry-run')) {
            $this->info("Migration complete: {$created} created, {$updated} updated, {$skipped} skipped, {$linked} event steps linked");
        } else {
            $this->info("Dry run complete: would link {$linked} event steps to templates");
        }

        return 0;
    }

    protected function linkEventSubformsToTemplates(bool $dryRun = false, bool $forceRebase = false): int
    {
        $templatesBySlug = FormTypeTemplate::query()
            ->pluck('id', 'slug');

        $linked = 0;

        foreach ($templatesBySlug as $slug => $templateId) {
            $subforms = EventSubform::query()
                ->where('form_type', $slug)
                ->get();

            foreach ($subforms as $subform) {
                $hasCustomSchema = !empty($subform->field_schema);
                if ($hasCustomSchema && !$forceRebase) {
                    continue;
                }

                if ($subform->form_type_template_id === $templateId && !($forceRebase && $hasCustomSchema)) {
                    continue;
                }

                $linked++;

                if (!$dryRun) {
                    $payload = [
                        'form_type_template_id' => $templateId,
                    ];

                    if ($forceRebase) {
                        $payload['field_schema'] = null;
                    }

                    $subform->update($payload);
                }
            }
        }

        return $linked;
    }

    /**
     * Display fields table for dry run
     */
    protected function displayFieldsTable(string $slug, array $rules): void
    {
        $rows = [];
        foreach ($rules as $fieldKey => $rule) {
            $fieldMeta = $this->fieldMetadata[$fieldKey] ?? [];
            $rows[] = [
                $fieldKey,
                $fieldMeta['label'] ?? $this->generateLabel($fieldKey),
                $fieldMeta['type'] ?? $this->inferFieldType($fieldKey, $rule),
                is_array($rule) ? implode('|', $rule) : $rule,
            ];
        }

        $this->table(['Field Key', 'Label', 'Type', 'Validation'], $rows);
        $this->newLine();
    }

    /**
     * Generate a human-readable label from field key
     */
    protected function generateLabel(string $fieldKey): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $fieldKey));
    }

    /**
     * Infer field type from field key and validation rules
     */
    protected function inferFieldType(string $fieldKey, string $validationRule): string
    {
        // Check for known patterns
        if (str_contains($validationRule, 'email')) {
            return 'email';
        }
        if (str_contains($validationRule, 'file') || str_contains($validationRule, 'mimes:')) {
            return 'file';
        }
        if (str_contains($validationRule, 'integer') || str_contains($validationRule, 'numeric')) {
            if (str_contains($validationRule, 'in:1,2,3,4,5')) {
                return 'likert_scale';
            }
            return 'number';
        }
        if (str_contains($validationRule, 'boolean') || str_contains($validationRule, 'accepted')) {
            if (str_contains($fieldKey, 'agreed')) {
                return 'checkbox_agreement';
            }
            if (str_contains($fieldKey, 'updates')) {
                return 'checkbox_updates';
            }
            return 'checkbox';
        }
        if (str_contains($validationRule, 'in:')) {
            // Extract options count
            preg_match('/in:([^|]+)/', $validationRule, $matches);
            $optionCount = count(explode(',', $matches[1] ?? ''));
            return $optionCount <= 5 ? 'radio' : 'select';
        }
        if (str_contains($fieldKey, 'address') && str_contains($validationRule, 'exists:loc_cities')) {
            if (str_contains($fieldKey, 'city')) return 'location_city';
            if (str_contains($fieldKey, 'province')) return 'location_province';
            if (str_contains($fieldKey, 'region')) return 'location_region';
        }
        if (str_contains($fieldKey, 'phone')) {
            return 'phone';
        }
        if (str_contains($fieldKey, 'comment') || str_contains($fieldKey, 'remark') || str_contains($fieldKey, 'topic')) {
            return 'textarea';
        }

        return 'text';
    }

    /**
     * Parse validation rule string into structured array
     */
    protected function parseValidationRules(string $validationRule): array
    {
        $parsedRules = [];
        $parts = explode('|', $validationRule);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            if (str_contains($part, ':')) {
                [$ruleName, $ruleValue] = explode(':', $part, 2);
                $parsedRules[$ruleName] = $ruleValue;
            } else {
                $parsedRules[$part] = true;
            }
        }

        return $parsedRules;
    }

    /**
     * Extract options from 'in:' validation rule
     */
    protected function extractOptionsFromRule(string $validationRule): array
    {
        if (!str_contains($validationRule, 'in:')) {
            return [];
        }

        preg_match('/in:([^|]+)/', $validationRule, $matches);
        if (empty($matches[1])) {
            return [];
        }

        $options = explode(',', $matches[1]);
        return array_map(fn($opt) => ['value' => trim($opt), 'label' => trim($opt)], $options);
    }
}
