<script>
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'OverviewTopic',
    components: {
        TopicLayout
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    computed: {
        topicDescription() {
            return this.showDeveloperSections 
                ? `${this.$appName} is a comprehensive resource management system designed to streamline operations for the center. This guide covers both administrative tasks and developer tasks.`
                : `${this.$appName} is a comprehensive resource management system designed to streamline operations for the center. This public guide focuses on end-user workflows and common tasks.`;
        }
    }
}
</script>

<template>
    <TopicLayout
        :title="'Welcome to ' + $appName"
        :description="topicDescription"
        icon="LuBookOpen"
    >
        <div class="p-6 space-y-8">
            
                        <ul class="space-y-2.5 text-sm font-medium text-slate-600 dark:text-slate-400">
                            <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" /> Manage events and participants</li>
                            <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" /> Handle resource requests (facilities, equipment, supplies)</li>
                            <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" /> Track and manage inventory</li>
                            <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" /> Maintain user profiles and access control</li>
                            <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" /> Audit system activities for compliance and security</li>
                        </ul>
                <!-- Core Modules -->
                <div class="space-y-3">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                        <LuBlocks class="w-3.5 h-3.5" /> Core Modules & Features
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-sky-800 dark:text-sky-300 mb-1.5 flex items-center gap-2">
                                <LuCalendarDays class="w-4 h-4" /> Event Management
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Create and manage events with custom registration forms. Define requirements, track participants, and generate reports. Each event can have multiple form types.</p>
                        </div>

                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 mb-1.5 flex items-center gap-2">
                                <LuFileText class="w-4 h-4" /> Custom Forms
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Build flexible, reusable forms for events. Administrators can select from pre-defined types or request specialized forms. Supports dynamic validation.</p>
                        </div>

                        <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-1.5 flex items-center gap-2">
                                <LuWrench class="w-4 h-4" /> FES Request System
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Facilities, Equipment, and Supplies request management. Submit requests for laboratory access and supply procurement with full tracking history.</p>
                        </div>

                        <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1.5 flex items-center gap-2">
                                <LuPackage class="w-4 h-4" /> Inventory Management
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Complete inventory tracking including items, categories, suppliers, and transactions. Monitor stock levels and manage supplier information.</p>
                        </div>

                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-rose-800 dark:text-rose-300 mb-1.5 flex items-center gap-2">
                                <LuUserCircle class="w-4 h-4" /> User Profiles
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Secure user account management with profile information, password management, two-factor authentication, and session control via Fortify.</p>
                        </div>

                        <div class="bg-slate-100/50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 rounded-xl p-4 shadow-sm">
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-300 mb-1.5 flex items-center gap-2">
                                <LuShieldCheck class="w-4 h-4" /> Audit Logging
                            </h4>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">Complete system audit trail tracking all user actions and system changes. Provides compliance documentation and security monitoring.</p>
                        </div>
                    </div>
                </div>

                <!-- How to Use -->
                <div class="space-y-3">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                        <LuMousePointerClick class="w-3.5 h-3.5" /> Navigation & Usage
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">How to Use This Manual</p>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400 list-decimal list-inside">
                                <li>Select a topic from the left menu.</li>
                                <li>Look for your specific role.</li>
                                <li>Follow the step-by-step instructions.</li>
                                <li>Use on-screen tour controls on public pages.</li>
                                <li v-if="showDeveloperSections">Refer to code snippets for implementations.</li>
                                <li>Check quick references for common files.</li>
                            </ol>
                        </div>
                        
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Interactive Guides</p>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                                Public-facing pages include Driver.js-powered tours. New guests are shown a startup guide after acknowledging the privacy notice, and public pages include controls to replay guides.
                            </p>
                            <p v-if="showDeveloperSections" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 leading-relaxed mt-3 p-2.5 bg-indigo-50/50 dark:bg-indigo-500/10 rounded-lg">
                                Developers should treat these tours as part of the public experience and update the guide registry when workflows change.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="pt-5 border-t border-slate-100 dark:border-slate-800/60 flex flex-col md:flex-row gap-6 justify-between">
                    <div>
                        <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-1.5">
                            <LuMap class="w-3.5 h-3.5" /> Quick Areas
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.65rem] font-semibold border border-slate-200 dark:border-slate-700">Administrators</span>
                            <span v-if="showDeveloperSections" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-[0.65rem] font-semibold border border-indigo-200 dark:border-indigo-500/30">Developers</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.65rem] font-semibold border border-slate-200 dark:border-slate-700">Getting Started</span>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-1.5">
                            <LuLayers class="w-3.5 h-3.5" /> Tech Stack
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.65rem] font-semibold border border-slate-200 dark:border-slate-700">Laravel 11 / PHP</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.65rem] font-semibold border border-slate-200 dark:border-slate-700">Vue 3 / Inertia.js</span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.65rem] font-semibold border border-slate-200 dark:border-slate-700">Tailwind CSS</span>
                        </div>
                    </div>
                </div>
                </div>
    </TopicLayout>
</template>

<style scoped>
</style>