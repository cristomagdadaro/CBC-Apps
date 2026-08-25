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
        <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-4 shadow-sm flex items-start gap-3">
            <LuShieldCheck class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" />
            <p class="text-xs font-medium text-indigo-900 dark:text-indigo-200 leading-relaxed">The module combines research metadata, barcode-based inventory handling, QR-based retrieval context, and audit visibility so staff can trace what was created, where it is stored, and what monitoring stages have already been completed[cite: 5].</p>
        </div>

        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Researchers & Staff)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- What you can do -->
                <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                        <LuListChecks class="w-3.5 h-3.5" />
                        What you can do
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <span class="leading-relaxed">Create and maintain research project profiles with funding, duration, and leadership details[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <span class="leading-relaxed">Add studies and experiments under a single project for organized monitoring[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <span class="leading-relaxed">Register samples and assign unique barcode identifiers for storage and movement tracking[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <span class="leading-relaxed">Record monitoring updates across research stages[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-sky-500 shrink-0 mt-0.5" />
                            <span class="leading-relaxed">Scan, retrieve, and print labels from the sample inventory view[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Recommended Workflow -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                        <LuRoute class="w-3.5 h-3.5" />
                        Recommended Workflow
                    </h4>
                    <ol class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
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
                <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                        <LuQrCode class="w-3.5 h-3.5" />
                        Barcode and QR labels
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Barcode</span>
                                is best for quick scanning during audits, shelf checks, hand-offs, and movement[cite: 5].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></div>
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">QR code</span>
                                is best when the scanner or viewer needs more context such as retrieval details or display-ready sample data[cite: 5].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuAlertTriangle class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed text-amber-700 dark:text-amber-400/90">Label printing and scan-based lookups should be treated as part of the sample history, so staff should avoid reusing labels across different samples[cite: 5].</p>
                        </li>
                    </ul>
                </div>

                <!-- Access & Practices -->
                <div class="space-y-6">
                    <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-1.5">
                            <LuMapPin class="w-3.5 h-3.5" />
                            Main Access Points
                        </h4>
                        <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-center gap-2.5">
                                <div class="w-1 h-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Dashboard</span>
                                for overview cards[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="w-1 h-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Projects</span>
                                for main profiles[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="w-1 h-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Experiments</span>
                                for status work[cite: 5].
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="w-1 h-1 rounded-full bg-purple-400"></div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Research Sample Inventory</span>
                                for scanning[cite: 5].
                            </li>
                        </ul>
                    </div>

                    <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-3 flex items-center gap-1.5">
                            <LuShieldCheck class="w-3.5 h-3.5" />
                            Good Operating Practices
                        </h4>
                        <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3 h-3 text-rose-400 shrink-0 mt-0.5" />
                                <span class="leading-relaxed">Finalize titles before printing large batches of labels[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3 h-3 text-rose-400 shrink-0 mt-0.5" />
                                <span class="leading-relaxed">Always verify the sample before replacing a barcode[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3 h-3 text-rose-400 shrink-0 mt-0.5" />
                                <span class="leading-relaxed">Use consistent storage naming for physical matching[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="w-3 h-3 text-rose-400 shrink-0 mt-0.5" />
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
            class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Architecture)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Implementation Focus -->
                <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                        <LuCode2 class="w-3.5 h-3.5" />
                        Implementation Focus
                    </h4>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mb-3 leading-relaxed">The module is split into project management, experiment monitoring, and sample inventory concerns. The frontend is Inertia and Vue driven, while Laravel controllers provide data for pages and repository-backed workflows[cite: 5].</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2">
                            <div class="w-1 h-1 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <span class="leading-relaxed">Project-level pages define the top-level research structure[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="w-1 h-1 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <span class="leading-relaxed">Experiment pages hold workflow state, stage updates, and context[cite: 5].</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="w-1 h-1 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <span class="leading-relaxed">Sample inventory pages handle barcode and QR retrieval flows[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Key Pages -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-1.5">
                        <LuLayoutTemplate class="w-3.5 h-3.5" />
                        Key User-Facing Pages
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchDashboard.vue</code>
                            <span class="block ml-1">Research dashboard and overview[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchProjectIndex.vue</code>
                            <span class="block ml-1">Project listing[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchProjectCreate.vue</code>
                            <span class="block ml-1">Project creation flow[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchProjectShow.vue</code>
                            <span class="block ml-1">Project detail and nested activity view[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchExperimentShow.vue</code>
                            <span class="block ml-1">Experiment monitoring detail page[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">ResearchSampleInventory.vue</code>
                            <span class="block ml-1">Barcode, QR, and retrieval inventory view[cite: 5].</span>
                        </li>
                    </ul>
                </div>

                <!-- Backend Routing -->
                <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-1.5">
                        <LuNetwork class="w-3.5 h-3.5" />
                        Backend Routing
                    </h4>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-purple-600 dark:text-purple-400 break-all">ResearchPageController.php</code>
                            <span class="block ml-1">Provides the primary Inertia pages for the module[cite: 5].</span>
                        </li>
                        <li>
                            <code class="block w-fit px-1.5 py-0.5 mb-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-purple-600 dark:text-purple-400 break-all">routes/research.php</code>
                            <span class="block ml-1">Defines research-specific routes and endpoints[cite: 5].</span>
                        </li>
                        <li>
                            <div class="flex items-start gap-2 mt-2">
                                <LuAlertTriangle class="w-3.5 h-3.5 text-purple-400 shrink-0" />
                                <span class="leading-relaxed">Any new workflow should preserve the existing controller-to-service or repository flow rather than adding page logic directly into Vue[cite: 5].</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <!-- Design Notes -->
                    <div class="bg-pink-50/50 dark:bg-pink-500/5 border border-pink-100 dark:border-pink-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400 mb-3 flex items-center gap-1.5">
                            <LuScanLine class="w-3.5 h-3.5" />
                            Barcode & QR Design Notes
                        </h4>
                        <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div>
                                <span class="leading-relaxed">Barcode payloads should stay compact and stable for consistent scanning[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div>
                                <span class="leading-relaxed">QR payloads can include richer context, but avoid storing presentation data[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div>
                                <span class="leading-relaxed">Printing flows should use a single source of truth for sample identity[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div>
                                <span class="leading-relaxed">Audit logs are critical for label regeneration and retrieval actions[cite: 5].</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Extending -->
                    <div class="bg-orange-50/50 dark:bg-orange-500/5 border border-orange-100 dark:border-orange-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400 mb-3 flex items-center gap-1.5">
                            <LuPlusCircle class="w-3.5 h-3.5" />
                            When Extending
                        </h4>
                        <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-orange-400 mt-1.5 shrink-0"></div>
                                <span class="leading-relaxed">Keep project, experiment, and inventory responsibilities separate[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-orange-400 mt-1.5 shrink-0"></div>
                                <span class="leading-relaxed">Prefer repository changes over embedding orchestration in page components[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-orange-400 mt-1.5 shrink-0"></div>
                                <span class="leading-relaxed">Add tests when introducing new stages or retrieval rules[cite: 5].</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-orange-400 mt-1.5 shrink-0"></div>
                                <span class="leading-relaxed">Preserve barcode/QR compatibility when changing payloads[cite: 5].</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
