<script>
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'CertificateGeneratorTopic',
    components: {
        TopicLayout
    }
}
</script>

<template>
    <TopicLayout
        title="Certificate Generator Manual"
        description="Use this guide when generating eCertificates from event responses in `/apps/event-forms` (Event Update page &rarr; eCertificates tab)[cite: 9]."
        icon="LuFileBadge"
    >
            <div class="p-6 space-y-8">
                
                <!-- Formatting Rules -->
                <div class="bg-sky-50/50 dark:bg-sky-500/10 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-3 flex items-center gap-1.5">
                        <LuSettings2 class="w-3.5 h-3.5" /> Template Formatting Rules
                    </h4>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" /> 
                            <span>Use placeholder format <span class="font-bold text-slate-800 dark:text-slate-200">&lt;&lt;COLUMN_NAME&gt;&gt;</span> inside text boxes or table cells[cite: 9].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" /> 
                            <span>Use the exact selected column key from the UI whenever possible (example: <code class="font-mono text-sky-600 dark:text-sky-400">fullname_8647</code>)[cite: 9].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" /> 
                            <span><span class="font-bold text-slate-800 dark:text-slate-200">&lt;&lt;NAME&gt;&gt;</span> can resolve to fullname-style fields, but exact keys are most reliable[cite: 9].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" /> 
                            <span>Avoid putting placeholder brackets in separate text boxes; keep each placeholder in one text element[cite: 9].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" /> 
                            <span>Save template as <span class="font-bold text-slate-800 dark:text-slate-200">.pptx</span> only[cite: 9].</span>
                        </li>
                    </ul>
                </div>

                <!-- Steps to Generate -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                        <LuListChecks class="w-3.5 h-3.5" /> How to Use the Generator
                    </h4>
                    <ol class="space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Open an event</span>, then go to the <span class="font-semibold text-slate-800 dark:text-slate-200">eCertificates</span> tab[cite: 9].
                        </li>
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Decide template source:</span>[cite: 9]
                            <ul class="list-none space-y-2 ml-5 mt-2 text-slate-600 dark:text-slate-400 border-l border-emerald-200 dark:border-emerald-500/30 pl-3">
                                <li>&bull; Enable <span class="font-semibold text-slate-700 dark:text-slate-300">Use saved template for this event</span> to use the saved/default template[cite: 9].</li>
                                <li>&bull; Disable it if you want to upload a new template now[cite: 9].</li>
                                <li>&bull; Use <span class="font-semibold text-slate-700 dark:text-slate-300">View Saved Template</span> to check the currently active template[cite: 9].</li>
                            </ul>
                        </li>
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Decide data source:</span>[cite: 9]
                            <ul class="list-none space-y-2 ml-5 mt-2 text-slate-600 dark:text-slate-400 border-l border-emerald-200 dark:border-emerald-500/30 pl-3">
                                <li>&bull; Enable <span class="font-semibold text-slate-700 dark:text-slate-300">Use Event Responses</span> to generate from submitted form data[cite: 9].</li>
                                <li>&bull; Disable it to upload your own <span class="font-semibold text-slate-700 dark:text-slate-300">.xlsx</span> or <span class="font-semibold text-slate-700 dark:text-slate-300">.csv</span> file[cite: 9].</li>
                            </ul>
                        </li>
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">If using Event Responses:</span>[cite: 9]
                            <ul class="list-none space-y-2 ml-5 mt-2 text-slate-600 dark:text-slate-400 border-l border-emerald-200 dark:border-emerald-500/30 pl-3">
                                <li>&bull; Select <span class="font-semibold text-slate-700 dark:text-slate-300">Name column</span>[cite: 9].</li>
                                <li>&bull; Select <span class="font-semibold text-slate-700 dark:text-slate-300">Email column</span>[cite: 9].</li>
                                <li>&bull; Optionally select <span class="font-semibold text-slate-700 dark:text-slate-300">Subform type</span> to limit the response source[cite: 9].</li>
                            </ul>
                        </li>
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Set output format:</span>[cite: 9]
                            <ul class="list-none space-y-2 ml-5 mt-2 text-slate-600 dark:text-slate-400 border-l border-emerald-200 dark:border-emerald-500/30 pl-3">
                                <li>&bull; <span class="font-semibold text-slate-700 dark:text-slate-300">PPTX:</span> editable certificate files[cite: 9].</li>
                                <li>&bull; <span class="font-semibold text-slate-700 dark:text-slate-300">PDF:</span> fixed-print format[cite: 9].</li>
                                <li>&bull; <span class="font-semibold text-slate-700 dark:text-slate-300">PNG/JPG:</span> image exports (no Poppler setup needed in current generator flow)[cite: 9].</li>
                            </ul>
                        </li>
                        <li>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Configure naming convention (optional):</span>[cite: 9]
                            <ul class="list-none space-y-2 ml-5 mt-2 text-slate-600 dark:text-slate-400 border-l border-emerald-200 dark:border-emerald-500/30 pl-3">
                                <li>&bull; Example: <code class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm text-emerald-600 dark:text-emerald-400">{event}_{Fullname}_{date}</code>[cite: 9].</li>
                                <li>&bull; You may use any column key in curly braces[cite: 9].</li>
                            </ul>
                        </li>
                        <li>
                            Click <span class="font-semibold text-slate-800 dark:text-slate-200">Upload & Process</span> and wait for status to become completed[cite: 9].
                        </li>
                        <li>
                            Click <span class="font-semibold text-slate-800 dark:text-slate-200">Download ZIP</span> to retrieve all generated files[cite: 9].
                        </li>
                    </ol>
                </div>

                <!-- Troubleshooting -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800/60">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                        <LuAlertTriangle class="w-3.5 h-3.5" /> Troubleshooting
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-4 shadow-sm flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                                <LuFileQuestion class="w-4 h-4 shrink-0" />
                                <h4 class="text-xs font-semibold">Placeholder not replaced</h4>
                            </div>
                            <p class="text-[0.65rem] font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                                Use exact column keys from the Name/Email dropdown list, then regenerate[cite: 9].
                            </p>
                        </div>

                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-4 shadow-sm flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                                <LuXCircle class="w-4 h-4 shrink-0" />
                                <h4 class="text-xs font-semibold">413 Request too large</h4>
                            </div>
                            <p class="text-[0.65rem] font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                                Use saved template and event responses (no file upload), or reduce file size[cite: 9].
                            </p>
                        </div>

                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-4 shadow-sm flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400">
                                <LuServerCrash class="w-4 h-4 shrink-0" />
                                <h4 class="text-xs font-semibold">Conversion failure</h4>
                            </div>
                            <p class="text-[0.65rem] font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                                Verify LibreOffice path in environment and ensure the queue worker is restarted[cite: 9].
                            </p>
                        </div>
                    </div>
                </div>

    </TopicLayout>
</template>