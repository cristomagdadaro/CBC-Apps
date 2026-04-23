<script>
import EquipmentLoggerPersonnelHistoryLog from '@/Modules/domain/EquipmentLoggerPersonnelHistoryLog';
import LaboratoryLogHeaderAction from '@/Pages/Laboratory/components/LaboratoryLogHeaderAction.vue';

export default {
    name: 'PersonnelLogHistory',
    components: {
        LaboratoryLogHeaderAction,
    },
    props: {
        personnelId: {
            type: String,
            required: true,
        },
        personnelSummary: {
            type: Object,
            required: true,
        },
    },
    computed: {
        historyParams() {
            return {
                routeParams: {
                    personnelId: this.personnelId,
                },
            };
        },
        EquipmentLoggerPersonnelHistoryLog() {
            return EquipmentLoggerPersonnelHistoryLog;
        },
        statCards() {
            return [
                { label: 'Total Logs', value: this.personnelSummary.total_logs ?? 0 },
                { label: 'Active', value: this.personnelSummary.active_logs ?? 0 },
                { label: 'Overdue', value: this.personnelSummary.overdue_logs ?? 0 },
                { label: 'Completed', value: this.personnelSummary.completed_logs ?? 0 },
            ];
        },
    },
};
</script>

<template>
    <AppLayout title="Personnel Logging History">
        <template #header>
            <LaboratoryLogHeaderAction />
        </template>

        <div class="space-y-6 px-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Personnel History</p>
                            <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ personnelSummary.full_name }}</h1>
                            <p class="mt-1 text-sm text-slate-600">{{ personnelSummary.position || 'No position recorded' }}</p>
                        </div>

                        <div class="grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                            <div>
                                <span class="font-medium text-slate-900">Employee ID:</span>
                                {{ personnelSummary.employee_id || '-' }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900">Email:</span>
                                {{ personnelSummary.email || '-' }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900">Phone:</span>
                                {{ personnelSummary.phone || '-' }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900">Address:</span>
                                {{ personnelSummary.address || '-' }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900">First Logged:</span>
                                {{ personnelSummary.first_logged_at || '-' }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900">Last Logged:</span>
                                {{ personnelSummary.last_logged_at || '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:w-[24rem]">
                        <div
                            v-for="card in statCards"
                            :key="card.label"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ card.value }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-900">Logging History</h2>
                    <p class="text-xs text-slate-500">Each row links to the source incoming transaction record.</p>
                </div>

                <CRCMDatatable
                    :base-model="EquipmentLoggerPersonnelHistoryLog"
                    :params="historyParams"
                    :can-view="true"
                    :can-create="false"
                    :can-update="false"
                    :can-delete="false"
                >
                    <template #cell-equipmentName="{ row, value }">
                        <div class="min-w-[16rem] whitespace-normal">
                            <a
                                v-if="row.latest_incoming_transaction_id"
                                :href="route('transactions.show', { id: row.latest_incoming_transaction_id })"
                                target="_blank"
                                class="font-medium text-blue-700 hover:underline"
                            >
                                {{ value }}
                            </a>
                            <span v-else class="font-medium text-slate-900">{{ value }}</span>
                        </div>
                    </template>
                    <template #cell-status="{ value }">
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium uppercase"
                            :class="value === 'overdue' ? 'bg-red-100 text-red-700' : value === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700'"
                        >
                            {{ value }}
                        </span>
                    </template>
                </CRCMDatatable>
            </section>
        </div>
    </AppLayout>
</template>
