<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "ResearchMonitoringTopic",
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
        title="Research Monitoring"
        description="Tracks the full research workflow from project planning to experiments, monitoring records, and sample inventory[cite: 5]. Designed for teams needing a reliable chain of records for studies, samples, movement history, and printable identifiers[cite: 5]."
        icon="LuMicroscope">
        <!-- Overview Note -->
        <div class="flex items-start gap-3 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
            <LuShieldCheck class="mt-0.5 h-5 w-5 shrink-0 text-indigo-500" />
            <p class="text-xs font-medium leading-relaxed text-indigo-900 dark:text-indigo-200">The module combines research metadata, barcode-based inventory handling, QR-based retrieval context, and audit visibility so staff can trace what was created, where it is stored, and what monitoring stages have already been completed[cite: 5].</p>
        </div>

        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Non-Programmers (Researchers & Staff)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- What you can do -->
                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                        <LuListChecks class="h-3.5 w-3.5" />
                        What you can do
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <span class="leading-relaxed">Create and maintain research project profiles with funding, duration, and leadership details[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <span class="leading-relaxed">Add studies and experiments under a single project for organized monitoring[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <span class="leading-relaxed">Register samples and assign unique barcode identifiers for storage and movement tracking[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <span class="leading-relaxed">Record monitoring updates across research stages[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-500" />
                            <span class="leading-relaxed">Scan, retrieve, and print labels from the sample inventory view[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Recommended Workflow -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuRoute class="h-3.5 w-3.5" />
                        Recommended Workflow
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Create a
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Research Project</span>
                            and fill in title, commodity, dates, budget, and leader[cite: 5].
                        </li>
                        <li>
                            Add the related
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Study or Experiment</span>
                            records so the project is broken into activities[cite: 5].
                        </li>
                        <li>Register samples once materials are available for tracking[cite: 5].</li>
                        <li>Generate and print barcode or QR labels before storage or distribution[cite: 5].</li>
                        <li>Use monitoring records to log progress, checkpoints, observations, and status[cite: 5].</li>
                        <li>
                            Open
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Research Sample Inventory</span>
                            whenever you need to scan or print labels again[cite: 5].
                        </li>
                    </ol>
                </div>

                <!-- Barcode & QR -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                        <LuQrCode class="h-3.5 w-3.5" />
                        Barcode and QR labels
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Barcode</span>
                                is best for quick scanning during audits, shelf checks, hand-offs, and movement[cite: 5].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">QR code</span>
                                is best when the scanner or viewer needs more context such as retrieval details or display-ready sample data[cite: 5].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-500" />
                            <p class="leading-relaxed text-amber-700 dark:text-amber-400/90">Label printing and scan-based lookups should be treated as part of the sample history, so staff should avoid reusing labels across different samples[cite: 5].</p>
                        </li>
                    </ul>
                </div>

                <!-- Access & Practices -->
                <div class="space-y-6">
                    <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                            <LuMapPin class="h-3.5 w-3.5" />
                            Main Access Points
                        </h4>
                        <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-2.5">
                                <div class="h-1 w-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Dashboard</span>
                                for overview cards[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="h-1 w-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Projects</span>
                                for main profiles[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="h-1 w-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Experiments</span>
                                for status work[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="h-1 w-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Sample Inventory</span>
                                for scanning[cite: 5].
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                            <LuShieldCheck class="h-3.5 w-3.5" />
                            Good Operating Practices
                        </h4>
                        <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3 w-3 shrink-0 text-rose-400" />
                                <span class="leading-relaxed">Finalize titles before printing large batches of labels[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3 w-3 shrink-0 text-rose-400" />
                                <span class="leading-relaxed">Always verify the sample before replacing a barcode[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3 w-3 shrink-0 text-rose-400" />
                                <span class="leading-relaxed">Use consistent storage naming for physical matching[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3 w-3 shrink-0 text-rose-400" />
                                <span class="leading-relaxed">Capture updates on time for accurate dashboards[cite: 5].</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Architecture)</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Implementation Focus -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        <LuCode2 class="h-3.5 w-3.5" />
                        Implementation Focus
                    </h4>
                    <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">The module is split into project management, experiment monitoring, and sample inventory concerns. The frontend is Inertia and Vue driven, while Laravel controllers provide data for pages and repository-backed workflows[cite: 5].</p>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                            <span class="leading-relaxed">Project-level pages define the top-level research structure[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                            <span class="leading-relaxed">Experiment pages hold workflow state, stage updates, and context[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                            <span class="leading-relaxed">Sample inventory pages handle barcode and QR retrieval flows[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Key Pages -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuLayoutTemplate class="h-3.5 w-3.5" />
                        Key User-Facing Pages
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchDashboard.vue</code>
                            <span class="ml-1 block">Research dashboard and overview[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchProjectIndex.vue</code>
                            <span class="ml-1 block">Project listing[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchProjectCreate.vue</code>
                            <span class="ml-1 block">Project creation flow[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchProjectShow.vue</code>
                            <span class="ml-1 block">Project detail and nested activity view[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchExperimentShow.vue</code>
                            <span class="ml-1 block">Experiment monitoring detail page[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">ResearchSampleInventory.vue</code>
                            <span class="ml-1 block">Barcode, QR, and retrieval inventory view[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Backend Routing -->
                <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                        <LuNetwork class="h-3.5 w-3.5" />
                        Backend Routing
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">ResearchPageController.php</code>
                            <span class="ml-1 block">Provides the primary Inertia pages for the module[cite: 5].</span>
                        </li>
                        <li>
                            <code class="mb-1 block w-fit break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">routes/research.php</code>
                            <span class="ml-1 block">Defines research-specific routes and endpoints[cite: 5].</span>
                        </li>
                        <li>
                            <div class="mt-2 flex items-start gap-2">
                                <LuAlertTriangle class="h-3.5 w-3.5 shrink-0 text-purple-400" />
                                <span class="leading-relaxed">Any new workflow should preserve the existing controller-to-service or repository flow rather than adding page logic directly into Vue[cite: 5].</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <!-- Design Notes -->
                    <div class="rounded-xl border border-pink-100 bg-pink-50/50 p-5 shadow-sm dark:border-pink-500/20 dark:bg-pink-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400">
                            <LuScanLine class="h-3.5 w-3.5" />
                            Barcode & QR Design Notes
                        </h4>
                        <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                                <span class="leading-relaxed">Barcode payloads should stay compact and stable for consistent scanning[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                                <span class="leading-relaxed">QR payloads can include richer context, but avoid storing presentation data[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                                <span class="leading-relaxed">Printing flows should use a single source of truth for sample identity[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                                <span class="leading-relaxed">Audit logs are critical for label regeneration and retrieval actions[cite: 5].</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Extending -->
                    <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-5 shadow-sm dark:border-orange-500/20 dark:bg-orange-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400">
                            <LuPlusCircle class="h-3.5 w-3.5" />
                            When Extending
                        </h4>
                        <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-orange-400"></div>
                                <span class="leading-relaxed">Keep project, experiment, and inventory responsibilities separate[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-orange-400"></div>
                                <span class="leading-relaxed">Prefer repository changes over embedding orchestration in page components[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-orange-400"></div>
                                <span class="leading-relaxed">Add tests when introducing new stages or retrieval rules[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-orange-400"></div>
                                <span class="leading-relaxed">Preserve barcode/QR compatibility when changing payloads[cite: 5].</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
