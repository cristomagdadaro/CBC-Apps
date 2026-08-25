<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "CertificateGeneratorTopic",
    components: {
        TopicLayout,
    },
};
</script>

<template>
    <TopicLayout
        title="Certificate Generator Manual"
        description="Use this guide when generating eCertificates from event responses in `/apps/event-forms` (Event Update page &rarr; eCertificates tab)[cite: 9]."
        icon="LuFileBadge">
        <div class="space-y-8 p-6">
            <!-- Formatting Rules -->
            <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <LuSettings2 class="h-3.5 w-3.5" />
                    Template Formatting Rules
                </h4>
                <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                        <span>
                            Use placeholder format
                            <span class="font-bold text-slate-800 dark:text-slate-200">&lt;&lt;COLUMN_NAME&gt;&gt;</span>
                            inside text boxes or table cells[cite: 9].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                        <span>
                            Use the exact selected column key from the UI whenever possible (example:
                            <code class="font-mono text-sky-600 dark:text-sky-400">fullname_8647</code>
                            )[cite: 9].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                        <span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">&lt;&lt;NAME&gt;&gt;</span>
                            can resolve to fullname-style fields, but exact keys are most reliable[cite: 9].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                        <span>Avoid putting placeholder brackets in separate text boxes; keep each placeholder in one text element[cite: 9].</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                        <span>
                            Save template as
                            <span class="font-bold text-slate-800 dark:text-slate-200">.pptx</span>
                            only[cite: 9].
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Steps to Generate -->
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    <LuListChecks class="h-3.5 w-3.5" />
                    How to Use the Generator
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Open an event</span>
                        , then go to the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">eCertificates</span>
                        tab[cite: 9].
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Decide template source:</span>
                        [cite: 9]
                        <ul class="ml-5 mt-2 list-none space-y-2 border-l border-emerald-200 pl-3 text-slate-600 dark:border-emerald-500/30 dark:text-slate-400">
                            <li>
                                &bull; Enable
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Use saved template for this event</span>
                                to use the saved/default template[cite: 9].
                            </li>
                            <li>&bull; Disable it if you want to upload a new template now[cite: 9].</li>
                            <li>
                                &bull; Use
                                <span class="font-semibold text-slate-700 dark:text-slate-300">View Saved Template</span>
                                to check the currently active template[cite: 9].
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Decide data source:</span>
                        [cite: 9]
                        <ul class="ml-5 mt-2 list-none space-y-2 border-l border-emerald-200 pl-3 text-slate-600 dark:border-emerald-500/30 dark:text-slate-400">
                            <li>
                                &bull; Enable
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Use Event Responses</span>
                                to generate from submitted form data[cite: 9].
                            </li>
                            <li>
                                &bull; Disable it to upload your own
                                <span class="font-semibold text-slate-700 dark:text-slate-300">.xlsx</span>
                                or
                                <span class="font-semibold text-slate-700 dark:text-slate-300">.csv</span>
                                file[cite: 9].
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">If using Event Responses:</span>
                        [cite: 9]
                        <ul class="ml-5 mt-2 list-none space-y-2 border-l border-emerald-200 pl-3 text-slate-600 dark:border-emerald-500/30 dark:text-slate-400">
                            <li>
                                &bull; Select
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Name column</span>
                                [cite: 9].
                            </li>
                            <li>
                                &bull; Select
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Email column</span>
                                [cite: 9].
                            </li>
                            <li>
                                &bull; Optionally select
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Subform type</span>
                                to limit the response source[cite: 9].
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Set output format:</span>
                        [cite: 9]
                        <ul class="ml-5 mt-2 list-none space-y-2 border-l border-emerald-200 pl-3 text-slate-600 dark:border-emerald-500/30 dark:text-slate-400">
                            <li>
                                &bull;
                                <span class="font-semibold text-slate-700 dark:text-slate-300">PPTX:</span>
                                editable certificate files[cite: 9].
                            </li>
                            <li>
                                &bull;
                                <span class="font-semibold text-slate-700 dark:text-slate-300">PDF:</span>
                                fixed-print format[cite: 9].
                            </li>
                            <li>
                                &bull;
                                <span class="font-semibold text-slate-700 dark:text-slate-300">PNG/JPG:</span>
                                image exports (no Poppler setup needed in current generator flow)[cite: 9].
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Configure naming convention (optional):</span>
                        [cite: 9]
                        <ul class="ml-5 mt-2 list-none space-y-2 border-l border-emerald-200 pl-3 text-slate-600 dark:border-emerald-500/30 dark:text-slate-400">
                            <li>
                                &bull; Example:
                                <code class="rounded bg-white px-1 py-0.5 font-mono text-emerald-600 shadow-sm dark:bg-slate-800 dark:text-emerald-400">{event}_{Fullname}_{date}</code>
                                [cite: 9].
                            </li>
                            <li>&bull; You may use any column key in curly braces[cite: 9].</li>
                        </ul>
                    </li>
                    <li>
                        Click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Upload & Process</span>
                        and wait for status to become completed[cite: 9].
                    </li>
                    <li>
                        Click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Download ZIP</span>
                        to retrieve all generated files[cite: 9].
                    </li>
                </ol>
            </div>

            <!-- Troubleshooting -->
            <div class="space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800/60">
                <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <LuAlertTriangle class="h-3.5 w-3.5" />
                    Troubleshooting
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="flex flex-col gap-2 rounded-xl border border-rose-100 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                            <LuFileQuestion class="h-4 w-4 shrink-0" />
                            <h4 class="text-xs font-semibold">Placeholder not replaced</h4>
                        </div>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-slate-600 dark:text-slate-400">Use exact column keys from the Name/Email dropdown list, then regenerate[cite: 9].</p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl border border-rose-100 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                            <LuXCircle class="h-4 w-4 shrink-0" />
                            <h4 class="text-xs font-semibold">413 Request too large</h4>
                        </div>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-slate-600 dark:text-slate-400">Use saved template and event responses (no file upload), or reduce file size[cite: 9].</p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl border border-rose-100 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                            <LuServerCrash class="h-4 w-4 shrink-0" />
                            <h4 class="text-xs font-semibold">Conversion failure</h4>
                        </div>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-slate-600 dark:text-slate-400">Verify LibreOffice path in environment and ensure the queue worker is restarted[cite: 9].</p>
                    </div>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
