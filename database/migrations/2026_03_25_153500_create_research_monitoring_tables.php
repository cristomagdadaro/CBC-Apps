<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('commodity')->nullable();
            $table->date('duration_start')->nullable();
            $table->date('duration_end')->nullable();
            $table->decimal('overall_budget', 14, 2)->nullable();
            $table->longText('objective')->nullable();
            $table->string('funding_agency')->nullable();
            $table->string('funding_code')->nullable();
            $table->longText('project_leader')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commodity', 'funding_agency']);
        });

        Schema::create('research_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('research_projects')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->longText('objective')->nullable();
            $table->decimal('budget', 14, 2)->nullable();
            $table->longText('study_leader')->nullable();
            $table->longText('staff_members')->nullable();
            $table->longText('supervisor')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
        });

        Schema::create('research_experiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('research_studies')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('geographic_location')->nullable();
            $table->string('season', 20)->nullable();
            $table->string('commodity')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('sample_descriptor')->nullable();
            $table->string('pr_code')->nullable();
            $table->longText('cross_combination')->nullable();
            $table->longText('parental_background')->nullable();
            $table->string('filial_generation')->nullable();
            $table->string('generation')->nullable();
            $table->string('plot_number')->nullable();
            $table->string('field_number')->nullable();
            $table->unsignedInteger('replication_number')->nullable();
            $table->unsignedInteger('planned_plant_count')->nullable();
            $table->longText('background_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['study_id', 'season']);
            $table->index(['commodity', 'sample_type']);
        });

        Schema::create('research_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained('research_experiments')->cascadeOnDelete();
            $table->string('uid', 16)->unique();
            $table->unsignedInteger('sequence_number');
            $table->string('commodity')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('accession_name');
            $table->string('pr_code')->nullable();
            $table->string('field_label')->nullable();
            $table->string('line_label')->nullable();
            $table->string('plant_label')->nullable();
            $table->string('generation')->nullable();
            $table->string('plot_number')->nullable();
            $table->string('field_number')->nullable();
            $table->unsignedInteger('replication_number')->nullable();
            $table->string('current_status')->nullable();
            $table->string('current_location')->nullable();
            $table->string('storage_location')->nullable();
            $table->date('germination_date')->nullable();
            $table->date('sowing_date')->nullable();
            $table->date('harvest_date')->nullable();
            $table->boolean('is_priority')->default(false);
            $table->string('legacy_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['experiment_id', 'sequence_number']);
            $table->index(['experiment_id', 'replication_number']);
        });

        Schema::create('research_monitoring_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('research_samples')->cascadeOnDelete();
            $table->string('stage', 40);
            $table->date('recorded_on');
            $table->longText('parameter_set')->nullable();
            $table->longText('notes')->nullable();
            $table->boolean('selected_for_export')->default(false);
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sample_id', 'stage']);
            $table->index('recorded_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_monitoring_records');
        Schema::dropIfExists('research_samples');
        Schema::dropIfExists('research_experiments');
        Schema::dropIfExists('research_studies');
        Schema::dropIfExists('research_projects');
    }
};
