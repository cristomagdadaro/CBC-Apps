<script>
import TopicLayout from "../Components/TopicLayout.vue";

export default {
    name: "DriverJsGuidesTopic",
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
        title="Interactive Tours with Driver.js"
        description="CBC-Apps uses Driver.js to provide interactive tours for the landing page and public-facing workflows. These tours help first-time guests understand where to start, what information is collected, and how to replay or disable the guide."
        icon="LuCompass">
        <!-- Current Guest Flow -->
        <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
            <h4 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-sky-600 dark:text-sky-400">
                <LuRoute class="h-3.5 w-3.5" />
                Current Guest Flow
            </h4>
            <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                <li>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">Guests land</span>
                    on the public welcome page.
                </li>
                <li>
                    They are shown a
                    <span class="font-semibold text-slate-800 dark:text-slate-200">data privacy notice</span>
                    before the startup guide is enabled.
                </li>
                <li>
                    If they agree and guides are enabled, the startup tour runs
                    <span class="font-semibold text-slate-800 dark:text-slate-200">automatically</span>
                    for first-time visitors.
                </li>
                <li>
                    Every public page exposes
                    <span class="font-semibold text-slate-800 dark:text-slate-200">guide controls</span>
                    so the tour can be replayed or toggled.
                </li>
            </ol>
        </div>

        <!-- Files You Will Touch -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4">
            <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">
                <LuFileCode2 class="h-3.5 w-3.5" />
                Files You Will Touch
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <LuSettings2 class="h-4 w-4 text-indigo-500" />
                        Guide Registry
                    </h4>
                    <code class="mb-2.5 block w-full break-all rounded border border-slate-200 bg-white px-2 py-1 font-mono text-[0.6rem] font-semibold text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">resources/js/Modules/guides/tourRegistry.js</code>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Defines guide keys, steps, inheritance, and the local-storage keys used to remember guide state.</p>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <LuRoute class="h-4 w-4 text-emerald-500" />
                        Guide Runtime
                    </h4>
                    <code class="mb-2.5 block w-full break-all rounded border border-slate-200 bg-white px-2 py-1 font-mono text-[0.6rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">resources/js/Modules/composables/useGuideTour.js</code>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Starts a Driver.js tour, respects privacy consent, tracks automatic-guide preferences, and marks tours as seen.</p>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <LuPanelTop class="h-4 w-4 text-amber-500" />
                        Guest Controls
                    </h4>
                    <code class="mb-2.5 block w-full break-all rounded border border-slate-200 bg-white px-2 py-1 font-mono text-[0.6rem] font-semibold text-amber-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400">resources/js/Components/GuideTourControls.vue</code>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Renders the floating controls used to start a guide or toggle automatic tours.</p>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <LuShieldCheck class="h-4 w-4 text-rose-500" />
                        Privacy Notice
                    </h4>
                    <code class="mb-2.5 block w-full break-all rounded border border-slate-200 bg-white px-2 py-1 font-mono text-[0.6rem] font-semibold text-rose-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-rose-400">resources/js/Components/DataPrivacyNoticeModal.vue</code>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Captures the guest acknowledgement required before startup guides run automatically.</p>
                </div>
            </div>
        </div>

        <!-- How to Add a Tour -->
        <div
            v-if="showDeveloperSections"
            class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
            <h4 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                <LuListPlus class="h-3.5 w-3.5" />
                Adding a Tour to a New Public Feature
            </h4>
            <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                <li>
                    Create a stable guide key in
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">tourRegistry.js</code>
                    .
                </li>
                <li>Add 2-4 short steps that explain the page purpose, the primary action area, and the guide controls.</li>
                <li>
                    Mark the important page regions with
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">data-guide</code>
                    attributes so the tour can anchor reliably.
                </li>
                <li>
                    If the page uses
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">GuestFormPage</code>
                    , pass a
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">guide-key</code>
                    prop and reuse the shared guest-page tour.
                </li>
                <li>
                    If the page is not based on
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">GuestFormPage</code>
                    , render
                    <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">GuideTourControls</code>
                    directly and add its own anchors.
                </li>
                <li>Update the manuals topic if the new tour introduces a new public workflow or implementation pattern.</li>
            </ol>
        </div>

        <!-- Implementation Notes -->
        <div
            v-if="showDeveloperSections"
            class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
            <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                <LuInfo class="h-3.5 w-3.5" />
                Implementation Notes
            </h4>
            <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                    <span class="leading-relaxed">
                        Keep selectors stable and semantic. Prefer
                        <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-amber-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400">data-guide</code>
                        attributes over styling classes.
                    </span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                    <span class="leading-relaxed">Keep copy brief and task-focused. Tours should orient, not duplicate the manuals.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                    <span class="leading-relaxed">Public tours should avoid exposing internal-only details, private names, or staff-only workflow rules.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                    <span class="leading-relaxed">When a public page collects data, ensure the privacy notice and the tour language match the actual data use.</span>
                </li>
            </ul>
        </div>
    </TopicLayout>
</template>
