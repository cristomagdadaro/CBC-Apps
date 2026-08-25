<script>
import EquipmentLoggerPersonnelHistoryLog from "@/Modules/domain/EquipmentLoggerPersonnelHistoryLog";
import LaboratoryLogHeaderAction from "@/Pages/Laboratory/components/LaboratoryLogHeaderAction.vue";

export default {
    name: "PersonnelLogHistory",
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
                {
                    label: "Total Logs",
                    value: this.personnelSummary.total_logs ?? 0,
                    color: "text-indigo-600 dark:text-indigo-400",
                },
                {
                    label: "Active",
                    value: this.personnelSummary.active_logs ?? 0,
                    color: "text-emerald-600 dark:text-emerald-400",
                },
                {
                    label: "Overdue",
                    value: this.personnelSummary.overdue_logs ?? 0,
                    color: "text-rose-600 dark:text-rose-400",
                },
                {
                    label: "Completed",
                    value: this.personnelSummary.completed_logs ?? 0,
                    color: "text-slate-600 dark:text-slate-400",
                },
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

        <div class="space-y-6 px-4 sm:px-6 lg:px-8 py-6">
            <!-- Personnel Summary Card -->
            <section class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 p-6 transition-all duration-300 overflow-hidden">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                    <!-- Details Left -->
                    <div class="space-y-6 flex-1">
                        <div>
                            <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-1">Personnel History</p>
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                                {{ personnelSummary.full_name }}
                            </h1>
                            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ personnelSummary.position || "No position recorded" }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuUser class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">Employee ID</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.employee_id || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuMail class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">Email</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                        {{ personnelSummary.email || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuPhone class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">Phone</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.phone || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuMapPin class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">Address</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                        {{ personnelSummary.address || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuClock class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">First Logged</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.first_logged_at || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                <LuClock class="w-4 h-4 text-slate-400 mt-0.5" />
                                <div>
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">Last Logged</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.last_logged_at || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Right -->
                    <div class="grid grid-cols-2 gap-4 w-full lg:w-[26rem] shrink-0">
                        <div
                            v-for="card in statCards"
                            :key="card.label"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 p-5 shadow-sm flex flex-col items-center justify-center text-center">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5">
                                {{ card.label }}
                            </p>
                            <p
                                class="text-3xl font-black"
                                :class="card.color">
                                {{ card.value }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Datatable Section -->
            <section class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/20">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Logging History</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Each row links to the source incoming transaction record.</p>
                </div>

                <div class="p-2 sm:p-4">
                    <CRCMDatatable
                        :base-model="EquipmentLoggerPersonnelHistoryLog"
                        :params="historyParams"
                        :can-view="true"
                        :can-create="false"
                        :can-update="false"
                        :can-delete="false">
                        <template #cell-equipmentName="{ row }">
                            <div class="py-2 w-full max-w-sm">
                                <div class="mb-1">
                                    <Link
                                        :href="
                                            route('transactions.show', {
                                                id: row.latest_incoming_transaction_id,
                                            })
                                        "
                                        class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                        {{ row?.equipment?.name }}
                                    </Link>
                                    <span
                                        v-if="row?.equipment?.description"
                                        class="text-xs font-medium text-slate-500 dark:text-slate-400 block mt-0.5 truncate">
                                        Model: {{ row?.equipment?.description }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    <span
                                        v-if="row?.equipment?.brand"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[0.65rem] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ row?.equipment?.brand }}
                                    </span>
                                    <span
                                        v-if="row?.equipment_barcode"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[0.65rem] font-bold font-mono bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/30">
                                        {{ row?.equipment_barcode }}
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template #cell-status="{ value }">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-widest border"
                                :class="{
                                    'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30': value === 'overdue',
                                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30': value === 'active',
                                    'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700': !['overdue', 'active'].includes(value),
                                }">
                                {{ value }}
                            </span>
                        </template>
                    </CRCMDatatable>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped></style>
