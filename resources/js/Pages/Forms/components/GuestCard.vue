<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import PreregistrationQuizBeeCard from "@/Pages/Forms/components/PreregistrationQuizBeeCard.vue";
import PreregistrationQuizbeeTeamCard from "@/Pages/Forms/components/PreregistrationQuizbeeTeamCard.vue";
import DynamicFormRenderer from "@/Pages/Forms/components/Dynamic/DynamicFormRenderer.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import RegistrationCard from "@/Pages/Forms/components/RegistrationCard.vue";
import FeedbackCard from "@/Pages/Forms/components/FeedbackCard.vue";
import { mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import { MapPin, Clock, Calendar, Users, Hash, Timer, CircleCheck, CircleX, ChevronRight, Menu, X, Shield, UserCheck, UserPlus, Mail, Search, Loader2, AlertTriangle, AlertCircle, CheckCircle, XCircle, Lock, Ban, LogOut, ArrowRight, Sparkles, CalendarDays, Hourglass, Radio } from "lucide-vue-next";

export default {
    name: "GuestCard",
    components: {
        FeedbackCard,
        RegistrationCard,
        PreregistrationCard,
        PreregistrationQuizBeeCard,
        PreregistrationQuizbeeTeamCard,
        DynamicFormRenderer,
        MapPin,
        Clock,
        Calendar,
        Users,
        Hash,
        Timer,
        CircleCheck,
        CircleX,
        ChevronRight,
        Menu,
        X,
        Shield,
        UserCheck,
        UserPlus,
        Mail,
        Search,
        Loader2,
        AlertTriangle,
        AlertCircle,
        CheckCircle,
        XCircle,
        Lock,
        Ban,
        LogOut,
        ArrowRight,
        Sparkles,
        CalendarDays,
        Hourglass,
        Radio,
    },
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            activeTab: null,
            intervalId: null,
            formCountdownIntervalId: null,
            formCountdownNow: Date.now(),
            workflowState: null,
            workflowLoading: false,
            workflowError: null,
            selectedParticipantHash: null,
            participantFlowChoice: null,
            participantLookupEmail: "",
            participantLookupLoading: false,
            participantLookupError: null,
            participantLookupSuccess: null,
            isInitialized: false,
            showMobileMenu: false,
        };
    },
    computed: {
        resolvedStyleTokens() {
            return mergeFormStyleTokens(this.data?.style_tokens);
        },
        workflowFeatureToggles() {
            return (
                this.workflowState?.feature_toggles || {
                    event_workflow_enabled: true,
                    participant_workflow_enabled: true,
                    participant_verification_enabled: true,
                }
            );
        },
        participantWorkflowEnabled() {
            return this.workflowFeatureToggles.participant_workflow_enabled !== false;
        },
        participantVerificationEnabled() {
            return this.workflowFeatureToggles.participant_verification_enabled !== false;
        },
        workflowSteps() {
            return this.workflowState?.steps || [];
        },
        workflowTabs() {
            return this.workflowSteps
                .filter((step) => step.status !== "hidden")
                .map((step, index) => ({
                    key: step.id,
                    label: step.name || `Step ${index + 1}`,
                    disabled: step.status !== "available",
                    status: step.status,
                }));
        },
        activeStep() {
            return this.workflowSteps.find((step) => step.id === this.activeTab) || null;
        },
        currentRequirement() {
            if (this.activeStep) return this.activeStep;
            const formType = this.activeTab;
            return formType ? this.whatForm(formType) : null;
        },
        currentMaxSlots() {
            return this.currentRequirement?.max_slots ?? this.data?.max_slots ?? null;
        },
        currentResponsesCount() {
            if (this.currentRequirement?.responses_count !== undefined && this.currentRequirement?.responses_count !== null) {
                return this.currentRequirement.responses_count;
            }
            return this.data?.responses_count ?? 0;
        },
        slotsAvailable() {
            if (!this.currentMaxSlots || this.currentMaxSlots <= 0) return null;
            return Math.max(0, this.currentMaxSlots - (this.currentResponsesCount ?? 0));
        },
        slotFillPercent() {
            if (!this.currentMaxSlots || this.currentMaxSlots <= 0) return 0;
            return Math.round(((this.currentMaxSlots - (this.slotsAvailable ?? 0)) / this.currentMaxSlots) * 100);
        },
        slotStatusClass() {
            if (this.slotsAvailable === 0) return "bg-red-500";
            const ratio = (this.slotsAvailable ?? 0) / (this.currentMaxSlots ?? 1);
            if (ratio <= 0.25) return "bg-orange-500";
            if (ratio <= 0.5) return "bg-amber-500";
            return "bg-emerald-500";
        },
        eventStartAt() {
            const startDate = this.data?.date_from;
            const startTime = this.data?.time_from;
            if (!startDate || !startTime) return null;
            return this.parseDateTimeValue(`${startDate} ${startTime}`);
        },
        eventEndAt() {
            const endDate = this.data?.date_to;
            const endTime = this.data?.time_to;
            if (!endDate || !endTime) return null;
            return this.parseDateTimeValue(`${endDate} ${endTime}`);
        },
        latestSubformCloseAt() {
            const candidates = [...(Array.isArray(this.workflowSteps) ? this.workflowSteps : []), ...(Array.isArray(this.data?.requirements) ? this.data.requirements : [])];
            const timestamps = candidates
                .map((step) => this.parseDateTimeValue(step?.open_to ?? step?.config?.open_to))
                .filter((value) => value instanceof Date && !Number.isNaN(value.getTime()))
                .map((value) => value.getTime());
            if (!timestamps.length) return null;
            return new Date(Math.max(...timestamps));
        },
        effectiveEventEndAt() {
            const eventEnd = this.eventEndAt?.getTime?.() ?? null;
            const subformEnd = this.latestSubformCloseAt?.getTime?.() ?? null;
            if (eventEnd && subformEnd) return new Date(Math.max(eventEnd, subformEnd));
            if (eventEnd) return this.eventEndAt;
            if (subformEnd) return this.latestSubformCloseAt;
            return null;
        },
        eventState() {
            const now = this.formCountdownNow;
            const start = this.eventStartAt?.getTime?.() ?? null;
            const end = this.effectiveEventEndAt?.getTime?.() ?? null;
            if (start && now < start) return "upcoming";
            if (end && now <= end) return "ongoing";
            if (start && !end && now >= start) return "ongoing";
            if (end && now > end) return "expired";
            return "upcoming";
        },
        eventCountdownTargetAt() {
            if (this.eventState === "upcoming") return this.eventStartAt;
            if (this.eventState === "ongoing") return this.effectiveEventEndAt;
            return null;
        },
        countdownParts() {
            const target = this.eventCountdownTargetAt;
            if (!target) return { d: "00", h: "00", m: "00", s: "00" };
            const remaining = target.getTime() - this.formCountdownNow;
            if (remaining <= 0) return { d: "00", h: "00", m: "00", s: "00" };
            const totalSeconds = Math.floor(remaining / 1000);
            const d = Math.floor(totalSeconds / 86400);
            const h = Math.floor((totalSeconds % 86400) / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            return {
                d: String(d).padStart(2, "0"),
                h: String(h).padStart(2, "0"),
                m: String(m).padStart(2, "0"),
                s: String(s).padStart(2, "0"),
            };
        },
        isExpired() {
            return this.eventState === "expired";
        },
        isMobile() {
            if (typeof window === "undefined") return false;
            return window.innerWidth < 768;
        },
    },
    mounted() {
        this.startCountdown();
        this.startFormCountdownTicker();
        this.initializeParticipantContext();
        this.hydrateParticipantLookupEmail();
        this.loadWorkflow();
        this.isInitialized = true;
    },
    watch: {
        participantHashes: {
            handler(newHashes) {
                const latest = newHashes?.slice(-1)?.[0] ?? null;
                if (latest && latest !== this.selectedParticipantHash) {
                    this.selectedParticipantHash = latest;
                    this.loadWorkflow();
                }
            },
        },
        selectedParticipantHash(newValue) {
            if (this.isInitialized) this.persistParticipantContext(newValue);
        },
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        clearInterval(this.formCountdownIntervalId);
    },
    methods: {
        normalizeParticipantHash(value) {
            if (!value || typeof value !== "string") return null;
            const normalized = value.trim();
            return normalized !== "" ? normalized : null;
        },
        getParticipantHashFromUrl() {
            if (typeof window === "undefined") return null;
            const params = new URLSearchParams(window.location.search);
            return this.normalizeParticipantHash(params.get("participant") || params.get("participant_id") || params.get("participant_hash"));
        },
        getParticipantHashFromSession() {
            if (!this.data?.event_id || typeof sessionStorage === "undefined") return null;
            return this.normalizeParticipantHash(sessionStorage.getItem(`event_participant_hash_${this.data.event_id}`));
        },
        persistParticipantContext(hash) {
            if (!this.data?.event_id || typeof window === "undefined") return;
            const normalizedHash = this.normalizeParticipantHash(hash);
            if (typeof sessionStorage !== "undefined") {
                const sessionKey = `event_participant_hash_${this.data.event_id}`;
                normalizedHash ? sessionStorage.setItem(sessionKey, normalizedHash) : sessionStorage.removeItem(sessionKey);
            }
            const url = new URL(window.location.href);
            normalizedHash ? url.searchParams.set("participant", normalizedHash) : url.searchParams.delete("participant");
            window.history.replaceState({}, "", url.toString());
        },
        initializeParticipantContext() {
            const urlHash = this.getParticipantHashFromUrl();
            const localHash = this.participantHashes?.slice(-1)?.[0] ?? null;
            this.selectedParticipantHash = urlHash || localHash || null;
        },
        startFormCountdownTicker() {
            this.formCountdownNow = Date.now();
            this.formCountdownIntervalId = setInterval(() => {
                this.formCountdownNow = Date.now();
            }, 1000);
        },
        parseDateTimeValue(value) {
            if (!value) return null;
            const parsed = new Date(value);
            if (!Number.isNaN(parsed.getTime())) return parsed;
            if (typeof value === "string") {
                const fallback = new Date(value.replace(" ", "T"));
                if (!Number.isNaN(fallback.getTime())) return fallback;
            }
            return null;
        },
        formatCountdownDuration(milliseconds) {
            if (!milliseconds || milliseconds <= 0) return "0d 0h 0m 0s";
            const totalSeconds = Math.floor(milliseconds / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            return `${days}d ${hours}h ${minutes}m ${seconds}s`;
        },
        getStepCountdownMeta(step) {
            if (!step) return null;
            const now = this.formCountdownNow;
            const openFrom = this.parseDateTimeValue(step.open_from ?? step.config?.open_from);
            const openTo = this.parseDateTimeValue(step.open_to ?? step.config?.open_to);
            if (step.status === "not_yet_open" && openFrom) {
                const remaining = openFrom.getTime() - now;
                if (remaining > 0) return { label: "Opens in", value: this.formatCountdownDuration(remaining) };
            }
            if (step.status === "available" && openTo) {
                const remaining = openTo.getTime() - now;
                if (remaining > 0) return { label: "Closes in", value: this.formatCountdownDuration(remaining) };
            }
            return null;
        },
        onParticipantHashChange(value) {
            this.selectedParticipantHash = value;
            this.participantFlowChoice = null;
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            this.hydrateParticipantLookupEmail();
            this.loadWorkflow();
        },
        clearParticipant() {
            this.selectedParticipantHash = null;
            this.participantFlowChoice = null;
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            this.persistParticipantContext(null);
            this.loadWorkflow();
        },
        requiresParticipant(formType) {
            if (!this.participantWorkflowEnabled) return false;
            if (!this.participantVerificationEnabled) return false;
            const step = formType ? this.getStep(formType) : this.activeStep;
            if (step?.requires_participant_context === true) return true;
            const explicitToggle = step?.form_config?.require_participant_verification;
            if (typeof explicitToggle === "boolean") return explicitToggle;
            const exempt = ["preregistration", "preregistration_biotech", "preregistration_quizbee"];
            return !exempt.includes(formType);
        },
        canRenderForm(formType) {
            if (!this.participantWorkflowEnabled) return true;
            if (!this.requiresParticipant(formType)) return true;
            return !!this.selectedParticipantHash;
        },
        getParticipantIdForStep(stepIdentifier) {
            if (!this.participantWorkflowEnabled) return null;
            return this.requiresParticipant(stepIdentifier) ? this.selectedParticipantHash : null;
        },
        getAvailablePreregistrationStep() {
            const preregTypes = ["preregistration", "preregistration_biotech", "preregistration_quizbee"];
            return this.workflowSteps.find((step) => preregTypes.includes(step.form_type)) || null;
        },
        goToPreregistrationStep() {
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            const step = this.getAvailablePreregistrationStep();
            if (step) {
                this.activeTab = step.id;
                if (step.status !== "available") {
                    this.participantLookupError = `Preregistration is currently ${step.status?.replace("_", " ") || "unavailable"}. ${this.getStepMessage(step)}`;
                }
                return;
            }
            this.participantLookupError = "No preregistration step is configured for this event. Please contact the event organizer.";
        },
        setParticipantFlowChoice(choice) {
            this.participantFlowChoice = choice;
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            if (choice === "yes") this.hydrateParticipantLookupEmail();
        },
        normalizeEmail(value) {
            if (!value || typeof value !== "string") return null;
            const normalized = value.trim().toLowerCase();
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized);
            return isValid ? normalized : null;
        },
        getStoredParticipantByHash(hash) {
            if (!hash) return null;
            return this.storedLocalHashedIds.find((item) => item?.participant_hash === hash) || null;
        },
        rememberParticipantLookupEmail(email) {
            const normalized = this.normalizeEmail(email);
            if (!normalized) return;
            this.participantLookupEmail = normalized;
            localStorage.setItem("participant_lookup_email", normalized);
        },
        getRememberedParticipantLookupEmail() {
            return this.normalizeEmail(localStorage.getItem("participant_lookup_email"));
        },
        hydrateParticipantLookupEmail() {
            const selectedEmail = this.normalizeEmail(this.getStoredParticipantByHash(this.selectedParticipantHash)?.participant?.email);
            const rememberedEmail = this.getRememberedParticipantLookupEmail();
            const preferredEmail = selectedEmail || rememberedEmail;
            if (preferredEmail) this.participantLookupEmail = preferredEmail;
        },
        inferEmailFromPayload(payload) {
            const directEmail = this.normalizeEmail(payload?.participant?.email);
            if (directEmail) return directEmail;
            const responseData = payload?.data?.response_data ?? payload?.response_data ?? {};
            const entries = Object.entries(responseData);
            for (const [key, rawValue] of entries) {
                if (typeof rawValue !== "string") continue;
                const maybeEmail = this.normalizeEmail(rawValue);
                if (!maybeEmail) continue;
                if (key.toLowerCase().includes("email")) return maybeEmail;
            }
            for (const [, rawValue] of entries) {
                if (typeof rawValue !== "string") continue;
                const maybeEmail = this.normalizeEmail(rawValue);
                if (maybeEmail) return maybeEmail;
            }
            return null;
        },
        extractParticipantHash(payload) {
            return payload?.participant_hash || payload?.data?.participant_hash || payload?.registration?.id || null;
        },
        extractParticipant(payload) {
            return payload?.participant || payload?.data?.participant || null;
        },
        async lookupRegisteredParticipant() {
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            const normalizedEmail = this.normalizeEmail(this.participantLookupEmail);
            if (!normalizedEmail) {
                this.participantLookupError = "Please enter your registered email address.";
                return;
            }
            this.rememberParticipantLookupEmail(normalizedEmail);
            this.participantLookupLoading = true;
            try {
                const response = await this.fetchGetApi("api.event.participant.lookup.guest", {
                    routeParams: this.data.event_id,
                    email: normalizedEmail,
                });
                const data = response?.data ?? {};
                if (!data?.found || !data?.participant_hash) {
                    this.participantLookupError = data?.message || "No registration found. Please complete preregistration first.";
                    return;
                }
                this.selectedParticipantHash = data.participant_hash;
                this.participantLookupSuccess = "Registration found! Continuing with your saved profile.";
                this.rememberParticipantLookupEmail(data?.participant?.email || normalizedEmail);
                this.saveLocalHashedIds({
                    participant_hash: data.participant_hash,
                    participant: data.participant,
                });
                await this.loadWorkflow();
            } catch (error) {
                this.participantLookupError = "Unable to validate your registration. Please try again.";
            } finally {
                this.participantLookupLoading = false;
            }
        },
        async loadWorkflow() {
            if (!this.data?.event_id) return;
            this.workflowLoading = true;
            this.workflowError = null;
            try {
                const response = await this.fetchGetApi("api.event.workflow.state.guest", {
                    routeParams: this.data.event_id,
                    participant_id: this.selectedParticipantHash,
                });
                this.workflowState = response?.data ?? null;
                this.setActiveTabFromWorkflow();
            } catch (error) {
                this.workflowError = "Unable to load workflow state.";
            } finally {
                this.workflowLoading = false;
            }
        },
        async handleCreatedModel(payload) {
            const inferredEmail = this.inferEmailFromPayload(payload);
            if (inferredEmail) this.rememberParticipantLookupEmail(inferredEmail);
            const participantHash = this.extractParticipantHash(payload);
            const participant = this.extractParticipant(payload);
            if (participantHash) {
                this.selectedParticipantHash = participantHash;
                this.saveLocalHashedIds({
                    participant_hash: participantHash,
                    participant: participant || {
                        name: payload?.data?.response_data?.name || payload?.response_data?.name || "Participant",
                        email: inferredEmail || null,
                    },
                });
            }
            this.$emit("createdModel", payload);
            await this.loadWorkflow();
        },
        setActiveTabFromWorkflow() {
            if (!this.workflowSteps?.length) return;
            const preferred = this.workflowState?.current_step_id ? this.workflowSteps.find((step) => step.id === this.workflowState.current_step_id) : null;
            const available = this.workflowSteps.find((step) => step.status === "available");
            this.activeTab = preferred?.id ?? available?.id ?? this.workflowSteps[0]?.id ?? null;
        },
        whatForm(formType) {
            if (!this.data || !Array.isArray(this.data.requirements) || this.data.requirements.length <= 0) return null;
            return this.data.requirements.find((requirement) => requirement.form_type === formType) || null;
        },
        getStep(identifier) {
            return this.workflowSteps.find((step) => step.id === identifier || step.form_type === identifier) || null;
        },
        getRequirementFormId(identifier) {
            const step = this.getStep(identifier) ?? this.whatForm(identifier);
            return step ? step.id : null;
        },
        styleFor(key) {
            const token = this.resolvedStyleTokens?.[key];
            const textColorToken = this.resolvedStyleTokens?.[`${key}-text-color`];
            const textShadowToken = this.resolvedStyleTokens?.["form-text-shadow"];
            const styles = {};
            if (token && token.value) {
                if (token.mode === "image") {
                    styles.backgroundImage = `url(${token.value})`;
                    styles.backgroundSize = "cover";
                    styles.backgroundPosition = "center";
                    styles.backgroundRepeat = "no-repeat";
                } else if (token.mode === "color") {
                    styles.backgroundColor = token.value;
                }
            }
            if (textColorToken?.value) styles.color = textColorToken.value;
            if (textShadowToken?.value) styles.textShadow = textShadowToken.value;
            return styles;
        },
        getStepMessage(step) {
            if (!step) return "This step is not available";
            switch (step.status) {
                case "locked":
                    return "Complete the previous step to continue";
                case "not_yet_open":
                    return step.open_from ? `Available on ${this.formatDateTime(step.open_from)}` : "Not yet available";
                case "expired":
                    return "Closed on " + (step.open_to ? this.formatDateTime(step.open_to) : "an earlier date");
                case "full":
                    return "No slots available";
                case "disabled":
                    return "Currently disabled";
                case "hidden":
                    return "Not available";
                case "completed":
                    return "Already completed";
                default:
                    return "Not available";
            }
        },
        formatDateTime(dateString) {
            try {
                return new Date(dateString).toLocaleString("en-US", {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: true,
                });
            } catch {
                return dateString;
            }
        },
        hasDynamicSchema(step) {
            return step?.field_schema && Array.isArray(step.field_schema) && step.field_schema.length > 0;
        },
        getFieldSchema(identifier) {
            const step = this.getStep(identifier);
            return step?.field_schema || [];
        },
        getStepTitle(identifier) {
            const step = this.getStep(identifier);
            if (!step) return "";
            const titles = {
                preregistration: "Pre-register Now!",
                registration: "Registration",
                feedback: "Feedback Form",
                pretest: "Pre-Test",
                posttest: "Post-Test",
            };
            return titles[step.form_type] || step.name || step.form_type.replace(/_/g, " ");
        },
        getDescription(identifier) {
            const step = this.getStep(identifier);
            return step?.description || "";
        },
        getStepIcon(status) {
            const map = {
                locked: "Lock",
                not_yet_open: "Clock",
                expired: "XCircle",
                full: "Users",
                completed: "CheckCircle",
                disabled: "Ban",
            };
            return map[status] || "Lock";
        },
    },
};
</script>

<template>
    <div
        v-if="!!data"
        class="mx-auto w-full max-w-3xl space-y-5 pb-24 md:pb-8">
        <!-- Mobile Header (Hidden on Desktop) -->
        <div
            v-if="workflowTabs.length > 1"
            class="sticky top-0 z-40 flex items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-md md:hidden dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex min-w-0 items-center gap-2">
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 font-mono text-xs font-medium tracking-wide text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <Hash
                        :size="12"
                        :stroke-width="2" />
                    {{ data.event_id }}
                </span>
                <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ data.title }}
                </span>
            </div>
            <button
                @click="showMobileMenu = !showMobileMenu"
                class="shrink-0 rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800">
                <Menu
                    :size="18"
                    :stroke-width="2" />
            </button>
        </div>

        <!-- Mobile Steps Drawer -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div
                v-if="showMobileMenu && workflowTabs.length > 1"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm md:hidden"
                @click="showMobileMenu = false">
                <div
                    class="absolute right-0 top-0 flex h-full w-64 transform flex-col gap-4 bg-white p-5 shadow-2xl transition-transform dark:bg-slate-900"
                    @click.stop>
                    <div class="mb-2 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Form Steps</span>
                        <button
                            @click="showMobileMenu = false"
                            class="rounded-md p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <X
                                :size="16"
                                :stroke-width="2" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <button
                            v-for="(tab, i) in workflowTabs"
                            :key="tab.key"
                            @click="
                                activeTab = tab.key;
                                showMobileMenu = false;
                            "
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                            :class="activeTab === tab.key ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50'"
                            :disabled="tab.disabled">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                :class="activeTab === tab.key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800'">
                                {{ i + 1 }}
                            </span>
                            <span class="flex-1 truncate">{{ tab.label }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- EVENT HEADER CARD -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/90 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
            :style="styleFor('form-background')">
            <div
                class="relative border-b border-white/10 p-6 sm:p-8 dark:border-slate-800/50"
                :style="{ ...styleFor('form-header-box') }">
                <div class="relative z-10 flex flex-col gap-2.5">
                    <div
                        class="inline-flex w-fit items-center gap-1.5 rounded-full bg-black/5 px-2.5 py-1 font-mono text-xs font-medium uppercase tracking-wide backdrop-blur-md dark:bg-white/5"
                        :style="{
                            color: resolvedStyleTokens?.['form-header-box-text-color']?.value,
                        }">
                        <Hash
                            :size="12"
                            :stroke-width="2" />
                        {{ data.event_id }}
                    </div>
                    <h2
                        class="text-xl font-semibold leading-tight tracking-tight sm:text-2xl"
                        :style="{
                            color: resolvedStyleTokens?.['form-header-box-text-color']?.value,
                        }">
                        {{ data.title }}
                    </h2>
                    <p
                        class="max-w-2xl text-sm font-normal leading-relaxed opacity-90"
                        :style="{
                            color: resolvedStyleTokens?.['form-header-box-text-color']?.value,
                        }">
                        {{ data.description }}
                    </p>
                </div>
            </div>

            <!-- Status Ribbon -->
            <div class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-3.5 sm:flex-row sm:items-center sm:px-6 dark:border-slate-800 dark:bg-slate-800/40">
                <div class="flex items-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium uppercase tracking-wide"
                        :class="eventState === 'ongoing' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : eventState === 'upcoming' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'">
                        <span
                            v-if="eventState === 'ongoing'"
                            class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        </span>
                        <span
                            v-else
                            class="h-1.5 w-1.5 rounded-full"
                            :class="eventState === 'upcoming' ? 'bg-amber-500' : 'bg-red-500'"></span>
                        {{ eventState === "ongoing" ? "Live" : eventState === "upcoming" ? "Upcoming" : "Ended" }}
                    </span>
                </div>

                <!-- Countdown Ticker -->
                <div class="flex items-center gap-1.5">
                    <template v-if="eventCountdownTargetAt">
                        <div class="flex items-baseline gap-0.5">
                            <span class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ countdownParts.d }}
                            </span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">d</span>
                        </div>
                        <span class="font-mono text-slate-300 dark:text-slate-600">:</span>
                        <div class="flex items-baseline gap-0.5">
                            <span class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ countdownParts.h }}
                            </span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">h</span>
                        </div>
                        <span class="font-mono text-slate-300 dark:text-slate-600">:</span>
                        <div class="flex items-baseline gap-0.5">
                            <span class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ countdownParts.m }}
                            </span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">m</span>
                        </div>
                        <span class="font-mono text-slate-300 dark:text-slate-600">:</span>
                        <div class="flex items-baseline gap-0.5">
                            <span class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ countdownParts.s }}
                            </span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">s</span>
                        </div>
                    </template>
                    <span
                        v-else
                        class="text-sm font-medium text-slate-400">
                        —
                    </span>
                </div>
            </div>

            <!-- Dates Grid -->
            <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100 bg-white/50 backdrop-blur-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900/50">
                <div
                    class="p-4 text-center sm:p-5"
                    :style="styleFor('form-time-from')">
                    <div class="mb-1.5 flex items-center justify-center gap-1.5 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <CalendarDays
                            :size="14"
                            :stroke-width="1.5"
                            class="text-indigo-500 dark:text-indigo-400" />
                        Starts
                    </div>
                    <p
                        class="text-sm font-medium text-slate-900 dark:text-white"
                        :style="{
                            color: resolvedStyleTokens?.['form-time-from-text-color']?.value,
                        }">
                        {{ formatDate(data.date_from) }}
                    </p>
                    <p
                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        :style="{
                            color: resolvedStyleTokens?.['form-time-from-text-color']?.value,
                        }">
                        {{ formatTime(data.time_from) }}
                    </p>
                </div>
                <div
                    class="p-4 text-center sm:p-5"
                    :style="styleFor('form-time-to')">
                    <div class="mb-1.5 flex items-center justify-center gap-1.5 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <CalendarDays
                            :size="14"
                            :stroke-width="1.5"
                            class="text-indigo-500 dark:text-indigo-400" />
                        Ends
                    </div>
                    <p
                        class="text-sm font-medium text-slate-900 dark:text-white"
                        :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                        {{ formatDate(data.date_to) }}
                    </p>
                    <p
                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                        {{ formatTime(data.time_to) }}
                    </p>
                </div>
            </div>

            <!-- Venue & Slots -->
            <div
                v-if="data.venue"
                class="flex items-start gap-3 border-b border-slate-100 bg-white/50 p-4 backdrop-blur-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/50">
                <MapPin
                    :size="16"
                    :stroke-width="1.5"
                    class="mt-0.5 shrink-0 text-indigo-500 dark:text-indigo-400" />
                <div class="flex flex-col gap-0.5">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        {{ data.venue }}
                    </p>
                    <p
                        v-if="data.details"
                        class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ data.details }}
                    </p>
                </div>
            </div>

            <div
                v-if="currentMaxSlots && currentMaxSlots > 0"
                class="flex items-center justify-between gap-4 bg-slate-50/80 p-4 sm:p-5 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <Users
                        :size="14"
                        :stroke-width="1.5"
                        class="text-indigo-500 dark:text-indigo-400" />
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            {{ slotsAvailable }}
                        </span>
                        of {{ currentMaxSlots }} slots available
                    </span>
                </div>
                <div class="h-1 w-24 shrink-0 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="slotStatusClass"
                        :style="{ width: `${slotFillPercent}%` }"></div>
                </div>
            </div>
        </div>

        <!-- STATUS ALERTS -->
        <div
            v-if="data.is_suspended"
            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
            <AlertTriangle
                :size="18"
                :stroke-width="1.5"
                class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400" />
            <div>
                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">Event Temporarily Unavailable</p>
                <p class="mt-1 text-xs text-amber-700 dark:text-amber-400/80">This event is currently suspended and not accepting responses.</p>
            </div>
        </div>
        <div
            v-else-if="isExpired"
            class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
            <XCircle
                :size="18"
                :stroke-width="1.5"
                class="mt-0.5 shrink-0 text-red-600 dark:text-red-400" />
            <div>
                <p class="text-sm font-medium text-red-900 dark:text-red-200">Event Has Ended</p>
                <p class="mt-1 text-xs text-red-700 dark:text-red-400/80">This event is no longer accepting responses.</p>
            </div>
        </div>

        <!-- PARTICIPANT SELECTOR -->
        <div
            v-if="participantHashes?.length && !data.is_suspended && !isExpired"
            class="flex flex-col gap-3 rounded-2xl border border-slate-200/60 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <label class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Continue as</label>
            <div class="flex items-center gap-3">
                <custom-dropdown
                    @selectedChange="onParticipantHashChange"
                    :value="selectedParticipantHash"
                    :options="[
                        { name: null, label: 'New participant' },
                        ...storedLocalHashedIds.map((item) => ({
                            name: item.participant_hash,
                            label: item.participant.name,
                        })),
                    ]"
                    :withAllOption="false"
                    class="flex-1 text-sm font-medium" />
                <button
                    v-if="selectedParticipantHash"
                    @click="clearParticipant"
                    class="rounded-xl border border-transparent p-2 text-red-500 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-500/30 dark:hover:bg-red-500/10"
                    title="Sign out">
                    <LogOut
                        :size="16"
                        :stroke-width="1.5" />
                </button>
            </div>
        </div>

        <!-- PARTICIPANT VERIFICATION -->
        <div
            v-if="activeStep?.status === 'available' && participantWorkflowEnabled && participantVerificationEnabled && requiresParticipant(activeTab) && !selectedParticipantHash && !data.is_suspended && !isExpired"
            class="flex flex-col gap-5 rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex items-center gap-2.5 text-indigo-600 dark:text-indigo-400">
                <Shield
                    :size="18"
                    :stroke-width="1.5" />
                <span class="text-sm font-medium uppercase tracking-wide text-slate-900 dark:text-white">Verify Registration</span>
            </div>
            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">This step requires a registered profile. Have you used this form before?</p>

            <div
                v-if="!participantFlowChoice"
                class="grid grid-cols-2 gap-4">
                <button
                    @click="setParticipantFlowChoice('yes')"
                    class="group flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-center transition-all duration-200 hover:border-indigo-500 hover:bg-indigo-50 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-500/10">
                    <UserCheck
                        :size="24"
                        :stroke-width="1.5"
                        class="text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" />
                    <span class="text-sm font-medium text-slate-900 dark:text-white">Yes, I have</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Continue with profile</span>
                </button>
                <button
                    @click="setParticipantFlowChoice('no')"
                    class="group flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-center transition-all duration-200 hover:border-indigo-500 hover:bg-indigo-50 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-500/10">
                    <UserPlus
                        :size="24"
                        :stroke-width="1.5"
                        class="text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" />
                    <span class="text-sm font-medium text-slate-900 dark:text-white">No, I'm new</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Start preregistration</span>
                </button>
            </div>

            <div
                v-if="participantFlowChoice === 'yes'"
                class="flex flex-col gap-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Enter your registered email:</p>
                <div class="flex gap-2.5">
                    <div class="relative flex-1">
                        <Mail
                            :size="14"
                            :stroke-width="1.5"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="participantLookupEmail"
                            type="email"
                            placeholder="your@email.com"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-900 shadow-sm transition-all focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            @keyup.enter="lookupRegisteredParticipant" />
                    </div>
                    <button
                        @click="lookupRegisteredParticipant"
                        :disabled="participantLookupLoading"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 disabled:opacity-50">
                        <Loader2
                            v-if="participantLookupLoading"
                            :size="14"
                            :stroke-width="1.5"
                            class="animate-spin" />
                        <Search
                            v-else
                            :size="14"
                            :stroke-width="1.5" />
                        Find
                    </button>
                </div>
            </div>

            <div
                v-if="participantFlowChoice === 'no'"
                class="flex flex-col items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row dark:border-slate-700 dark:bg-slate-800/50">
                <p class="text-sm text-slate-600 dark:text-slate-300">Complete preregistration first to get started.</p>
                <button
                    @click="goToPreregistrationStep"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 sm:w-auto">
                    Go to Preregistration
                    <ArrowRight
                        :size="14"
                        :stroke-width="1.5" />
                </button>
            </div>

            <!-- Lookup Alerts -->
            <div
                v-if="participantLookupSuccess"
                class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-medium text-emerald-700 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                <CheckCircle
                    :size="14"
                    :stroke-width="1.5" />
                {{ participantLookupSuccess }}
            </div>
            <div
                v-if="participantLookupError"
                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-700 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                <AlertCircle
                    :size="14"
                    :stroke-width="1.5" />
                {{ participantLookupError }}
            </div>
        </div>

        <!-- LOADING STATE -->
        <div
            v-if="workflowLoading"
            class="flex flex-col items-center justify-center gap-3 py-12 text-indigo-600 dark:text-indigo-400">
            <Loader2
                :size="24"
                :stroke-width="1.5"
                class="animate-spin" />
            <span class="text-sm font-medium uppercase tracking-wide opacity-80">Loading forms...</span>
        </div>

        <!-- ERROR STATE -->
        <div
            v-if="workflowError"
            class="flex flex-col items-center justify-center rounded-2xl border border-red-200 bg-red-50 p-8 text-center dark:border-red-500/30 dark:bg-red-500/10">
            <AlertCircle
                :size="28"
                :stroke-width="1.5"
                class="mb-2 text-red-500" />
            <p class="text-sm font-medium text-red-900 dark:text-red-200">Failed to load</p>
            <p class="mt-1 text-xs text-red-700 dark:text-red-400">{{ workflowError }}</p>
        </div>

        <!-- TABS (Desktop) -->
        <div
            v-if="workflowTabs.length > 1 && !workflowLoading"
            class="no-scrollbar hidden gap-1.5 overflow-x-auto rounded-xl border border-slate-200 bg-slate-100 p-1.5 shadow-inner md:flex dark:border-slate-700/50 dark:bg-slate-800/60">
            <button
                v-for="(tab, i) in workflowTabs"
                :key="tab.key"
                @click="activeTab = tab.key"
                class="flex min-w-0 items-center gap-2.5 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all"
                :class="activeTab === tab.key ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-700 dark:text-indigo-400 dark:ring-slate-600' : 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-800 disabled:cursor-not-allowed disabled:opacity-50 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'"
                :disabled="tab.disabled">
                <span
                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[0.65rem]"
                    :class="activeTab === tab.key ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400' : 'bg-slate-200 text-slate-500 dark:bg-slate-800'">
                    {{ i + 1 }}
                </span>
                <span class="truncate">{{ tab.label }}</span>
                <!-- Status Dot -->
                <span
                    class="h-1.5 w-1.5 shrink-0 rounded-full"
                    :class="{
                        'bg-emerald-500': tab.status === 'available',
                        'bg-blue-500': tab.status === 'completed',
                        'bg-slate-400': ['locked', 'not_yet_open', 'disabled', 'hidden'].includes(tab.status),
                        'bg-red-500': ['expired', 'full'].includes(tab.status),
                    }"></span>
            </button>
        </div>

        <!-- MAIN FORM AREA -->
        <div
            v-if="activeTab && !workflowLoading"
            class="flex flex-col gap-4">
            <!-- Step Countdown Banner -->
            <div
                v-if="getStepCountdownMeta(getStep(activeTab))"
                class="flex items-center justify-between rounded-2xl border border-slate-200/60 bg-white/80 p-4 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                <div class="flex items-center gap-2.5 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <Hourglass
                        :size="14"
                        :stroke-width="1.5" />
                    <span>{{ getStepCountdownMeta(getStep(activeTab)).label }}</span>
                </div>
                <span
                    class="font-mono text-sm font-semibold"
                    :class="getStepCountdownMeta(getStep(activeTab)).label === 'Closes in' ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400'">
                    {{ getStepCountdownMeta(getStep(activeTab)).value }}
                </span>
            </div>

            <!-- Form Shell -->
            <div class="mt-2 flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/20">
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ getStepTitle(activeTab) }}
                    </h3>
                    <p
                        v-if="getDescription(activeTab)"
                        class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ getDescription(activeTab) }}
                    </p>
                </div>

                <div class="p-6">
                    <template v-if="activeStep?.status === 'available' && canRenderForm(activeTab)">
                        <DynamicFormRenderer
                            v-if="hasDynamicSchema(activeStep)"
                            :field-schema="getFieldSchema(activeTab)"
                            :event-id="getRequirementFormId(activeTab)"
                            :subform-type="getStep(activeTab)?.form_type || activeTab"
                            :participant-id="getParticipantIdForStep(activeTab)"
                            :config="getStep(activeTab)"
                            :title="getStepTitle(activeTab)"
                            :description="getDescription(activeTab)"
                            @createdModel="handleCreatedModel" />
                        <preregistration-card
                            v-else-if="getStep(activeTab)?.form_type === 'preregistration'"
                            :event-id="getRequirementFormId(activeTab)"
                            :config="getStep(activeTab)"
                            @createdModel="handleCreatedModel" />
                        <preregistration-quiz-bee-card
                            v-else-if="getStep(activeTab)?.form_type === 'preregistration_biotech'"
                            :event-id="getRequirementFormId(activeTab)"
                            :config="getStep(activeTab)"
                            @createdModel="handleCreatedModel" />
                        <preregistration-quizbee-team-card
                            v-else-if="getStep(activeTab)?.form_type === 'preregistration_quizbee'"
                            :event-id="getRequirementFormId(activeTab)"
                            :config="getStep(activeTab)"
                            @createdModel="handleCreatedModel" />
                        <registration-card
                            v-else-if="getStep(activeTab)?.form_type === 'registration'"
                            :event-id="getRequirementFormId(activeTab)"
                            :participant-id="getParticipantIdForStep(activeTab)"
                            :config="getStep(activeTab)"
                            @createdModel="handleCreatedModel" />
                        <feedback-card
                            v-else-if="getStep(activeTab)?.form_type === 'feedback'"
                            :event-id="getRequirementFormId(activeTab)"
                            :participant-id="getParticipantIdForStep(activeTab)"
                            :config="getStep(activeTab)"
                            @createdModel="handleCreatedModel" />
                    </template>

                    <!-- Unavailable state inside shell -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center px-4 py-16 text-center">
                        <div
                            class="mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                            :class="{
                                'bg-slate-100 text-slate-400 dark:bg-slate-800': ['locked', 'disabled'].includes(activeStep?.status),
                                'bg-amber-50 text-amber-500 dark:bg-amber-500/10': activeStep?.status === 'not_yet_open',
                                'bg-red-50 text-red-500 dark:bg-red-500/10': ['expired', 'full'].includes(activeStep?.status),
                                'bg-blue-50 text-blue-500 dark:bg-blue-500/10': activeStep?.status === 'completed',
                            }">
                            <component
                                :is="getStepIcon(activeStep?.status)"
                                :size="28"
                                :stroke-width="1.5" />
                        </div>
                        <h3 class="text-base font-semibold tracking-wide text-slate-900 dark:text-white">
                            {{ getStepMessage(getStep(activeTab)) }}
                        </h3>
                        <p
                            v-if="activeStep?.status === 'locked'"
                            class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                            Complete previous steps in the workflow to unlock this form.
                        </p>
                    </div>
                </div>
            </div>
        </div>
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
</style>
