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

        <div class="space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- Personnel Summary Card -->
            <section class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/80">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <!-- Details Left -->
                    <div class="flex-1 space-y-6">
                        <div>
                            <p class="mb-1 text-[0.65rem] font-semibold uppercase text-indigo-600 dark:text-indigo-400">Personnel History</p>
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                                {{ personnelSummary.full_name }}
                            </h1>
                            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ personnelSummary.position || "No position recorded" }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuUser class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Employee ID</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.employee_id || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuMail class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Email</p>
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.email || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuPhone class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Phone</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.phone || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuMapPin class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Address</p>
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.address || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuClock class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">First Logged</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.first_logged_at || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <LuClock class="mt-0.5 h-4 w-4 text-slate-400" />
                                <div>
                                    <p class="mb-0.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Last Logged</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ personnelSummary.last_logged_at || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Right -->
                    <div class="grid w-full shrink-0 grid-cols-2 gap-4 lg:w-[26rem]">
                        <div
                            v-for="card in statCards"
                            :key="card.label"
                            class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
                            <p class="mb-1.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">
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
            <section class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800/60 dark:bg-slate-800/20">
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Logging History</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Each row links to the source incoming transaction record.</p>
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
                            <div class="w-full max-w-sm py-2">
                                <div class="mb-1">
                                    <Link
                                        :href="
                                            route('transactions.show', {
                                                id: row.latest_incoming_transaction_id,
                                            })
                                        "
                                        class="line-clamp-2 text-sm font-bold leading-snug text-indigo-600 transition-colors hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        {{ row?.equipment?.name }}
                                    </Link>
                                    <span
                                        v-if="row?.equipment?.description"
                                        class="mt-0.5 block truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                        Model: {{ row?.equipment?.description }}
                                    </span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="row?.equipment?.brand"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ row?.equipment?.brand }}
                                    </span>
                                    <span
                                        v-if="row?.equipment_barcode"
                                        class="inline-flex items-center rounded border border-indigo-200 bg-indigo-50 px-2 py-0.5 font-mono text-[0.65rem] font-bold text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        {{ row?.equipment_barcode }}
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template #cell-status="{ value }">
                            <span
                                class="inline-flex items-center rounded-md border px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-widest"
                                :class="{
                                    'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400': value === 'overdue',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400': value === 'active',
                                    'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300': !['overdue', 'active'].includes(value),
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
