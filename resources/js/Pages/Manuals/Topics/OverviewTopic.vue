<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "OverviewTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    computed: {
        topicDescription() {
            return this.showDeveloperSections ? `${this.$appName} is a comprehensive resource management system designed to streamline operations for the center. This guide covers both administrative tasks and developer tasks.` : `${this.$appName} is a comprehensive resource management system designed to streamline operations for the center. This public guide focuses on end-user workflows and common tasks.`;
        },
    },
};
</script>

<template>
    <TopicLayout
        :title="'Welcome to ' + $appName"
        :description="topicDescription"
        icon="LuBookOpen">
        <div class="space-y-8 p-6">
            <ul class="space-y-2.5 text-sm font-medium text-slate-600 dark:text-slate-400">
                <li class="flex items-center gap-2.5">
                    <LuCheckCircle2 class="h-4 w-4 shrink-0 text-indigo-400" />
                    Manage events and participants
                </li>
                <li class="flex items-center gap-2.5">
                    <LuCheckCircle2 class="h-4 w-4 shrink-0 text-indigo-400" />
                    Handle resource requests (facilities, equipment, supplies)
                </li>
                <li class="flex items-center gap-2.5">
                    <LuCheckCircle2 class="h-4 w-4 shrink-0 text-indigo-400" />
                    Track and manage inventory
                </li>
                <li class="flex items-center gap-2.5">
                    <LuCheckCircle2 class="h-4 w-4 shrink-0 text-indigo-400" />
                    Maintain user profiles and access control
                </li>
                <li class="flex items-center gap-2.5">
                    <LuCheckCircle2 class="h-4 w-4 shrink-0 text-indigo-400" />
                    Audit system activities for compliance and security
                </li>
            </ul>
            <!-- Core Modules -->
            <div class="space-y-3">
                <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <LuBlocks class="h-3.5 w-3.5" />
                    Core Modules & Features
                </h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-4 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-sky-800 dark:text-sky-300">
                            <LuCalendarDays class="h-4 w-4" />
                            Event Management
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Create and manage events with custom registration forms. Define requirements, track participants, and generate reports. Each event can have multiple form types.</p>
                    </div>

                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                            <LuFileText class="h-4 w-4" />
                            Custom Forms
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Build flexible, reusable forms for events. Administrators can select from pre-defined types or request specialized forms. Supports dynamic validation.</p>
                    </div>

                    <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-4 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-purple-800 dark:text-purple-300">
                            <LuWrench class="h-4 w-4" />
                            FES Request System
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Facilities, Equipment, and Supplies request management. Submit requests for laboratory access and supply procurement with full tracking history.</p>
                    </div>

                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-300">
                            <LuPackage class="h-4 w-4" />
                            Inventory Management
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Complete inventory tracking including items, categories, suppliers, and transactions. Monitor stock levels and manage supplier information.</p>
                    </div>

                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-rose-800 dark:text-rose-300">
                            <LuUserCircle class="h-4 w-4" />
                            User Profiles
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Secure user account management with profile information, password management, two-factor authentication, and session control via Fortify.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-100/50 p-4 shadow-sm dark:border-slate-700/50 dark:bg-slate-800/50">
                        <h4 class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-300">
                            <LuShieldCheck class="h-4 w-4" />
                            Audit Logging
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Complete system audit trail tracking all user actions and system changes. Provides compliance documentation and security monitoring.</p>
                    </div>
                </div>
            </div>

            <!-- How to Use -->
            <div class="space-y-3">
                <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <LuMousePointerClick class="h-3.5 w-3.5" />
                    Navigation & Usage
                </h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">How to Use This Manual</p>
                        <ol class="list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <li>Select a topic from the left menu.</li>
                            <li>Look for your specific role.</li>
                            <li>Follow the step-by-step instructions.</li>
                            <li>Use on-screen tour controls on public pages.</li>
                            <li v-if="showDeveloperSections">Refer to code snippets for implementations.</li>
                            <li>Check quick references for common files.</li>
                        </ol>
                    </div>

                    <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Interactive Guides</p>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">Public-facing pages include Driver.js-powered tours. New guests are shown a startup guide after acknowledging the privacy notice, and public pages include controls to replay guides.</p>
                        <p
                            v-if="showDeveloperSections"
                            class="mt-3 rounded-lg bg-indigo-50/50 p-2.5 text-xs font-medium leading-relaxed text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            Developers should treat these tours as part of the public experience and update the guide registry when workflows change.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="flex flex-col justify-between gap-6 border-t border-slate-100 pt-5 md:flex-row dark:border-slate-800/60">
                <div>
                    <h3 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuMap class="h-3.5 w-3.5" />
                        Quick Areas
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Administrators</span>
                        <span
                            v-if="showDeveloperSections"
                            class="rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[0.65rem] font-semibold text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                            Developers
                        </span>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Getting Started</span>
                    </div>
                </div>

                <div>
                    <h3 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuLayers class="h-3.5 w-3.5" />
                        Tech Stack
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Laravel 11 / PHP</span>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Vue 3 / Inertia.js</span>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Tailwind CSS</span>
                    </div>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>

<style scoped></style>
