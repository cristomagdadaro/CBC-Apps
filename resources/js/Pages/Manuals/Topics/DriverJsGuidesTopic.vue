<script>
import TopicLayout from '../Components/TopicLayout.vue';

export default {
    name: "DriverJsGuidesTopic",
    components: {
        TopicLayout
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
        title="Interactive Tours with Driver.js"
        description="CBC-Apps uses Driver.js to provide interactive tours for the landing page and public-facing workflows. These tours help first-time guests understand where to start, what information is collected, and how to replay or disable the guide."
        icon="LuCompass"
    >
        <!-- Current Guest Flow -->
        <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-3.5 flex items-center gap-1.5">
                        <LuRoute class="w-3.5 h-3.5" /> Current Guest Flow
                    </h4>
                    <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li><span class="font-semibold text-slate-800 dark:text-slate-200">Guests land</span> on the public welcome page.</li>
                        <li>They are shown a <span class="font-semibold text-slate-800 dark:text-slate-200">data privacy notice</span> before the startup guide is enabled.</li>
                        <li>If they agree and guides are enabled, the startup tour runs <span class="font-semibold text-slate-800 dark:text-slate-200">automatically</span> for first-time visitors.</li>
                        <li>Every public page exposes <span class="font-semibold text-slate-800 dark:text-slate-200">guide controls</span> so the tour can be replayed or toggled.</li>
                    </ol>
                </div>

                <!-- Files You Will Touch -->
                <div v-if="showDeveloperSections" class="space-y-4">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                        <LuFileCode2 class="w-3.5 h-3.5" /> Files You Will Touch
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <LuSettings2 class="w-4 h-4 text-indigo-500" /> Guide Registry
                            </h4>
                            <code class="block w-full px-2 py-1 mb-2.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400 break-all">resources/js/Modules/guides/tourRegistry.js</code>
                            <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                Defines guide keys, steps, inheritance, and the local-storage keys used to remember guide state.
                            </p>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <LuRoute class="w-4 h-4 text-emerald-500" /> Guide Runtime
                            </h4>
                            <code class="block w-full px-2 py-1 mb-2.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400 break-all">resources/js/Modules/composables/useGuideTour.js</code>
                            <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                Starts a Driver.js tour, respects privacy consent, tracks automatic-guide preferences, and marks tours as seen.
                            </p>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <LuPanelTop class="w-4 h-4 text-amber-500" /> Guest Controls
                            </h4>
                            <code class="block w-full px-2 py-1 mb-2.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] font-semibold shadow-sm text-amber-600 dark:text-amber-400 break-all">resources/js/Components/GuideTourControls.vue</code>
                            <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                Renders the floating controls used to start a guide or toggle automatic tours.
                            </p>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <LuShieldCheck class="w-4 h-4 text-rose-500" /> Privacy Notice
                            </h4>
                            <code class="block w-full px-2 py-1 mb-2.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] font-semibold shadow-sm text-rose-600 dark:text-rose-400 break-all">resources/js/Components/DataPrivacyNoticeModal.vue</code>
                            <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                Captures the guest acknowledgement required before startup guides run automatically.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- How to Add a Tour -->
                <div v-if="showDeveloperSections" class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3.5 flex items-center gap-1.5">
                        <LuListPlus class="w-3.5 h-3.5" /> Adding a Tour to a New Public Feature
                    </h4>
                    <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>Create a stable guide key in <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">tourRegistry.js</code>.</li>
                        <li>Add 2-4 short steps that explain the page purpose, the primary action area, and the guide controls.</li>
                        <li>Mark the important page regions with <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">data-guide</code> attributes so the tour can anchor reliably.</li>
                        <li>If the page uses <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">GuestFormPage</code>, pass a <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">guide-key</code> prop and reuse the shared guest-page tour.</li>
                        <li>If the page is not based on <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">GuestFormPage</code>, render <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-600 dark:text-emerald-400">GuideTourControls</code> directly and add its own anchors.</li>
                        <li>Update the manuals topic if the new tour introduces a new public workflow or implementation pattern.</li>
                    </ol>
                </div>

                <!-- Implementation Notes -->
                <div v-if="showDeveloperSections" class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                        <LuInfo class="w-3.5 h-3.5" /> Implementation Notes
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> 
                            <span class="leading-relaxed">Keep selectors stable and semantic. Prefer <code class="px-1.5 py-0.5 mx-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-amber-600 dark:text-amber-400">data-guide</code> attributes over styling classes.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> 
                            <span class="leading-relaxed">Keep copy brief and task-focused. Tours should orient, not duplicate the manuals.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> 
                            <span class="leading-relaxed">Public tours should avoid exposing internal-only details, private names, or staff-only workflow rules.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> 
                            <span class="leading-relaxed">When a public page collects data, ensure the privacy notice and the tour language match the actual data use.</span>
                        </li>
                    </ul>
                </div>

    </TopicLayout>
</template>