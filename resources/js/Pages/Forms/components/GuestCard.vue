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
        };
    },
    computed: {
        resolvedStyleTokens() {
            return mergeFormStyleTokens(this.data?.style_tokens);
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
    },
    mounted() {
        this.startCountdown();
        this.startFormCountdownTicker();
        this.initializeParticipantContext();
        this.hydrateParticipantLookupEmail();
        this.loadWorkflow();
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
            this.persistParticipantContext(newValue);
        },
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        clearInterval(this.formCountdownIntervalId);
    },
    methods: {
        normalizeParticipantHash(value) {
            if (!value || typeof value !== 'string') {
                return null;
            }

            const normalized = value.trim();
            return normalized !== '' ? normalized : null;
        },
        getParticipantHashFromUrl() {
            if (typeof window === 'undefined') {
                return null;
            }

            const params = new URLSearchParams(window.location.search);
            return this.normalizeParticipantHash(
                params.get('participant') || params.get('participant_id') || params.get('participant_hash')
            );
        },
        getParticipantHashFromSession() {
            if (!this.data?.event_id || typeof sessionStorage === 'undefined') {
                return null;
            }

            return this.normalizeParticipantHash(sessionStorage.getItem(`event_participant_hash_${this.data.event_id}`));
        },
        persistParticipantContext(hash) {
            if (!this.data?.event_id || typeof window === 'undefined') {
                return;
            }

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
            const sessionHash = this.getParticipantHashFromSession();
            const localHash = this.participantHashes?.slice(-1)?.[0] ?? null;

            this.selectedParticipantHash = urlHash || sessionHash || localHash || null;
        },
        startFormCountdownTicker() {
            this.formCountdownNow = Date.now();
            this.formCountdownIntervalId = setInterval(() => {
                this.formCountdownNow = Date.now();
            }, 1000);
        },
        parseDateTimeValue(value) {
            if (!value) {
                return null;
            }

            const parsed = new Date(value);
            if (!Number.isNaN(parsed.getTime())) {
                return parsed;
            }

            if (typeof value === 'string') {
                const fallback = new Date(value.replace(' ', 'T'));
                if (!Number.isNaN(fallback.getTime())) {
                    return fallback;
                }
            }

            return null;
        },
        formatCountdownDuration(milliseconds) {
            if (!milliseconds || milliseconds <= 0) {
                return '0d 0h 0m 0s';
            }

            const totalSeconds = Math.floor(milliseconds / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            return `${days}d ${hours}h ${minutes}m ${seconds}s`;
        },
        getStepCountdownMeta(step) {
            if (!step) {
                return null;
            }

            const now = this.formCountdownNow;
            const openFrom = this.parseDateTimeValue(step.open_from ?? step.config?.open_from);
            const openTo = this.parseDateTimeValue(step.open_to ?? step.config?.open_to);

            if (step.status === 'not_yet_open' && openFrom) {
                const remaining = openFrom.getTime() - now;
                if (remaining > 0) {
                    return {
                        label: 'Opens in',
                        value: this.formatCountdownDuration(remaining),
                    };
                }
            }

            if (step.status === 'available' && openTo) {
                const remaining = openTo.getTime() - now;
                if (remaining > 0) {
                    return {
                        label: 'Closes in',
                        value: this.formatCountdownDuration(remaining),
                    };
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
        requiresParticipant(formType) {
            const step = formType ? this.getStep(formType) : this.activeStep;

            if (step?.requires_participant_context === true) {
                return true;
            }

            const explicitToggle = step?.form_config?.require_participant_verification;

            if (typeof explicitToggle === 'boolean') {
                return explicitToggle;
            }

            const exempt = ['preregistration', 'preregistration_biotech', 'preregistration_quizbee'];
            return !exempt.includes(formType);
        },
        canRenderForm(formType) {
            if (!this.requiresParticipant(formType)) {
                return true;
            }

            return !!this.selectedParticipantHash;
        },
        getParticipantIdForStep(stepIdentifier) {
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
            if (!value || typeof value !== 'string') {
                return null;
            }

            const normalized = value.trim().toLowerCase();
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized);

            return isValid ? normalized : null;
        },
        getStoredParticipantByHash(hash) {
            if (!hash) {
                return null;
            }

            return this.storedLocalHashedIds.find((item) => item?.participant_hash === hash) || null;
        },
        rememberParticipantLookupEmail(email) {
            const normalized = this.normalizeEmail(email);
            if (!normalized) {
                return;
            }

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
            if (directEmail) {
                return directEmail;
            }

            const responseData = payload?.data?.response_data ?? payload?.response_data ?? {};
            const entries = Object.entries(responseData);

            for (const [key, rawValue] of entries) {
                if (typeof rawValue !== 'string') {
                    continue;
                }

                const maybeEmail = this.normalizeEmail(rawValue);
                if (!maybeEmail) {
                    continue;
                }

                if (key.toLowerCase().includes('email')) {
                    return maybeEmail;
                }
            }

            for (const [, rawValue] of entries) {
                if (typeof rawValue !== 'string') {
                    continue;
                }

                const maybeEmail = this.normalizeEmail(rawValue);
                if (maybeEmail) {
                    return maybeEmail;
                }
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
                this.participantLookupSuccess = 'Registration found. Continuing with your saved participant profile.';
                this.rememberParticipantLookupEmail(data?.participant?.email || normalizedEmail);

                this.saveLocalHashedIds({
                    participant_hash: data.participant_hash,
                    participant: data.participant,
                });

                await this.loadWorkflow();
            } catch (error) {
                this.participantLookupError = 'Unable to validate your registration right now. Please try again.';
            } finally {
                this.participantLookupLoading = false;
            }
        },
        async loadWorkflow() {
            if (!this.data?.event_id) {
                return;
            }

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
        handleCreatedModel(payload) {
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
            this.loadWorkflow();
        },
        setActiveTabFromWorkflow() {
            if (!this.workflowSteps?.length) {
                return;
            }

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
            if (!form) {
                return false;
            }
            const open_from = form.open_from ?? form.config?.open_from;
            const open_to = form.open_to ?? form.config?.open_to;
            if (!open_from || !open_to) {
                return false;
            }
            const now = new Date();
            const openFrom = new Date(open_from);
            const openTo = new Date(open_to);
            if (isNaN(openFrom.getTime()) || isNaN(openTo.getTime())) {
                return false;
            }
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
            if (!step) {
                return 'This step is not available';
            }
            switch (step.status) {
                case 'locked':
                    return 'Complete the previous step to continue';
                case 'not_yet_open':
                    return step.open_from 
                        ? `This form will be available on ${this.formatDateTime(step.open_from)}`
                        : 'This form is not yet available';
                case 'expired':
                    return 'This form is no longer available. It closed on ' + (step.open_to ? this.formatDateTime(step.open_to) : 'an earlier date');
                case 'full':
                    return 'Maximum number of responses reached. No more slots available.';
                case 'disabled':
                    return 'This form is currently disabled';
                case 'hidden':
                    return 'This form is not available at this time';
                case 'completed':
                    return 'You have already completed this form';
                default:
                    return 'This form is not available';
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
                console.error('Error formatting date:', dateString, error);
                return dateString;
            }
        },
        /**
         * Check if a step has a dynamic field schema
         */
        hasDynamicSchema(step) {
            return step?.field_schema && Array.isArray(step.field_schema) && step.field_schema.length > 0;
        },
        /**
         * Get the field schema for a step
         */
        getFieldSchema(identifier) {
            const step = this.getStep(identifier);
            return step?.field_schema || [];
        },
        /**
         * Get step title for dynamic form
         */
        getStepTitle(identifier) {
            const step = this.getStep(identifier);
            if (!step) return '';
            
            // Try to get a human-readable title
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
        /**
         * Check if form type should use legacy hardcoded component
         */
        useLegacyComponent(formType) {
            // Use legacy components only if no dynamic schema is available
            const step = this.getStep(formType);
            if (this.hasDynamicSchema(step)) {
                return false;
            }
            // Legacy form types that have hardcoded Vue components
            const legacyTypes = ['preregistration', 'preregistration_biotech', 'preregistration_quizbee', 'registration', 'feedback'];
            return legacyTypes.includes(formType);
        },
    }
}
</script>

<template>
    <div v-if="!!data" id="form-background" class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl drop-shadow-lg mx-4 lg:mx-0 my-4 md:mt-0" :style="styleFor('form-background')">
        <div id="form-header-box" class="flex flex-row bg-AB gap-2 text-white p-2 rounded-md justify-between shadow py-4" :style="styleFor('form-header-box')">
            <div class="flex flex-col justify-center drop-shadow">
                <label class="leading-tight font-semibold text-2xl">{{ data.title }}</label>
                <p class="text-xs leading-snug break-all">
                    {{ data.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center border-l pl-2">
                <label class="text-xl leading-none font-[1000]">{{ data.event_id }}</label>
                <span class="text-[0.6rem] leading-none ">Form ID</span>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center p-2 rounded-md  drop-shadow">
            <span v-if="isExpired" class="text-sm uppercase leading-none text-red-600">Event has ended</span>
            <span v-else class="text-sm uppercase leading-none">Event starts in </span>
            <label class="leading-none font-bold text-4xl" :class="{'text-red-600' : isExpired}">{{ countdownDisplay }}</label>
        </div>

        <div class="flex relative items-center drop-shadow">
            <div id="form-time-from" class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full" :style="styleFor('form-time-from')">
                <label class="font-bold">{{ formatTime(data.time_from) }} {{ formatDate(data.date_from) }}</label>
                <span class="text-xs">Start</span>
            </div>
            <div id="form-time-between" class="flex w-full bg-AB max-w-[2rem]" :style="styleFor('form-time-between')">
                <label class="m-auto text-white font-bold">TO</label>
            </div>
            <div id="form-time-to"  class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full" :style="styleFor('form-time-to')">
                <label class="font-bold">{{ formatTime(data.time_to) }} {{ formatDate(data.date_to) }}</label>
                <span class="text-xs">End</span>
            </div>
        </div>

        <div  class="px-1">
            <div v-if="data.venue">
                <span class="font-bold uppercase">Venue: </span>
                <label class="break-all overflow-ellipsis">{{ data.venue }}</label>
            </div>
            <p v-if="data.venue" class="text-justify break-all">{{ data.details }}</p>
        </div>
        <div v-if="currentMaxSlots && currentMaxSlots > 0" class="px-1 flex gap-2 justify-between">
            <div>
                <span class="font-bold uppercase">Max Slots: </span>
                <label>{{ currentMaxSlots }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">Slots Available: </span>
                <span v-if="isFormFull(activeTab)" class="text-red-600">FULL</span>
                <label v-else>{{ slotsAvailable }}</label>
            </div>
        </div>
        <div v-show="data.is_suspended" v-if="data.is_suspended" class="flex flex-col border-t p-2 bg-yellow-300 w-full min-w-full rounded-md min-h-[3rem]">
            <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
            <span class="leading-none text-xs text-center">unable to accept registration</span>
        </div>
        <div v-show="isExpired" v-else-if="isExpired" class="flex flex-col border-t p-2 bg-yellow-300 w-full min-w-full rounded-md min-h-[3rem]">
            <span class="font-bold uppercase leading-none text-center">This Form has expired</span>
            <span class="leading-none text-xs text-center">unable to accept registration</span>
        </div>
        <div v-else class="flex flex-col gap-1">
            <div v-if="participantHashes?.length" class="flex items-center gap-2 px-2 py-2 bg-white rounded-md border">
                <label class="text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Continue as</label>
                <custom-dropdown
                    @selectedChange="onParticipantHashChange"
                    :value="selectedParticipantHash"
                    :options="[
                        {
                            name: null,
                            label: 'New participant'
                        },
                        ...storedLocalHashedIds.map(item => ({
                            name: item.participant_hash,
                            label: item.participant.name
                        })),
                        
                    ]"
                    :withAllOption="false"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
            </div>
            <div
                v-if="activeStep?.status === 'available' && requiresParticipant(activeTab) && !selectedParticipantHash"
                class="px-3 py-3 bg-white rounded-md border flex flex-col gap-2"
            >
                <label class="text-xs font-semibold uppercase text-gray-600">Participant verification</label>
                <p class="text-sm text-gray-700 leading-snug">
                    This step requires a registered participant. Have you used this form system before?
                </p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                        @click="setParticipantFlowChoice('yes')"
                    >
                        Yes, I used this before
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                        @click="setParticipantFlowChoice('no')"
                    >
                        No, I’m new here
                    </button>
                </div>

                <div v-if="participantFlowChoice === 'yes'" class="flex flex-col gap-2">
                    <p class="text-xs text-gray-600">Enter the email you used before. We’ll check it against participant records.</p>
                    <div class="flex gap-2">
                        <input
                            v-model="participantLookupEmail"
                            type="email"
                            class="w-full rounded-md border-gray-300 text-sm"
                            placeholder="Enter your registered email"
                        />
                        <button
                            type="button"
                            class="px-3 py-1.5 text-sm bg-AB text-white rounded-md hover:bg-AB/90 disabled:opacity-50"
                            :disabled="participantLookupLoading"
                            @click="lookupRegisteredParticipant"
                        >
                            {{ participantLookupLoading ? 'Searching...' : 'Find' }}
                        </button>
                    </div>
                </div>

                <div v-if="participantFlowChoice === 'no'" class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-600">Complete preregistration first to continue this step.</p>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs bg-AB text-white rounded-md hover:bg-AB/90"
                        @click="goToPreregistrationStep"
                    >
                        Go to preregistration
                    </button>
                </div>

                <p v-if="participantLookupSuccess" class="text-xs text-green-700">{{ participantLookupSuccess }}</p>
                <p v-if="participantLookupError" class="text-xs text-red-600">{{ participantLookupError }}</p>
            </div>
            <div v-if="workflowLoading" class="text-sm text-gray-500 px-2 text-center w-full flex gap-1 justify-center"><LoaderIcon /> Loading Attached Forms</div>
            <div v-if="workflowError" class="text-sm text-red-600 px-2 text-center w-full">{{ workflowError }}</div>

            <TabNavigation
                v-if="workflowTabs.length"
                v-model="activeTab"
                :tabs="workflowTabs"
            >
                <template #default="{ activeKey }">
                    <div
                        v-if="getStepCountdownMeta(getStep(activeKey))"
                        class="bg-white px-3 pt-3 flex items-center justify-between"
                    >
                        <span class="text-xs uppercase text-gray-600">
                            {{ getStepCountdownMeta(getStep(activeKey)).label }}
                        </span>
                        <span class="text-xs" :class="{ 'text-red-600': getStepCountdownMeta(getStep(activeKey)).label === 'Closes in' }">
                            {{ getStepCountdownMeta(getStep(activeKey)).value }}
                        </span>
                    </div>
                    <div v-if="getStep(activeKey)?.form_type === 'preregistration'">
                        <template v-if="activeStep?.status === 'available'">
                            <DynamicFormRenderer
                                v-if="hasDynamicSchema(activeStep)"
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || 'preregistration'"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                            <preregistration-card
                                v-else
                                :event-id="getRequirementFormId(activeKey)"
                                :config="getStep(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep(activeKey)) }}
                        </div>
                    </div>

                    <div v-if="getStep(activeKey)?.form_type === 'preregistration_biotech'">
                        <template v-if="activeStep?.status === 'available'">
                            <DynamicFormRenderer
                                v-if="hasDynamicSchema(activeStep)"
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || 'preregistration_biotech'"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                            <preregistration-quiz-bee-card
                                v-else
                                :event-id="getRequirementFormId(activeKey)"
                                :config="getStep(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep(activeKey)) }}
                        </div>
                    </div>

                    <div v-if="getStep(activeKey)?.form_type === 'preregistration_quizbee'">
                        <template v-if="activeStep?.status === 'available'">
                            <DynamicFormRenderer
                                v-if="hasDynamicSchema(activeStep)"
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || 'preregistration_quizbee'"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                            <preregistration-quizbee-team-card
                                v-else
                                :event-id="getRequirementFormId(activeKey)"
                                :config="getStep(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep(activeKey)) }}
                        </div>
                    </div>

                    <div v-if="getStep(activeKey)?.form_type === 'registration'">
                        <template v-if="activeStep?.status === 'available' && canRenderForm(activeKey)">
                            <DynamicFormRenderer
                                v-if="hasDynamicSchema(activeStep)"
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || 'registration'"
                                :participant-id="getParticipantIdForStep(activeKey)"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                            <registration-card
                                v-else
                                :event-id="getRequirementFormId(activeKey)"
                                :participant-id="getParticipantIdForStep(activeKey)"
                                :config="getStep(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ activeStep?.status === 'available' ? 'Select or verify your participant profile to continue.' : getStepMessage(getStep(activeKey)) }}
                        </div>
                    </div>

                    <div v-if="getStep(activeKey)?.form_type === 'feedback'">
                        <template v-if="activeStep?.status === 'available' && canRenderForm(activeKey)">
                            <DynamicFormRenderer
                                v-if="hasDynamicSchema(activeStep)"
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || 'feedback'"
                                :participant-id="getParticipantIdForStep(activeKey)"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                            <feedback-card
                                v-else
                                :event-id="getRequirementFormId(activeKey)"
                                :participant-id="getParticipantIdForStep(activeKey)"
                                :config="getStep(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ activeStep?.status === 'available' ? 'Select or verify your participant profile to continue.' : getStepMessage(getStep(activeKey)) }}
                        </div>
                    </div>

                    <!-- Dynamic fallback for any other form types with field_schema -->
                    <div v-if="['preregistration', 'preregistration_biotech', 'preregistration_quizbee', 'registration', 'feedback'].indexOf(getStep(activeKey)?.form_type) === -1">
                        <template v-if="activeStep?.status === 'available' && hasDynamicSchema(activeStep) && canRenderForm(activeKey)">
                            <DynamicFormRenderer
                                :field-schema="getFieldSchema(activeKey)"
                                :event-id="getRequirementFormId(activeKey)"
                                :subform-type="getStep(activeKey)?.form_type || activeKey"
                                :participant-id="getParticipantIdForStep(activeKey)"
                                :config="getStep(activeKey)"
                                :title="getStepTitle(activeKey)"
                                :description="getDescription(activeKey)"
                                @createdModel="handleCreatedModel"
                            />
                        </template>
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ activeStep?.status === 'available' ? 'Select or verify your participant profile to continue.' : (getStepMessage(getStep(activeKey)) || 'This step type is not yet supported in the guest UI.') }}
                        </div>
                    </div>
                </template>
            </TabNavigation>

        </div>

    </div>
</template>

<style scoped>

</style>
