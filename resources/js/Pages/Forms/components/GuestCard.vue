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

export default {
    name: "GuestCard",
    components: { FeedbackCard, RegistrationCard, PreregistrationCard, PreregistrationQuizBeeCard, PreregistrationQuizbeeTeamCard, DynamicFormRenderer },
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
            participantLookupEmail: '',
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
            return this.workflowState?.feature_toggles || {
                event_workflow_enabled: true,
                participant_workflow_enabled: true,
                participant_verification_enabled: true,
            };
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
                .filter((step) => step.status !== 'hidden')
                .map((step, index) => ({
                    key: step.id,
                    label: step.name || `Step ${index + 1}`,
                    disabled: step.status !== 'available',
                    status: step.status,
                }));
        },
        activeStep() {
            return this.workflowSteps.find((step) => step.id === this.activeTab) || null;
        },
        currentRequirement() {
            if (this.activeStep) {
                return this.activeStep;
            }
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
            if (!this.currentMaxSlots || this.currentMaxSlots <= 0) {
                return null;
            }
            return Math.max(0, this.currentMaxSlots - (this.currentResponsesCount ?? 0));
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
            const candidates = [
                ...(Array.isArray(this.workflowSteps) ? this.workflowSteps : []),
                ...(Array.isArray(this.data?.requirements) ? this.data.requirements : []),
            ];
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
            if (start && now < start) return 'upcoming';
            if (end && now <= end) return 'ongoing';
            if (start && !end && now >= start) return 'ongoing';
            if (end && now > end) return 'expired';
            return 'upcoming';
        },
        eventCountdownTargetAt() {
            if (this.eventState === 'upcoming') return this.eventStartAt;
            if (this.eventState === 'ongoing') return this.effectiveEventEndAt;
            return null;
        },
        eventCountdownDisplay() {
            const target = this.eventCountdownTargetAt;
            if (!target) return '0d 0h 0m 0s';
            const remaining = target.getTime() - this.formCountdownNow;
            return this.formatCountdownDuration(remaining);
        },
        countdownDisplay() {
            return this.eventCountdownDisplay;
        },
        isExpired() {
            return this.eventState === 'expired';
        },
        isMobile() {
            if (typeof window === 'undefined') return false;
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
        window.addEventListener('resize', this.handleResize);
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
            if (this.isInitialized) {
                this.persistParticipantContext(newValue);
            }
        },
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        clearInterval(this.formCountdownIntervalId);
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        handleResize() {
            this.$forceUpdate();
        },
        normalizeParticipantHash(value) {
            if (!value || typeof value !== 'string') return null;
            const normalized = value.trim();
            return normalized !== '' ? normalized : null;
        },
        getParticipantHashFromUrl() {
            if (typeof window === 'undefined') return null;
            const params = new URLSearchParams(window.location.search);
            return this.normalizeParticipantHash(
                params.get('participant') || params.get('participant_id') || params.get('participant_hash')
            );
        },
        getParticipantHashFromSession() {
            if (!this.data?.event_id || typeof sessionStorage === 'undefined') return null;
            return this.normalizeParticipantHash(sessionStorage.getItem(`event_participant_hash_${this.data.event_id}`));
        },
        persistParticipantContext(hash) {
            if (!this.data?.event_id || typeof window === 'undefined') return;
            const normalizedHash = this.normalizeParticipantHash(hash);
            if (typeof sessionStorage !== 'undefined') {
                const sessionKey = `event_participant_hash_${this.data.event_id}`;
                if (normalizedHash) {
                    sessionStorage.setItem(sessionKey, normalizedHash);
                } else {
                    sessionStorage.removeItem(sessionKey);
                }
            }
            const url = new URL(window.location.href);
            if (normalizedHash) {
                url.searchParams.set('participant', normalizedHash);
            } else {
                url.searchParams.delete('participant');
            }
            window.history.replaceState({}, '', url.toString());
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
            if (typeof value === 'string') {
                const fallback = new Date(value.replace(' ', 'T'));
                if (!Number.isNaN(fallback.getTime())) return fallback;
            }
            return null;
        },
        formatCountdownDuration(milliseconds) {
            if (!milliseconds || milliseconds <= 0) return '0d 0h 0m 0s';
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
            if (step.status === 'not_yet_open' && openFrom) {
                const remaining = openFrom.getTime() - now;
                if (remaining > 0) {
                    return { label: 'Opens in', value: this.formatCountdownDuration(remaining) };
                }
            }
            if (step.status === 'available' && openTo) {
                const remaining = openTo.getTime() - now;
                if (remaining > 0) {
                    return { label: 'Closes in', value: this.formatCountdownDuration(remaining) };
                }
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
            if (typeof explicitToggle === 'boolean') return explicitToggle;
            const exempt = ['preregistration', 'preregistration_biotech', 'preregistration_quizbee'];
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
            const preregTypes = ['preregistration', 'preregistration_biotech', 'preregistration_quizbee'];
            return this.workflowSteps.find((step) => preregTypes.includes(step.form_type)) || null;
        },
        goToPreregistrationStep() {
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            const step = this.getAvailablePreregistrationStep();
            if (step) {
                this.activeTab = step.id;
                if (step.status !== 'available') {
                    this.participantLookupError = `Preregistration is currently ${step.status?.replace('_', ' ') || 'unavailable'}. ${this.getStepMessage(step)}`;
                }
                return;
            }
            this.participantLookupError = 'No preregistration step is configured for this event. Please contact the event organizer.';
        },
        setParticipantFlowChoice(choice) {
            this.participantFlowChoice = choice;
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            if (choice === 'yes') {
                this.hydrateParticipantLookupEmail();
            }
        },
        normalizeEmail(value) {
            if (!value || typeof value !== 'string') return null;
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
            localStorage.setItem('participant_lookup_email', normalized);
        },
        getRememberedParticipantLookupEmail() {
            return this.normalizeEmail(localStorage.getItem('participant_lookup_email'));
        },
        hydrateParticipantLookupEmail() {
            const selectedEmail = this.normalizeEmail(this.getStoredParticipantByHash(this.selectedParticipantHash)?.participant?.email);
            const rememberedEmail = this.getRememberedParticipantLookupEmail();
            const preferredEmail = selectedEmail || rememberedEmail;
            if (preferredEmail) {
                this.participantLookupEmail = preferredEmail;
            }
        },
        inferEmailFromPayload(payload) {
            const directEmail = this.normalizeEmail(payload?.participant?.email);
            if (directEmail) return directEmail;
            const responseData = payload?.data?.response_data ?? payload?.response_data ?? {};
            const entries = Object.entries(responseData);
            for (const [key, rawValue] of entries) {
                if (typeof rawValue !== 'string') continue;
                const maybeEmail = this.normalizeEmail(rawValue);
                if (!maybeEmail) continue;
                if (key.toLowerCase().includes('email')) return maybeEmail;
            }
            for (const [, rawValue] of entries) {
                if (typeof rawValue !== 'string') continue;
                const maybeEmail = this.normalizeEmail(rawValue);
                if (maybeEmail) return maybeEmail;
            }
            return null;
        },
        extractParticipantHash(payload) {
            return payload?.participant_hash
                || payload?.data?.participant_hash
                || payload?.registration?.id
                || null;
        },
        extractParticipant(payload) {
            return payload?.participant
                || payload?.data?.participant
                || null;
        },
        async lookupRegisteredParticipant() {
            this.participantLookupError = null;
            this.participantLookupSuccess = null;
            const normalizedEmail = this.normalizeEmail(this.participantLookupEmail);
            if (!normalizedEmail) {
                this.participantLookupError = 'Please enter your registered email address.';
                return;
            }
            this.rememberParticipantLookupEmail(normalizedEmail);
            this.participantLookupLoading = true;
            try {
                const response = await this.fetchGetApi('api.event.participant.lookup.guest', {
                    routeParams: this.data.event_id,
                    email: normalizedEmail,
                });
                const data = response?.data ?? {};
                if (!data?.found || !data?.participant_hash) {
                    this.participantLookupError = data?.message || 'No registration found. Please complete preregistration first.';
                    return;
                }
                this.selectedParticipantHash = data.participant_hash;
                this.participantLookupSuccess = 'Registration found! Continuing with your saved profile.';
                this.rememberParticipantLookupEmail(data?.participant?.email || normalizedEmail);
                this.saveLocalHashedIds({
                    participant_hash: data.participant_hash,
                    participant: data.participant,
                });
                await this.loadWorkflow();
            } catch (error) {
                this.participantLookupError = 'Unable to validate your registration. Please try again.';
            } finally {
                this.participantLookupLoading = false;
            }
        },
        async loadWorkflow() {
            if (!this.data?.event_id) return;
            this.workflowLoading = true;
            this.workflowError = null;
            try {
                const response = await this.fetchGetApi('api.event.workflow.state.guest', {
                    routeParams: this.data.event_id,
                    participant_id: this.selectedParticipantHash,
                });
                this.workflowState = response?.data ?? null;
                this.setActiveTabFromWorkflow();
            } catch (error) {
                this.workflowError = 'Unable to load workflow state.';
            } finally {
                this.workflowLoading = false;
            }
        },
        async handleCreatedModel(payload) {
            const inferredEmail = this.inferEmailFromPayload(payload);
            if (inferredEmail) {
                this.rememberParticipantLookupEmail(inferredEmail);
            }
            const participantHash = this.extractParticipantHash(payload);
            const participant = this.extractParticipant(payload);
            if (participantHash) {
                this.selectedParticipantHash = participantHash;
                this.saveLocalHashedIds({
                    participant_hash: participantHash,
                    participant: participant || {
                        name: payload?.data?.response_data?.name || payload?.response_data?.name || 'Participant',
                        email: inferredEmail || null,
                    },
                });
            }
            this.$emit('createdModel', payload);
            await this.loadWorkflow();
        },
        setActiveTabFromWorkflow() {
            if (!this.workflowSteps?.length) return;
            const preferred = this.workflowState?.current_step_id
                ? this.workflowSteps.find((step) => step.id === this.workflowState.current_step_id)
                : null;
            const available = this.workflowSteps.find((step) => step.status === 'available');
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
        isFormOpen(form) {
            if (!form) return false;
            const open_from = form.open_from ?? form.config?.open_from;
            const open_to = form.open_to ?? form.config?.open_to;
            if (!open_from || !open_to) return false;
            const now = new Date();
            const openFrom = new Date(open_from);
            const openTo = new Date(open_to);
            if (isNaN(openFrom.getTime()) || isNaN(openTo.getTime())) return false;
            return now >= openFrom && now <= openTo;
        },
        styleFor(key) {
            const token = this.resolvedStyleTokens?.[key];
            const textColorToken = this.resolvedStyleTokens?.[`${key}-text-color`];
            const textShadowToken = this.resolvedStyleTokens?.['form-text-shadow'];
            const styles = {};
            if (token && token.value) {
                if (token.mode === 'image') {
                    styles.backgroundImage = `url(${token.value})`;
                    styles.backgroundSize = 'cover';
                    styles.backgroundPosition = 'center';
                    styles.backgroundRepeat = 'no-repeat';
                } else if (token.mode === 'color') {
                    styles.backgroundColor = token.value;
                }
            }
            if (textColorToken?.value) {
                styles.color = textColorToken.value;
            }
            if (textShadowToken?.value) {
                styles.textShadow = textShadowToken.value;
            }
            return styles;
        },
        isFormFull(formType) {
            const form = this.getStep(formType) ?? this.whatForm(formType);
            if (!form) return false;
            const maxSlots = form?.max_slots ?? this.data?.max_slots ?? null;
            const responsesCount = form?.responses_count ?? 0;
            return !!maxSlots && maxSlots > 0 && responsesCount >= maxSlots;
        },
        getStepMessage(step) {
            if (!step) return 'This step is not available';
            switch (step.status) {
                case 'locked':
                    return 'Complete the previous step to continue';
                case 'not_yet_open':
                    return step.open_from 
                        ? `Available on ${this.formatDateTime(step.open_from)}`
                        : 'Not yet available';
                case 'expired':
                    return 'Closed on ' + (step.open_to ? this.formatDateTime(step.open_to) : 'an earlier date');
                case 'full':
                    return 'No slots available';
                case 'disabled':
                    return 'Currently disabled';
                case 'hidden':
                    return 'Not available';
                case 'completed':
                    return 'Already completed';
                default:
                    return 'Not available';
            }
        },
        formatDateTime(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (error) {
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
            if (!step) return '';
            const titles = {
                'preregistration': 'Pre-register Now!',
                'registration': 'Registration',
                'feedback': 'Feedback Form',
                'pretest': 'Pre-Test',
                'posttest': 'Post-Test',
            };
            return titles[step.form_type] || step.name || step.form_type.replace(/_/g, ' ');
        },
        getDescription(identifier) {
            const step = this.getStep(identifier);
            return step?.description || '';
        },
        useLegacyComponent(formType) {
            const step = this.getStep(formType);
            if (this.hasDynamicSchema(step)) return false;
            const legacyTypes = ['preregistration', 'preregistration_biotech', 'preregistration_quizbee', 'registration', 'feedback'];
            return legacyTypes.includes(formType);
        },
    }
}
</script>

<template>
    <div v-if="!!data" class="rounded-md bg-gray-50 dark:bg-gray-900 pb-20 md:pb-0">
        <!-- Mobile Header (Sticky) -->
        <div class="md:hidden sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">{{ data.event_id }}</span>
                </div>
                <h1 class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ data.title }}</h1>
            </div>
            <button 
                v-if="workflowTabs.length > 1"
                @click="showMobileMenu = !showMobileMenu"
                class="p-2 -mr-2 text-gray-600 dark:text-gray-300"
            >
                <LuMenu class="w-5 h-5" />
            </button>
        </div>

        <!-- Mobile Step Menu (Sheet) -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="showMobileMenu && workflowTabs.length > 1"
                class="fixed inset-0 z-40 bg-black/50 md:hidden"
                @click="showMobileMenu = false"
            >
                <div 
                    class="absolute right-0 top-0 h-full w-64 bg-white dark:bg-gray-800 shadow-xl p-4"
                    @click.stop
                >
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <span class="font-semibold text-gray-900 dark:text-white">Steps</span>
                        <button @click="showMobileMenu = false" class="p-1">
                            <LuX class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                    <div class="space-y-2">
                        <button
                            v-for="tab in workflowTabs"
                            :key="tab.key"
                            @click="activeTab = tab.key; showMobileMenu = false"
                            class="w-full text-left px-3 py-3 rounded-lg text-sm transition-colors flex items-center gap-3"
                            :class="activeTab === tab.key ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'"
                            :disabled="tab.disabled"
                        >
                            <div 
                                class="w-2 h-2 rounded-full"
                                :class="tab.status === 'available' ? 'bg-green-500' : tab.status === 'completed' ? 'bg-blue-500' : 'bg-gray-300'"
                            />
                            {{ tab.label }}
                            <span v-if="tab.disabled" class="ml-auto text-xs text-gray-400">Locked</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Main Container -->
        <div class="max-w-2xl mx-auto p-2 md:p-4 space-y-4">
            
            <!-- Event Header Card -->
            <div 
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                :style="styleFor('form-background')"
            >
                <!-- Header Banner -->
                <div 
                    class="px-4 md:px-6 py-4 md:py-6"
                    :style="styleFor('form-header-box')"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl md:text-2xl font-bold leading-tight" :style="{ color: resolvedStyleTokens?.['form-header-box-text-color']?.value }">
                                {{ data.title }}
                            </h2>
                            <p class="mt-1 text-sm opacity-90" :style="{ color: resolvedStyleTokens?.['form-header-box-text-color']?.value }">
                                {{ data.description }}
                            </p>
                        </div>
                        <div 
                            class="flex-shrink-0 text-center px-3 py-2 rounded-lg bg-white/20 backdrop-blur"
                        >
                            <span class="block text-2xl font-black leading-none">#{{ data.event_id }}</span>
                            <span class="text-xs opacity-75">ID</span>
                        </div>
                    </div>
                </div>

                <!-- Event Status Bar -->
                <div class="px-4 md:px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': eventState === 'ongoing',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300': eventState === 'upcoming',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': eventState === 'expired'
                            }"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="{
                                'bg-green-500 animate-pulse': eventState === 'ongoing',
                                'bg-amber-500': eventState === 'upcoming',
                                'bg-red-500': eventState === 'expired'
                            }" />
                            {{ eventState === 'ongoing' ? 'Live' : eventState === 'upcoming' ? 'Upcoming' : 'Ended' }}
                        </span>
                    </div>
                    <div class="text-sm font-mono font-medium text-gray-700 dark:text-gray-300">
                        {{ countdownDisplay }}
                    </div>
                </div>

                <!-- Date/Time Info -->
                <div class="grid grid-cols-2 divide-x divide-gray-200 dark:divide-gray-700 border-t border-gray-200 dark:border-gray-700">
                    <div class="p-3 md:p-4 text-center" :style="styleFor('form-time-from')">
                        <p class="text-xs opacity-75 mb-1" :style="{ color: resolvedStyleTokens?.['form-time-from-text-color']?.value }">Starts</p>
                        <p class="font-semibold text-sm" :style="{ color: resolvedStyleTokens?.['form-time-from-text-color']?.value }">
                            {{ formatDate(data.date_from) }}
                        </p>
                        <p class="text-xs opacity-75" :style="{ color: resolvedStyleTokens?.['form-time-from-text-color']?.value }">
                            {{ formatTime(data.time_from) }}
                        </p>
                    </div>
                    <div class="p-3 md:p-4 text-center" :style="styleFor('form-time-to')">
                        <p class="text-xs opacity-75 mb-1" :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">Ends</p>
                        <p class="font-semibold text-sm" :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                            {{ formatDate(data.date_to) }}
                        </p>
                        <p class="text-xs opacity-75" :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                            {{ formatTime(data.time_to) }}
                        </p>
                    </div>
                </div>

                <!-- Venue & Details -->
                <div v-if="data.venue" class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-start gap-2">
                        <LuMapPin class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">{{ data.venue }}</p>
                            <p v-if="data.details" class="mt-1 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ data.details }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Slots Info -->
                <div v-if="currentMaxSlots && currentMaxSlots > 0" class="px-4 md:px-6 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ slotsAvailable }} of {{ currentMaxSlots }} slots available
                    </span>
                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div 
                            class="h-full rounded-full transition-all duration-500"
                            :class="slotsAvailable === 0 ? 'bg-red-500' : slotsAvailable < currentMaxSlots * 0.2 ? 'bg-amber-500' : 'bg-green-500'"
                            :style="{ width: `${(slotsAvailable / currentMaxSlots) * 100}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Status Alerts -->
            <div 
                v-if="data.is_suspended" 
                class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-start gap-3"
            >
                <LuAlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="font-medium text-amber-800 dark:text-amber-200">Event Temporarily Unavailable</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">This event is currently suspended and not accepting responses.</p>
                </div>
            </div>

            <div 
                v-else-if="isExpired" 
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3"
            >
                <LuXCircle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="font-medium text-red-800 dark:text-red-200">Event Has Ended</p>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">This event is no longer accepting responses.</p>
                </div>
            </div>

            <!-- Participant Selector -->
            <div 
                v-if="participantHashes?.length && !data.is_suspended && !isExpired" 
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4"
            >
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Continue as</label>
                <div class="flex items-center gap-2">
                    <custom-dropdown
                        @selectedChange="onParticipantHashChange"
                        :value="selectedParticipantHash"
                        :options="[
                            { name: null, label: 'New participant' },
                            ...storedLocalHashedIds.map(item => ({
                                name: item.participant_hash,
                                label: item.participant.name
                            })),
                        ]"
                        :withAllOption="false"
                        class="flex-1"
                    />
                    <button
                        v-if="selectedParticipantHash"
                        @click="clearParticipant"
                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    >
                        <LuLogOut class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Participant Verification Flow -->
            <div 
                v-if="activeStep?.status === 'available' && participantWorkflowEnabled && participantVerificationEnabled && requiresParticipant(activeTab) && !selectedParticipantHash && !data.is_suspended && !isExpired"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
                <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <LuShield class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100">Verify Your Registration</h3>
                    </div>
                </div>
                
                <div class="p-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        This step requires a registered participant profile. Have you used this form before?
                    </p>

                    <!-- Choice Buttons -->
                    <div v-if="!participantFlowChoice" class="grid grid-cols-2 gap-3">
                        <button
                            @click="setParticipantFlowChoice('yes')"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all"
                        >
                            <LuUserCheck class="w-8 h-8 text-blue-600" />
                            <span class="font-medium text-sm">Yes, I have</span>
                        </button>
                        <button
                            @click="setParticipantFlowChoice('no')"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-green-500 dark:hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all"
                        >
                            <LuUserPlus class="w-8 h-8 text-green-600" />
                            <span class="font-medium text-sm">No, I'm new</span>
                        </button>
                    </div>

                    <!-- Email Lookup -->
                    <div v-if="participantFlowChoice === 'yes'" class="space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Enter your registered email address:</p>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <LuMail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="participantLookupEmail"
                                    type="email"
                                    placeholder="your@email.com"
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm"
                                    @keyup.enter="lookupRegisteredParticipant"
                                />
                            </div>
                            <button
                                @click="lookupRegisteredParticipant"
                                :disabled="participantLookupLoading"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                            >
                                <LuLoader2 v-if="participantLookupLoading" class="w-4 h-4 animate-spin" />
                                <LuSearch v-else class="w-4 h-4" />
                                <span class="hidden sm:inline">Find</span>
                            </button>
                        </div>
                    </div>

                    <!-- Go to Preregistration -->
                    <div v-if="participantFlowChoice === 'no'" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Complete preregistration first:</p>
                        <button
                            @click="goToPreregistrationStep"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm transition-colors"
                        >
                            Go to Preregistration
                        </button>
                    </div>

                    <!-- Messages -->
                    <div v-if="participantLookupSuccess" class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-sm">
                        <LuCheckCircle class="w-4 h-4 flex-shrink-0" />
                        {{ participantLookupSuccess }}
                    </div>
                    <div v-if="participantLookupError" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg text-sm">
                        <LuAlertCircle class="w-4 h-4 flex-shrink-0" />
                        {{ participantLookupError }}
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="workflowLoading" class="flex items-center justify-center gap-2 py-8 text-gray-500 dark:text-gray-400">
                <LuLoader2 class="w-5 h-5 animate-spin" />
                <span class="text-sm">Loading forms...</span>
            </div>

            <!-- Error State -->
            <div v-if="workflowError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
                <LuAlertCircle class="w-6 h-6 text-red-600 dark:text-red-400 mx-auto mb-2" />
                <p class="text-sm text-red-700 dark:text-red-300">{{ workflowError }}</p>
            </div>

            <!-- Desktop Tab Navigation -->
            <div v-if="workflowTabs.length > 1 && !workflowLoading" class="hidden md:block">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-1 flex gap-1 overflow-x-auto">
                    <button
                        v-for="tab in workflowTabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="flex-1 min-w-0 px-3 py-2.5 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-2"
                        :class="activeTab === tab.key 
                            ? 'bg-blue-600 text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        :disabled="tab.disabled"
                    >
                        <div 
                            class="w-2 h-2 rounded-full"
                            :class="tab.status === 'available' ? 'bg-green-400' : tab.status === 'completed' ? 'bg-blue-400' : 'bg-gray-400'"
                        />
                        <span class="truncate">{{ tab.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Step Indicator -->
            <div v-if="workflowTabs.length > 1 && !workflowLoading" class="md:hidden flex items-center justify-between px-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Step {{ workflowTabs.findIndex(t => t.key === activeTab) + 1 }} of {{ workflowTabs.length }}
                </span>
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                    {{ workflowTabs.find(t => t.key === activeTab)?.label }}
                </span>
            </div>

            <!-- Form Content -->
            <div v-if="activeTab && !workflowLoading" class="space-y-4">
                <!-- Step Countdown -->
                <div 
                    v-if="getStepCountdownMeta(getStep(activeTab))"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between"
                >
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ getStepCountdownMeta(getStep(activeTab)).label }}
                    </span>
                    <span 
                        class="font-mono font-medium"
                        :class="getStepCountdownMeta(getStep(activeTab)).label === 'Closes in' ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400'"
                    >
                        {{ getStepCountdownMeta(getStep(activeTab)).value }}
                    </span>
                </div>

                <!-- Form Container -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Form Header -->
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ getStepTitle(activeTab) }}</h3>
                        <p v-if="getDescription(activeTab)" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ getDescription(activeTab) }}
                        </p>
                    </div>

                    <!-- Form Content -->
                    <div class="p-4">
                        <!-- Dynamic Form Renderer -->
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
                                @createdModel="handleCreatedModel"
                            />
                            
                            <!-- Legacy Components -->
                            <preregistration-card
                                v-else-if="getStep(activeTab)?.form_type === 'preregistration'"
                                :event-id="getRequirementFormId(activeTab)"
                                :config="getStep(activeTab)"
                                @createdModel="handleCreatedModel"
                            />
                            <preregistration-quiz-bee-card
                                v-else-if="getStep(activeTab)?.form_type === 'preregistration_biotech'"
                                :event-id="getRequirementFormId(activeTab)"
                                :config="getStep(activeTab)"
                                @createdModel="handleCreatedModel"
                            />
                            <preregistration-quizbee-team-card
                                v-else-if="getStep(activeTab)?.form_type === 'preregistration_quizbee'"
                                :event-id="getRequirementFormId(activeTab)"
                                :config="getStep(activeTab)"
                                @createdModel="handleCreatedModel"
                            />
                            <registration-card
                                v-else-if="getStep(activeTab)?.form_type === 'registration'"
                                :event-id="getRequirementFormId(activeTab)"
                                :participant-id="getParticipantIdForStep(activeTab)"
                                :config="getStep(activeTab)"
                                @createdModel="handleCreatedModel"
                            />
                            <feedback-card
                                v-else-if="getStep(activeTab)?.form_type === 'feedback'"
                                :event-id="getRequirementFormId(activeTab)"
                                :participant-id="getParticipantIdForStep(activeTab)"
                                :config="getStep(activeTab)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>

                        <!-- Unavailable State -->
                        <div v-else class="text-center py-8">
                            <div 
                                class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                                :class="{
                                    'bg-gray-100 dark:bg-gray-700': activeStep?.status === 'locked',
                                    'bg-amber-100 dark:bg-amber-900/30': activeStep?.status === 'not_yet_open',
                                    'bg-red-100 dark:bg-red-900/30': activeStep?.status === 'expired' || activeStep?.status === 'full',
                                    'bg-blue-100 dark:bg-blue-900/30': activeStep?.status === 'completed'
                                }"
                            >
                                <component 
                                    :is="{
                                        'locked': 'LuLock',
                                        'not_yet_open': 'LuClock',
                                        'expired': 'LuXCircle',
                                        'full': 'LuUsers',
                                        'completed': 'LuCheckCircle',
                                        'disabled': 'LuBan'
                                    }[activeStep?.status] || 'LuLock'"
                                    class="w-8 h-8"
                                    :class="{
                                        'text-gray-500 dark:text-gray-400': activeStep?.status === 'locked',
                                        'text-amber-600 dark:text-amber-400': activeStep?.status === 'not_yet_open',
                                        'text-red-600 dark:text-red-400': activeStep?.status === 'expired' || activeStep?.status === 'full',
                                        'text-blue-600 dark:text-blue-400': activeStep?.status === 'completed'
                                    }"
                                />
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white mb-1">
                                {{ getStepMessage(getStep(activeTab)) }}
                            </p>
                            <p v-if="activeStep?.status === 'locked'" class="text-sm text-gray-500 dark:text-gray-400">
                                Complete previous steps to unlock this form
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>