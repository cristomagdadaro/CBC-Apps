<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "InventoryReportTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
};
</script>

<template>
    <TopicLayout
        title="Inventory Reports"
        description="Reports document specific events or issues related to inventory transactions, such as damaged items, missing stock, or quality concerns."
        icon="LuFileWarning">
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Non-Programmers (Inventory Staff)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- What are reports -->
                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-sky-600 dark:text-sky-400">
                        <LuHelpCircle class="h-3.5 w-3.5" />
                        What are Inventory Reports?
                    </h4>
                    <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">Reports document specific events or issues related to inventory transactions, such as:</p>
                    <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Damaged items
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Missing or lost items
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Quality issues
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Discrepancies in quantities
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Other inventory concerns
                        </li>
                    </ul>
                </div>

                <!-- Status Tracking -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                        <LuActivity class="h-3.5 w-3.5" />
                        Report Status Tracking
                    </h4>
                    <ul class="ml-1 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Filed:</span>
                                Report submitted and waiting for review.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Reviewed:</span>
                                Manager has reviewed the report.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Resolved:</span>
                                Issue has been addressed.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Archived:</span>
                                Report filed and no longer active.
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- How to File -->
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                    <LuListPlus class="h-3.5 w-3.5" />
                    How to File a Report
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li>
                        Open a
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Transaction</span>
                        that needs a report attached.
                    </li>
                    <li>
                        Scroll down to the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Attached Reports</span>
                        section.
                    </li>
                    <li>
                        Click the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Add Report</span>
                        button.
                    </li>
                    <li>
                        Fill in the report details:
                        <ul class="ml-5 mt-2 list-none space-y-1.5">
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Report type / category
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Description of the issue
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Date and time of incident
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-emerald-400"></div>
                                Any relevant notes or attachments
                            </li>
                        </ul>
                    </li>
                    <li>
                        Click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Submit Report</span>
                        .
                    </li>
                    <li>The report is now linked to the transaction and visible to managers.</li>
                </ol>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Programmers (Implementation)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Model Structure -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                        <LuDatabase class="h-3.5 w-3.5" />
                        Report Model Structure
                    </h4>
                    <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Reports are stored and linked to transactions:</p>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">transaction_id</code>
                            FK to transaction
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">report_data</code>
                            JSON field for flexible data
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">reported_at</code>
                            Timestamp of report
                        </li>
                        <li class="flex items-center">
                            <code class="mr-2 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">user_id</code>
                            Who filed the report
                        </li>
                    </ul>
                </div>

                <!-- Key Files -->
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-600 dark:text-slate-300">
                        <LuFolderTree class="h-3.5 w-3.5" />
                        Key Files
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Report Display Component</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">resources/js/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue</code>
                        </li>
                        <li>
                            <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Report Model</span>
                            <code class="block break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">app/Models/TransactionReport.php</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
