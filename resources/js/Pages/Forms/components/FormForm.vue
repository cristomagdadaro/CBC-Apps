<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import FormStyleDesigner from "@/Pages/Forms/components/FormStyleDesigner.vue";
import SuspendFormBtn from "./SuspendFormBtn.vue";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";

export default {
    name: "FormCreate",
    components: {
        FormStyleDesigner,
        RequirementsManager,
        ListOfForms,
        SuspendFormBtn,
        GuestCard,
    },
    mixins: [ApiMixin],
    data() {
        return {
            isEdit: !!this.data,
            activeSection: "details", // 'details', 'requirements', 'style', 'preview'
            isSaving: false,
            showMobilePreview: false,
            windowWidth: typeof window !== "undefined" ? window.innerWidth : 1024,
        };
    },
    beforeMount() {
        this.model = new Form();
        if (this.data) {
            this.setFormAction("update");
            this.setRequirements();
        } else {
            this.setFormAction("create");
            if (!this.form.requirements) {
                this.form.requirements = [];
            }
        }
    },
    computed: {
        styleTokensError() {
            if (!this.form?.errors) return null;
            const entry = Object.entries(this.form.errors).find(([key]) => key.startsWith("style_tokens"));
            return entry ? entry[1] : null;
        },
        formUrl() {
            return this.data ? route("forms.guest.index", this.form.event_id) : null;
        },
        canPreview() {
            return !!this.form.title && !!this.form.date_from;
        },
        isMobile() {
            return this.windowWidth < 1024;
        },
    },
    methods: {
        applyServerForm(serverForm) {
            if (!serverForm || !this.form) return;

            Object.entries(serverForm).forEach(([key, value]) => {
                this.form[key] = value;
            });
        },
        async syncFormFromServer() {
            if (!this.form?.event_id) return;

            const response = await this.fetchGetApi("api.form.show", {
                routeParams: this.form.event_id,
            });

            const serverForm = response?.data ?? response ?? null;
            this.applyServerForm(serverForm);
            this.setRequirements();
        },
        async submitProxy() {
            this.isSaving = true;
            this.form.requirements = this.form.requirements || [];
            try {
                if (this.isEdit) {
                    await this.submitUpdate();
                    await this.syncFormFromServer();
                } else {
                    await this.submitCreate();
                    await this.syncFormFromServer();
                }
            } finally {
                this.isSaving = false;
            }
        },
        handleResize() {
            if (typeof window === "undefined") return;
            this.windowWidth = window.innerWidth;
        },
        setRequirements() {
            if (!this.form.requirements) {
                this.form.requirements = this.$page.props?.data?.requirements || [];
            }
        },
        copyFormLink() {
            if (this.formUrl) {
                navigator.clipboard.writeText(this.formUrl);
                // Could add toast notification here
            }
        },
    },
    mounted() {
        if (typeof window !== "undefined") {
            window.addEventListener("resize", this.handleResize);
        }
    },
    beforeUnmount() {
        if (typeof window !== "undefined") {
            window.removeEventListener("resize", this.handleResize);
        }
    },
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200 rounded-xl overflow-hidden">
        <!-- Mobile Navigation Floating Header -->
        <div class="lg:hidden sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800 shadow-sm px-4 py-3">
            <!-- Mobile Header Top Row (Title & Save) -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1 min-w-0 pr-4">
                    <h1 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                        {{ isEdit ? "Edit: " + (form.title || "Form") : "Create New Form" }}
                    </h1>
                    <div class="text-[0.65rem] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wider">
                        {{ form.event_id ? `ID: #${form.event_id}` : "Draft" }}
                    </div>
                </div>
                <button
                    @click="submitProxy"
                    :disabled="processing || isSaving"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-sm shadow-indigo-600/20 active:scale-95 transition-transform">
                    <LuLoader2
                        v-if="processing || isSaving"
                        class="w-3.5 h-3.5 animate-spin" />
                    <LuSave
                        v-else
                        class="w-3.5 h-3.5" />
                    Save
                </button>
            </div>

            <!-- Mobile Navigation Tabs -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 -mx-1 px-1">
                <button
                    @click="activeSection = 'details'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                    :class="activeSection === 'details' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                    <LuFileText class="w-4 h-4" />
                    Details
                </button>
                <button
                    @click="activeSection = 'requirements'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                    :class="activeSection === 'requirements' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                    <LuListChecks class="w-4 h-4" />
                    Attached Forms
                </button>
                <button
                    @click="activeSection = 'style'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                    :class="activeSection === 'style' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                    <LuPalette class="w-4 h-4" />
                    Theme
                </button>
                <button
                    @click="showMobilePreview = true"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    <LuEye class="w-4 h-4" />
                    Preview
                </button>
            </div>
        </div>

        <!-- Desktop Header Frame -->
        <div class="hidden lg:block bg-white/50 dark:bg-slate-900/50 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
            <div class="mx-auto px-4 lg:px-8">
                <div class="flex items-center justify-between h-[4.5rem]">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-indigo-100 dark:bg-indigo-500/20 rounded-xl border border-indigo-200 dark:border-indigo-500/30 shadow-sm shrink-0">
                            <LuFileEdit class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h1 class="text-normal font-semibold text-slate-900 dark:text-white tracking-tight leading-none">
                                {{ isEdit ? "Edit Event Form" : "Create New Form" }}
                            </h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a
                            v-if="formUrl"
                            :href="formUrl"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all active:scale-95">
                            <LuExternalLink class="w-4 h-4 text-slate-400" />
                            Open Form
                        </a>
                        <button
                            @click="copyFormLink"
                            v-if="formUrl"
                            class="p-2.5 text-slate-500 hover:text-indigo-600 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm transition-all"
                            title="Copy link">
                            <LuCopy class="w-4 h-4" />
                        </button>

                        <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 mx-2" />

                        <suspend-form-btn
                            v-if="isEdit"
                            :data="form"
                            @updated="syncFormFromServer" />

                        <button
                            @click="submitProxy"
                            :disabled="processing || isSaving"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                            <LuLoader2
                                v-if="processing || isSaving"
                                class="w-4 h-4 animate-spin" />
                            <LuSave
                                v-else
                                class="w-4 h-4" />
                            {{ processing || isSaving ? "Saving..." : "Save Form" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8">
                <!-- Left Sidebar Navigation (Desktop) -->
                <div class="hidden lg:block lg:col-span-3 xl:col-span-2 space-y-6">
                    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-white/5 overflow-hidden">
                        <nav class="flex flex-col p-2 space-y-1">
                            <button
                                @click="activeSection = 'details'"
                                class="flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all font-semibold text-sm"
                                :class="activeSection === 'details' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60'">
                                <LuFileText
                                    class="w-5 h-5"
                                    :class="activeSection === 'details' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" />
                                Event Details
                            </button>
                            <button
                                @click="activeSection = 'requirements'"
                                class="flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all font-semibold text-sm"
                                :class="activeSection === 'requirements' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60'">
                                <LuListChecks
                                    class="w-5 h-5"
                                    :class="activeSection === 'requirements' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" />
                                Attached Forms
                            </button>
                            <button
                                @click="activeSection = 'style'"
                                class="flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all font-semibold text-sm"
                                :class="activeSection === 'style' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60'">
                                <LuPalette
                                    class="w-5 h-5"
                                    :class="activeSection === 'style' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'" />
                                Theme & Style
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Form Editor Area -->
                <div class="lg:col-span-3 xl:col-span-4 space-y-6">
                    <form
                        v-if="!!form"
                        @submit.prevent="submitProxy"
                        class="space-y-6">
                        <!-- Event Details Section -->
                        <div
                            v-show="activeSection === 'details'"
                            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-white/5 overflow-hidden transition-all duration-300">
                            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                                <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2.5">
                                    <LuFileText class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    Event Details
                                </h2>
                            </div>

                            <div class="p-6 space-y-6">
                                <!-- Title & ID -->
                                <div class="flex flex-col sm:flex-row gap-5">
                                    <div class="flex-1">
                                        <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Form Title</label>
                                        <text-input
                                            placeholder="Enter form title"
                                            v-model="form.title"
                                            :error="form.errors.title"
                                            class="w-full text-base font-medium" />
                                    </div>
                                    <div class="w-full sm:w-28 flex-shrink-0">
                                        <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Event ID</label>
                                        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-center font-black tracking-widest text-slate-700 dark:text-slate-300 shadow-inner">
                                            {{ form.event_id || "—" }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Description</label>
                                    <text-area
                                        placeholder="Describe your event or form purpose"
                                        v-model="form.description"
                                        :error="form.errors.description"
                                        class="w-full text-sm font-medium"
                                        :rows="3" />
                                </div>

                                <!-- Date & Time Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/50 dark:bg-slate-800/30 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                                <LuCalendar class="w-3.5 h-3.5" />
                                                Start Date
                                            </label>
                                            <date-input
                                                v-model="form.date_from"
                                                :error="form.errors.date_from"
                                                class="w-full" />
                                        </div>
                                        <div>
                                            <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                                <LuClock class="w-3.5 h-3.5" />
                                                Start Time
                                            </label>
                                            <time-input
                                                v-model="form.time_from"
                                                :error="form.errors.time_from"
                                                class="w-full" />
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                                <LuCalendar class="w-3.5 h-3.5" />
                                                End Date
                                            </label>
                                            <date-input
                                                v-model="form.date_to"
                                                :error="form.errors.date_to"
                                                class="w-full" />
                                        </div>
                                        <div>
                                            <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                                <LuClock class="w-3.5 h-3.5" />
                                                End Time
                                            </label>
                                            <time-input
                                                v-model="form.time_to"
                                                :error="form.errors.time_to"
                                                class="w-full" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Venue -->
                                <div>
                                    <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                        <LuMapPin class="w-3.5 h-3.5" />
                                        Venue
                                    </label>
                                    <text-input
                                        placeholder="Event location or online link"
                                        v-model="form.venue"
                                        :error="form.errors.venue"
                                        class="w-full text-sm font-medium" />
                                </div>

                                <!-- Details -->
                                <div>
                                    <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Additional Details</label>
                                    <text-area
                                        placeholder="Any other important information for participants"
                                        v-model="form.details"
                                        :error="form.errors.details"
                                        class="w-full text-sm font-medium"
                                        :rows="4" />
                                </div>
                            </div>
                        </div>

                        <!-- Requirements Section -->
                        <div
                            v-show="activeSection === 'requirements'"
                            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-white/5 overflow-hidden transition-all duration-300">
                            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                                <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2.5">
                                    <LuListChecks class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    Attached Forms
                                </h2>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1.5 ml-7.5">Attach other subforms that must be completed within this event.</p>
                            </div>
                            <div class="p-6">
                                <requirements-manager
                                    v-model="form.requirements"
                                    :error="form.errors.requirements" />
                            </div>
                        </div>

                        <!-- Style Section -->
                        <div
                            v-show="activeSection === 'style'"
                            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm ring-1 ring-slate-900/5 dark:ring-white/5 overflow-hidden transition-all duration-300">
                            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                                <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2.5">
                                    <LuPalette class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    Theme & Appearance
                                </h2>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1.5 ml-7.5">Customize colors and imagery for the guest-facing form.</p>
                            </div>
                            <div class="p-6">
                                <form-style-designer
                                    v-model="form.style_tokens"
                                    :error="styleTokensError" />
                            </div>
                        </div>

                        <!-- Mobile Save Button Container -->
                        <div class="lg:hidden pt-4 pb-8">
                            <button
                                @click="submitProxy"
                                :disabled="processing || isSaving"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-black tracking-wide rounded-xl shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                                <LuLoader2
                                    v-if="processing || isSaving"
                                    class="w-5 h-5 animate-spin" />
                                <LuSave
                                    v-else
                                    class="w-5 h-5" />
                                {{ processing || isSaving ? "Saving Form..." : "Save Complete Form" }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Live Preview Panel (Desktop) -->
                <div class="hidden lg:block lg:col-span-4 xl:col-span-6 sticky top-[5.5rem] self-start h-[calc(100vh-6.5rem)] overflow-y-auto no-scrollbar pb-8">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2.5">
                                <LuEye class="w-4 h-4 text-indigo-500" />
                                Live Preview
                            </h2>
                            <span
                                v-if="!canPreview"
                                class="text-[0.65rem] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                Fill details to preview
                            </span>
                        </div>

                        <!-- Mobile Device Mockup for Preview -->
                        <div
                            class="relative mx-auto drop-shadow-2xl mt-4 origin-top scale-[0.82] xl:scale-[0.95]"
                            style="width: 426px; height: 866px">
                            <!-- Device Frame Image -->
                            <div
                                class="absolute inset-0 z-10 pointer-events-none"
                                style="background-image: url(&quot;/imgs/iphone14-promax.png&quot;); background-size: 100% 100%; width: 478px; height: 973px; transform: scale(0.89); transform-origin: top left"></div>

                            <!-- Screen Content -->
                            <div
                                class="absolute z-20 overflow-y-auto no-scrollbar bg-slate-900"
                                style="width: 428px; height: 928px; top: 20.47px; left: 23.14px; transform: scale(0.89); transform-origin: top left; clip-path: url(#viewport-mask1)">
                                <guest-card
                                    v-if="canPreview"
                                    :data="form"
                                    class="w-full shadow-none border-none rounded-none min-h-full pb-10" />
                                <div
                                    v-else
                                    class="flex flex-col items-center justify-center h-full px-6 text-center text-slate-400 dark:text-slate-500">
                                    <div class="w-20 h-20 bg-slate-800 rounded-3xl flex items-center justify-center mb-5 shadow-sm border border-slate-700">
                                        <LuFileQuestion class="w-10 h-10 opacity-60" />
                                    </div>
                                    <p class="text-base font-semibold tracking-wide">Enter form title and start date to see the live preview.</p>
                                </div>
                            </div>

                            <!-- Clip path for the dynamic island and screen corners -->
                            <svg
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="absolute w-0 h-0">
                                <clipPath id="viewport-mask1">
                                    <path
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M18.5 16.5C3.11567 31.5 0 47.5 0 81V847C0 880.5 3.11567 896.5 18.5 911.5C33.0965 925.732 51.5 928 81 928H347C376.5 928 394.904 925.732 409.5 911.5C424.884 896.5 428 880.5 428 847V81C428 47.5 424.884 31.5 409.5 16.5C394.904 2.26814 376.5 0 347 0H81C51.5 0 33.0965 2.26814 18.5 16.5ZM147.4 31.95C147.4 21.8708 155.571 13.7 165.65 13.7H212.15C222.23 13.7 230.4 21.8708 230.4 31.95C230.4 42.0292 222.23 50.2 212.15 50.2H165.65C155.571 50.2 147.4 42.0292 147.4 31.95ZM261.95 13.7C251.871 13.7 243.7 21.8708 243.7 31.95C243.7 42.0291 251.871 50.2 261.95 50.2C272.029 50.2 280.2 42.0291 280.2 31.95C280.2 21.8708 272.029 13.7 261.95 13.7Z"></path>
                                </clipPath>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Preview Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 backdrop-blur-none"
                enter-to-class="opacity-100 backdrop-blur-sm"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 backdrop-blur-sm"
                leave-to-class="opacity-0 backdrop-blur-none">
                <div
                    v-if="showMobilePreview"
                    class="fixed inset-0 z-50 bg-slate-900/60 lg:hidden"
                    @click="showMobilePreview = false">
                    <div
                        class="absolute inset-x-0 bottom-0 top-[4.5rem] bg-slate-100 dark:bg-slate-950 rounded-t-3xl overflow-y-auto shadow-2xl ring-1 ring-white/10"
                        @click.stop>
                        <!-- Drag Handle / Header -->
                        <div class="sticky top-0 bg-slate-100 dark:bg-slate-950 pt-3 pb-2 z-10 flex flex-col items-center justify-center border-b border-slate-200 dark:border-slate-800/50">
                            <div
                                class="w-12 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mb-3 cursor-pointer"
                                @click="showMobilePreview = false"></div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">Live Form Preview</h3>
                        </div>

                        <div class="p-4 sm:p-6 pb-20">
                            <guest-card
                                v-if="canPreview"
                                :data="form"
                                class="w-full mx-auto max-w-md shadow-xl" />
                            <div
                                v-else
                                class="bg-white dark:bg-slate-900 rounded-2xl p-10 text-center text-slate-400 dark:text-slate-500 shadow-sm border border-slate-200 dark:border-slate-800 max-w-md mx-auto mt-10">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <LuFileQuestion class="w-8 h-8 opacity-60" />
                                </div>
                                <p class="text-sm font-bold">Add a title and date to preview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
