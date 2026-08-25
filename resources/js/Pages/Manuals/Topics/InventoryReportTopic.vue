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
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Inventory Staff)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- What are reports -->
                <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                        <LuHelpCircle class="w-3.5 h-3.5" />
                        What are Inventory Reports?
                    </h4>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-3 leading-relaxed">Reports document specific events or issues related to inventory transactions, such as:</p>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            Damaged items
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            Missing or lost items
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            Quality issues
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            Discrepancies in quantities
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            Other inventory concerns
                        </li>
                    </ul>
                </div>

                <!-- Status Tracking -->
                <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                        <LuActivity class="w-3.5 h-3.5" />
                        Report Status Tracking
                    </h4>
                    <ul class="space-y-4 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Filed:</span>
                                Report submitted and waiting for review.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Reviewed:</span>
                                Manager has reviewed the report.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Resolved:</span>
                                Issue has been addressed.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Archived:</span>
                                Report filed and no longer active.
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- How to File -->
            <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                    <LuListPlus class="w-3.5 h-3.5" />
                    How to File a Report
                </h4>
                <ol class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
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
                        <ul class="list-none space-y-1.5 ml-5 mt-2">
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                                Report type / category
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                                Description of the issue
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                                Date and time of incident
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
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
            class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Implementation)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Model Structure -->
                <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                        <LuDatabase class="w-3.5 h-3.5" />
                        Report Model Structure
                    </h4>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mb-3 leading-relaxed">Reports are stored and linked to transactions:</p>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">transaction_id</code>
                            FK to transaction
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">report_data</code>
                            JSON field for flexible data
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">reported_at</code>
                            Timestamp of report
                        </li>
                        <li class="flex items-center">
                            <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">user_id</code>
                            Who filed the report
                        </li>
                    </ul>
                </div>

                <!-- Key Files -->
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                        <LuFolderTree class="w-3.5 h-3.5" />
                        Key Files
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Report Display Component</span>
                            <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-700 dark:text-slate-300 break-all">resources/js/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue</code>
                        </li>
                        <li>
                            <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Report Model</span>
                            <code class="block px-2 py-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-700 dark:text-slate-300 break-all">app/Models/TransactionReport.php</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
