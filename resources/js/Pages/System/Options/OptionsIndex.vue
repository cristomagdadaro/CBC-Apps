<script>
import TabNavigation from "@/Components/TabNavigation.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Options from "@/Modules/domain/Options";
import { LayoutGrid, Save, Loader2, CheckCircle2, XCircle, ShieldCheck, Server, Globe } from "lucide-vue-next";

export default {
    name: "OptionsIndex",
    components: {
        TabNavigation,
        LayoutGrid,
        Save,
        Loader2,
        CheckCircle2,
        XCircle,
        ShieldCheck,
        Server,
        Globe,
    },
    mixins: [ApiMixin],
    data() {
        return {
            activeTab: "controls",
            optionsModel: Options,
            workflowToggles: {
                event_workflow_enabled: true,
                participant_workflow_enabled: true,
                participant_verification_enabled: true,
            },
            workflowToggleLoading: false,
            workflowToggleSaving: false,
            deploymentAccessValues: {},
            deploymentAccessSections: [],
            deploymentAccessLoading: false,
            deploymentAccessSaving: false,
        };
    },
    mounted() {
        this.loadInitialState();
    },
    computed: {
        deploymentAccessMeta() {
            return this.$page.props.deployment_access || {};
        },
        optionTabs() {
            return [
                {
                    key: "controls",
                    label: "Controls",
                    icon: "LuSettings2",
                },
                {
                    key: "options",
                    label: "All Options",
                    icon: "LuTableProperties",
                },
            ];
        },
    },
    methods: {
        extractApiPayload(response, fallback = {}) {
            const payload = response?.data?.data ?? response?.data ?? response;
            return payload && typeof payload === "object" ? payload : fallback;
        },
        syncWorkflowToggles(data = {}) {
            this.workflowToggles = {
                event_workflow_enabled: data.event_workflow_enabled !== false,
                participant_workflow_enabled: data.participant_workflow_enabled !== false,
                participant_verification_enabled: data.participant_verification_enabled !== false,
            };
        },
        async loadInitialState() {
            await Promise.allSettled([this.loadWorkflowToggles(), this.loadDeploymentAccess()]);
        },
        isModuleProtected(item) {
            return item?.allows_deactivation === false;
        },
        moduleMode(item) {
            return this.deploymentAccessValues[item?.module]?.mode || item?.mode || "active";
        },
        moduleStatusBadge(item) {
            const mode = this.moduleMode(item);

            if (mode === "maintenance") {
                return {
                    label: "Maintenance",
                    className: "bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30",
                };
            }

            if (mode === "deactivated") {
                return {
                    label: "Deactivated",
                    className: "bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30",
                };
            }

            return {
                label: "Active",
                className: "bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30",
            };
        },
        moduleStatusBanner(item) {
            const mode = this.moduleMode(item);

            if (mode === "maintenance") {
                return {
                    className: "border-amber-200 bg-amber-50/80 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300",
                    text: "Maintenance mode keeps this module visible but blocks create, update, and delete requests.",
                };
            }

            if (mode === "deactivated") {
                return {
                    className: "border-rose-200 bg-rose-50/80 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300",
                    text: "This module is fully unavailable and is hidden within the system.",
                };
            }

            return null;
        },
        applyDeploymentAccessPayload(payload = {}) {
            const sections = Array.isArray(payload.sections) ? payload.sections : [];
            const modules = payload.modules || {};

            this.deploymentAccessSections = sections;
            this.deploymentAccessValues = Object.fromEntries(
                Object.entries(modules).map(([module, settings]) => [
                    module,
                    {
                        access: settings?.access || "both",
                        mode: settings?.mode || "active",
                    },
                ]),
            );
        },
        async loadWorkflowToggles() {
            this.workflowToggleLoading = true;
            try {
                const response = await this.fetchGetApi("api.options.workflow-toggles");
                this.syncWorkflowToggles(this.extractApiPayload(response));
            } catch (error) {
                // ApiService handles error notification
            } finally {
                this.workflowToggleLoading = false;
            }
        },
        async saveWorkflowToggles() {
            this.workflowToggleSaving = true;
            try {
                const payload = {
                    event_workflow_enabled: !!this.workflowToggles.event_workflow_enabled,
                    participant_workflow_enabled: !!this.workflowToggles.participant_workflow_enabled,
                    participant_verification_enabled: !!this.workflowToggles.participant_verification_enabled,
                };

                const response = await this.fetchPutApi("api.options.workflow-toggles.update", null, payload);

                this.syncWorkflowToggles(this.extractApiPayload(response, payload));
            } catch (error) {
                // ApiService handles error notification
            } finally {
                this.workflowToggleSaving = false;
            }
        },
        async loadDeploymentAccess() {
            this.deploymentAccessLoading = true;
            try {
                const response = await this.fetchGetApi("api.options.deployment-access");
                this.applyDeploymentAccessPayload(this.extractApiPayload(response));
            } catch (error) {
                // ApiService handles error notification
            } finally {
                this.deploymentAccessLoading = false;
            }
        },
        async saveDeploymentAccess() {
            this.deploymentAccessSaving = true;
            try {
                const response = await this.fetchPutApi("api.options.deployment-access.update", null, {
                    modules: this.deploymentAccessValues,
                });

                this.applyDeploymentAccessPayload(this.extractApiPayload(response));
            } catch (error) {
                // ApiService handles error notification
            } finally {
                this.deploymentAccessSaving = false;
            }
        },
    },
};
</script>

<template>
    <app-layout title="Options Module">
        <template #header>
            <ActionHeaderLayout
                title="System Options"
                subtitle="Configure system settings, manage module access, and toggle core workflows from the controls tab. The options tab provides a comprehensive view of all configurable options across modules."
                route-link="system.options.index">
                <Link :href="route('system.options.create')">
                    <add-icon class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200" />
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="default-container pt-5">
            <section class="transition-colors">
                <TabNavigation
                    v-model="activeTab"
                    :tabs="optionTabs">
                    <template #default="{ activeKey }">
                        <!-- CONTROLS TAB -->
                        <div
                            v-if="activeKey === 'controls'"
                            class="space-y-6 pt-4">
                            <!-- Form Workflow Toggles -->
                            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 p-5 md:p-7">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6 pb-5 border-b border-slate-100 dark:border-slate-800/60">
                                    <div class="flex items-start gap-3.5">
                                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm shrink-0">
                                            <LayoutGrid class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-900 dark:text-white tracking-tight">Form Workflow Toggles</h3>
                                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Enable or disable event, participant, and verification workflows across the system.</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="saveWorkflowToggles"
                                        :disabled="workflowToggleLoading || workflowToggleSaving"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 text-sm shadow-sm transition-all active:scale-95 disabled:opacity-70 disabled:pointer-events-none shrink-0">
                                        <Loader2
                                            v-if="workflowToggleSaving"
                                            class="w-4 h-4 animate-spin" />
                                        <Save
                                            v-else
                                            class="w-4 h-4" />
                                        <span>
                                            {{ workflowToggleSaving ? "Saving..." : "Save Changes" }}
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-if="workflowToggleLoading"
                                    class="flex flex-col items-center justify-center py-10 text-slate-500 dark:text-slate-400">
                                    <Loader2 class="w-6 h-6 animate-spin mb-3 text-indigo-500" />
                                    <span class="text-sm font-medium">Loading workflow settings...</span>
                                </div>

                                <div
                                    v-else
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Event Workflow -->
                                    <label
                                        class="group relative flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-300 cursor-pointer shadow-sm"
                                        :class="workflowToggles.event_workflow_enabled ? 'bg-indigo-50/50 border-indigo-500 dark:bg-indigo-500/10 dark:border-indigo-500' : 'bg-slate-50/50 border-slate-200/60 hover:border-indigo-300 dark:bg-slate-800/30 dark:border-slate-700/60 dark:hover:border-indigo-600'">
                                        <div class="flex items-center gap-3.5">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                                                :class="workflowToggles.event_workflow_enabled ? 'bg-white dark:bg-slate-800 border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500'">
                                                <CheckCircle2
                                                    v-if="workflowToggles.event_workflow_enabled"
                                                    class="w-5 h-5" />
                                                <XCircle
                                                    v-else
                                                    class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900 dark:text-white tracking-tight">Event Workflow</span>
                                                <span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-0.5 block">Manage Processing</span>
                                            </div>
                                        </div>
                                        <input
                                            v-model="workflowToggles.event_workflow_enabled"
                                            type="checkbox"
                                            class="w-5 h-5 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer transition-colors" />
                                    </label>

                                    <!-- Participant Workflow -->
                                    <label
                                        class="group relative flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-300 cursor-pointer shadow-sm"
                                        :class="workflowToggles.participant_workflow_enabled ? 'bg-indigo-50/50 border-indigo-500 dark:bg-indigo-500/10 dark:border-indigo-500' : 'bg-slate-50/50 border-slate-200/60 hover:border-indigo-300 dark:bg-slate-800/30 dark:border-slate-700/60 dark:hover:border-indigo-600'">
                                        <div class="flex items-center gap-3.5">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                                                :class="workflowToggles.participant_workflow_enabled ? 'bg-white dark:bg-slate-800 border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500'">
                                                <CheckCircle2
                                                    v-if="workflowToggles.participant_workflow_enabled"
                                                    class="w-5 h-5" />
                                                <XCircle
                                                    v-else
                                                    class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900 dark:text-white tracking-tight">Participant Workflow</span>
                                                <span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-0.5 block">Handle Logic</span>
                                            </div>
                                        </div>
                                        <input
                                            v-model="workflowToggles.participant_workflow_enabled"
                                            type="checkbox"
                                            class="w-5 h-5 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer transition-colors" />
                                    </label>

                                    <!-- Verification Workflow -->
                                    <label
                                        class="group relative flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-300 cursor-pointer shadow-sm"
                                        :class="workflowToggles.participant_verification_enabled ? 'bg-indigo-50/50 border-indigo-500 dark:bg-indigo-500/10 dark:border-indigo-500' : 'bg-slate-50/50 border-slate-200/60 hover:border-indigo-300 dark:bg-slate-800/30 dark:border-slate-700/60 dark:hover:border-indigo-600'">
                                        <div class="flex items-center gap-3.5">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                                                :class="workflowToggles.participant_verification_enabled ? 'bg-white dark:bg-slate-800 border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500'">
                                                <CheckCircle2
                                                    v-if="workflowToggles.participant_verification_enabled"
                                                    class="w-5 h-5" />
                                                <XCircle
                                                    v-else
                                                    class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900 dark:text-white tracking-tight">Verification</span>
                                                <span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-0.5 block">Enable Steps</span>
                                            </div>
                                        </div>
                                        <input
                                            v-model="workflowToggles.participant_verification_enabled"
                                            type="checkbox"
                                            class="w-5 h-5 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer transition-colors" />
                                    </label>
                                </div>
                            </div>

                            <!-- Module Access Controls -->
                            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 p-5 md:p-7">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6 pb-5 border-b border-slate-100 dark:border-slate-800/60">
                                    <div class="flex items-start gap-3.5">
                                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl shadow-sm shrink-0">
                                            <ShieldCheck class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-900 dark:text-white tracking-tight">Module Access Controls</h3>
                                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed max-w-xl">Configure deployment visibility and runtime mode for each module. Deployment access applies to the module's web pages and APIs together.</p>
                                            <div class="mt-3 flex flex-wrap gap-2 text-[0.65rem] font-mono font-semibold">
                                                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 flex items-center gap-1.5 shadow-sm">
                                                    <Server class="w-3 h-3" />
                                                    Local: {{ deploymentAccessMeta.local_url }}
                                                </span>
                                                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 flex items-center gap-1.5 shadow-sm">
                                                    <Globe class="w-3 h-3" />
                                                    Internet:
                                                    {{ deploymentAccessMeta.internet_url }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="saveDeploymentAccess"
                                        :disabled="deploymentAccessLoading || deploymentAccessSaving"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 text-sm shadow-sm transition-all active:scale-95 disabled:opacity-70 disabled:pointer-events-none shrink-0">
                                        <Loader2
                                            v-if="deploymentAccessSaving"
                                            class="w-4 h-4 animate-spin" />
                                        <Save
                                            v-else
                                            class="w-4 h-4" />
                                        <span>
                                            {{ deploymentAccessSaving ? "Saving..." : "Save Module Rules" }}
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-if="deploymentAccessLoading"
                                    class="flex flex-col items-center justify-center py-10 text-slate-500 dark:text-slate-400">
                                    <Loader2 class="w-6 h-6 animate-spin mb-3 text-emerald-500" />
                                    <span class="text-sm font-medium">Loading module controls...</span>
                                </div>

                                <div
                                    v-else
                                    class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <section
                                        v-for="section in deploymentAccessSections"
                                        :key="section.key"
                                        class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm">
                                        <div class="mb-4">
                                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                                {{ section.label }}
                                            </h4>
                                        </div>

                                        <div class="space-y-4">
                                            <div
                                                v-for="item in section.items"
                                                :key="item.module"
                                                class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/50 p-5 shadow-sm hover:border-emerald-300 dark:hover:border-emerald-600 transition-colors">
                                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                                                    <!-- Module Header -->
                                                    <div class="flex-1">
                                                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                            <h5 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                                                {{ item.label }}
                                                            </h5>
                                                            <span
                                                                class="inline-flex items-center rounded-md px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest shadow-sm"
                                                                :class="moduleStatusBadge(item).className">
                                                                {{ moduleStatusBadge(item).label }}
                                                            </span>
                                                            <span
                                                                v-if="isModuleProtected(item)"
                                                                class="inline-flex items-center rounded-md bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30 shadow-sm">
                                                                Safeguarded
                                                            </span>
                                                        </div>
                                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                                            {{ item.description }}
                                                        </p>
                                                    </div>

                                                    <!-- Selects -->
                                                    <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-3 w-full lg:w-auto shrink-0">
                                                        <div class="w-full sm:w-40 lg:w-full xl:w-40">
                                                            <label
                                                                :for="`${item.module}-access`"
                                                                class="mb-1.5 block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                                                Deployment Access
                                                            </label>
                                                            <select
                                                                :id="`${item.module}-access`"
                                                                v-model="deploymentAccessValues[item.module].access"
                                                                class="block w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors shadow-sm appearance-none">
                                                                <option
                                                                    v-for="option in item.access_options"
                                                                    :key="`${item.module}-access-${option.value}`"
                                                                    :value="option.value">
                                                                    {{ option.label }}
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="w-full sm:w-40 lg:w-full xl:w-40">
                                                            <label
                                                                :for="`${item.module}-mode`"
                                                                class="mb-1.5 block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                                                Module Mode
                                                            </label>
                                                            <select
                                                                :id="`${item.module}-mode`"
                                                                v-model="deploymentAccessValues[item.module].mode"
                                                                class="block w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors shadow-sm appearance-none">
                                                                <option
                                                                    v-for="option in item.mode_options"
                                                                    :key="`${item.module}-mode-${option.value}`"
                                                                    :value="option.value"
                                                                    :disabled="isModuleProtected(item) && option.value === 'deactivated'">
                                                                    {{ isModuleProtected(item) && option.value === "deactivated" ? `${option.label} (locked)` : option.label }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Banners -->
                                                <div class="mt-4 space-y-2">
                                                    <p
                                                        v-if="isModuleProtected(item)"
                                                        class="text-[0.65rem] font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-500/30 rounded-lg p-2.5 leading-relaxed">
                                                        This module cannot be deactivated from the admin UI to avoid locking out system settings.
                                                    </p>

                                                    <div
                                                        v-if="moduleStatusBanner(item)"
                                                        class="rounded-lg border p-2.5 text-[0.65rem] font-semibold leading-relaxed"
                                                        :class="moduleStatusBanner(item).className">
                                                        {{ moduleStatusBanner(item).text }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>

                        <!-- OPTIONS TAB -->
                        <div
                            v-else
                            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 p-3 mt-4 overflow-hidden">
                            <CRCMDatatable
                                :base-model="optionsModel"
                                :can-view="true"
                                :can-create="true"
                                :can-update="true"
                                :can-delete="false" />
                        </div>
                    </template>
                </TabNavigation>
            </section>
        </div>
    </app-layout>
</template>
