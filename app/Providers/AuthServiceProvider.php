<?php

namespace App\Providers;

use App\Models\Form;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\RequestFormPivot;
use App\Models\SuppEquipReport;
use App\Models\Transaction;
use App\Policies\FormPolicy;
use App\Policies\LaboratoryEquipmentLogPolicy;
use App\Policies\ResearchExperimentPolicy;
use App\Policies\ResearchMonitoringRecordPolicy;
use App\Policies\ResearchProjectPolicy;
use App\Policies\ResearchSamplePolicy;
use App\Policies\ResearchStudyPolicy;
use App\Policies\RentalVehiclePolicy;
use App\Policies\RentalVenuePolicy;
use App\Policies\RequestFormPivotPolicy;
use App\Policies\SuppEquipReportPolicy;
use App\Policies\TransactionPolicy;
use App\Services\RbacService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Form::class => FormPolicy::class,
        RequestFormPivot::class => RequestFormPivotPolicy::class,
        Transaction::class => TransactionPolicy::class,
        SuppEquipReport::class => SuppEquipReportPolicy::class,
        LaboratoryEquipmentLog::class => LaboratoryEquipmentLogPolicy::class,
        RentalVehicle::class => RentalVehiclePolicy::class,
        RentalVenue::class => RentalVenuePolicy::class,
        ResearchProject::class => ResearchProjectPolicy::class,
        ResearchStudy::class => ResearchStudyPolicy::class,
        ResearchExperiment::class => ResearchExperimentPolicy::class,
        ResearchSample::class => ResearchSamplePolicy::class,
        ResearchMonitoringRecord::class => ResearchMonitoringRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $rbacService = app(RbacService::class);

        foreach (config('rbac.permissions', []) as $permission) {
            Gate::define($permission, fn ($user) => $rbacService->hasPermission($user, $permission));
        }
    }
}
