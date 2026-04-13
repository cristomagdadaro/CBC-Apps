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
import {
    MapPin, Clock, Calendar, Users, Hash, Timer,
    CircleCheck, CircleX, ChevronRight, Menu, X,
    Shield, UserCheck, UserPlus, Mail, Search,
    Loader2, AlertTriangle, AlertCircle, CheckCircle,
    XCircle, Lock, Ban, LogOut, ArrowRight, Sparkles,
    CalendarDays, Hourglass, Radio
} from 'lucide-vue-next';

export default {
    name: "GuestCard",
    components: {
        FeedbackCard, RegistrationCard, PreregistrationCard,
        PreregistrationQuizBeeCard, PreregistrationQuizbeeTeamCard,
        DynamicFormRenderer,
        MapPin, Clock, Calendar, Users, Hash, Timer,
        CircleCheck, CircleX, ChevronRight, Menu, X,
        Shield, UserCheck, UserPlus, Mail, Search,
        Loader2, AlertTriangle, AlertCircle, CheckCircle,
        XCircle, Lock, Ban, LogOut, ArrowRight, Sparkles,
        CalendarDays, Hourglass, Radio
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
            if (this.slotsAvailable === 0) return 'slot-full';
            const ratio = (this.slotsAvailable ?? 0) / (this.currentMaxSlots ?? 1);
            if (ratio <= 0.25) return 'slot-critical';
            if (ratio <= 0.5) return 'slot-low';
            return 'slot-ok';
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
        countdownParts() {
            const target = this.eventCountdownTargetAt;
            if (!target) return { d: '00', h: '00', m: '00', s: '00' };
            const remaining = target.getTime() - this.formCountdownNow;
            if (remaining <= 0) return { d: '00', h: '00', m: '00', s: '00' };
            const totalSeconds = Math.floor(remaining / 1000);
            const d = Math.floor(totalSeconds / 86400);
            const h = Math.floor((totalSeconds % 86400) / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            return {
                d: String(d).padStart(2, '0'),
                h: String(h).padStart(2, '0'),
                m: String(m).padStart(2, '0'),
                s: String(s).padStart(2, '0'),
            };
        },
        countdownDisplay() {
            const p = this.countdownParts;
            return `${p.d}d ${p.h}h ${p.m}m ${p.s}s`;
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
            if (this.isInitialized) this.persistParticipantContext(newValue);
        },
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        clearInterval(this.formCountdownIntervalId);
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        handleResize() { this.$forceUpdate(); },
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
                normalizedHash ? sessionStorage.setItem(sessionKey, normalizedHash) : sessionStorage.removeItem(sessionKey);
            }
            const url = new URL(window.location.href);
            normalizedHash ? url.searchParams.set('participant', normalizedHash) : url.searchParams.delete('participant');
            window.history.replaceState({}, '', url.toString());
        },
        initializeParticipantContext() {
            const urlHash = this.getParticipantHashFromUrl();
            const localHash = this.participantHashes?.slice(-1)?.[0] ?? null;
            this.selectedParticipantHash = urlHash || localHash || null;
        },
        startFormCountdownTicker() {
            this.formCountdownNow = Date.now();
            this.formCountdownIntervalId = setInterval(() => { this.formCountdownNow = Date.now(); }, 1000);
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
                if (remaining > 0) return { label: 'Opens in', value: this.formatCountdownDuration(remaining) };
            }
            if (step.status === 'available' && openTo) {
                const remaining = openTo.getTime() - now;
                if (remaining > 0) return { label: 'Closes in', value: this.formatCountdownDuration(remaining) };
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
            if (choice === 'yes') this.hydrateParticipantLookupEmail();
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
            if (preferredEmail) this.participantLookupEmail = preferredEmail;
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
                this.saveLocalHashedIds({ participant_hash: data.participant_hash, participant: data.participant });
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
            if (inferredEmail) this.rememberParticipantLookupEmail(inferredEmail);
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
            if (textColorToken?.value) styles.color = textColorToken.value;
            if (textShadowToken?.value) styles.textShadow = textShadowToken.value;
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
                case 'locked': return 'Complete the previous step to continue';
                case 'not_yet_open': return step.open_from ? `Available on ${this.formatDateTime(step.open_from)}` : 'Not yet available';
                case 'expired': return 'Closed on ' + (step.open_to ? this.formatDateTime(step.open_to) : 'an earlier date');
                case 'full': return 'No slots available';
                case 'disabled': return 'Currently disabled';
                case 'hidden': return 'Not available';
                case 'completed': return 'Already completed';
                default: return 'Not available';
            }
        },
        formatDateTime(dateString) {
            try {
                return new Date(dateString).toLocaleString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: true
                });
            } catch { return dateString; }
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
        getStepIcon(status) {
            const map = {
                locked: 'Lock',
                not_yet_open: 'Clock',
                expired: 'XCircle',
                full: 'Users',
                completed: 'CheckCircle',
                disabled: 'Ban',
            };
            return map[status] || 'Lock';
        }
    }
}
</script>

<template>
    <div v-if="!!data" class="pin-guest-card">

        <!-- ─── Mobile Header ─── -->
        <div class="mobile-header md:hidden">
            <div class="mobile-header-inner">
                <div class="mobile-id-chip">
                    <Hash :size="10" :stroke-width="2" />
                    {{ data.event_id }}
                </div>
                <h1 class="mobile-title">{{ data.title }}</h1>
            </div>
            <button
                v-if="workflowTabs.length > 1"
                @click="showMobileMenu = !showMobileMenu"
                class="mobile-menu-btn"
            >
                <Menu :size="18" :stroke-width="1.75" />
            </button>
        </div>

        <!-- ─── Mobile Drawer ─── -->
        <Transition name="drawer">
            <div
                v-if="showMobileMenu && workflowTabs.length > 1"
                class="drawer-overlay md:hidden"
                @click="showMobileMenu = false"
            >
                <div class="drawer-panel" @click.stop>
                    <div class="drawer-header">
                        <span class="drawer-title">Form Steps</span>
                        <button @click="showMobileMenu = false" class="drawer-close">
                            <X :size="16" :stroke-width="1.75" />
                        </button>
                    </div>
                    <div class="drawer-steps">
                        <button
                            v-for="(tab, i) in workflowTabs"
                            :key="tab.key"
                            @click="activeTab = tab.key; showMobileMenu = false"
                            class="drawer-step-btn"
                            :class="{ 'active': activeTab === tab.key, 'disabled': tab.disabled }"
                            :disabled="tab.disabled"
                        >
                            <span class="step-num">{{ i + 1 }}</span>
                            <span class="step-label">{{ tab.label }}</span>
                            <span
                                class="step-dot"
                                :class="`dot-${tab.status}`"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ─── Main layout ─── -->
        <div class="card-shell">

            <!-- ══ EVENT HEADER CARD ══ -->
            <div class="event-card" :style="styleFor('form-background')">

                <!-- Header block -->
                <div class="event-header" :style="styleFor('form-header-box')">
                    <div class="event-header-content">
                        <div class="event-meta">
                            <div class="event-id-badge">
                                <Hash :size="11" :stroke-width="2.5" />
                                <span>{{ data.event_id }}</span>
                            </div>
                            <h2 class="event-title" :style="{ color: resolvedStyleTokens?.['form-header-box-text-color']?.value }">
                                {{ data.title }}
                            </h2>
                            <p class="event-desc" :style="{ color: resolvedStyleTokens?.['form-header-box-text-color']?.value }">
                                {{ data.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status ribbon -->
                <div class="status-ribbon">
                    <div class="status-left">
                        <span class="status-badge" :class="`badge-${eventState}`">
                            <span v-if="eventState === 'ongoing'" class="live-ring">
                                <span class="live-ring-ping" />
                                <span class="live-ring-dot" />
                            </span>
                            <span v-else class="status-dot" :class="`dot-state-${eventState}`" />
                            {{ eventState === 'ongoing' ? 'Live' : eventState === 'upcoming' ? 'Upcoming' : 'Ended' }}
                        </span>
                    </div>
                    <div class="countdown-ticker">
                        <template v-if="eventCountdownTargetAt">
                            <div class="ticker-unit">
                                <span class="ticker-num">{{ countdownParts.d }}</span>
                                <span class="ticker-label">d</span>
                            </div>
                            <span class="ticker-sep">:</span>
                            <div class="ticker-unit">
                                <span class="ticker-num">{{ countdownParts.h }}</span>
                                <span class="ticker-label">h</span>
                            </div>
                            <span class="ticker-sep">:</span>
                            <div class="ticker-unit">
                                <span class="ticker-num">{{ countdownParts.m }}</span>
                                <span class="ticker-label">m</span>
                            </div>
                            <span class="ticker-sep">:</span>
                            <div class="ticker-unit">
                                <span class="ticker-num">{{ countdownParts.s }}</span>
                                <span class="ticker-label">s</span>
                            </div>
                        </template>
                        <span v-else class="ticker-ended">—</span>
                    </div>
                </div>

                <!-- Dates grid -->
                <div class="dates-grid">
                    <div class="date-cell" :style="styleFor('form-time-from')">
                        <div class="date-cell-label">
                            <CalendarDays :size="12" :stroke-width="1.75" />
                            Starts
                        </div>
                        <p class="date-cell-value" :style="{ color: resolvedStyleTokens?.['form-time-from-text-color']?.value }">
                            {{ formatDate(data.date_from) }}
                        </p>
                        <p class="date-cell-time" :style="{ color: resolvedStyleTokens?.['form-time-from-text-color']?.value }">
                            {{ formatTime(data.time_from) }}
                        </p>
                    </div>
                    <div class="date-divider" />
                    <div class="date-cell" :style="styleFor('form-time-to')">
                        <div class="date-cell-label">
                            <CalendarDays :size="12" :stroke-width="1.75" />
                            Ends
                        </div>
                        <p class="date-cell-value" :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                            {{ formatDate(data.date_to) }}
                        </p>
                        <p class="date-cell-time" :style="{ color: resolvedStyleTokens?.['form-time-to-text-color']?.value }">
                            {{ formatTime(data.time_to) }}
                        </p>
                    </div>
                </div>

                <!-- Venue -->
                <div v-if="data.venue" class="venue-row">
                    <MapPin :size="14" :stroke-width="1.75" class="venue-icon" />
                    <div class="venue-text">
                        <p class="venue-name">{{ data.venue }}</p>
                        <p v-if="data.details" class="venue-details">{{ data.details }}</p>
                    </div>
                </div>

                <!-- Slots -->
                <div v-if="currentMaxSlots && currentMaxSlots > 0" class="slots-row">
                    <div class="slots-info">
                        <Users :size="13" :stroke-width="1.75" class="slots-icon" />
                        <span class="slots-text">
                            <strong>{{ slotsAvailable }}</strong> of {{ currentMaxSlots }} slots available
                        </span>
                    </div>
                    <div class="slots-bar-track">
                        <div
                            class="slots-bar-fill"
                            :class="slotStatusClass"
                            :style="{ width: `${slotFillPercent}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- ══ STATUS ALERTS ══ -->
            <div v-if="data.is_suspended" class="alert alert-warning">
                <AlertTriangle :size="16" :stroke-width="1.75" class="alert-icon" />
                <div>
                    <p class="alert-title">Event Temporarily Unavailable</p>
                    <p class="alert-body">This event is currently suspended and not accepting responses.</p>
                </div>
            </div>

            <div v-else-if="isExpired" class="alert alert-danger">
                <XCircle :size="16" :stroke-width="1.75" class="alert-icon" />
                <div>
                    <p class="alert-title">Event Has Ended</p>
                    <p class="alert-body">This event is no longer accepting responses.</p>
                </div>
            </div>

            <!-- ══ PARTICIPANT SELECTOR ══ -->
            <div
                v-if="participantHashes?.length && !data.is_suspended && !isExpired"
                class="section-card"
            >
                <label class="field-label">Continue as</label>
                <div class="participant-row">
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
                    <button v-if="selectedParticipantHash" @click="clearParticipant" class="btn-ghost-danger">
                        <LogOut :size="15" :stroke-width="1.75" />
                    </button>
                </div>
            </div>

            <!-- ══ PARTICIPANT VERIFICATION ══ -->
            <div
                v-if="activeStep?.status === 'available' && participantWorkflowEnabled && participantVerificationEnabled && requiresParticipant(activeTab) && !selectedParticipantHash && !data.is_suspended && !isExpired"
                class="section-card verify-card"
            >
                <div class="verify-header">
                    <Shield :size="15" :stroke-width="1.75" class="verify-icon" />
                    <span>Verify Your Registration</span>
                </div>
                <p class="verify-body">This step requires a registered participant profile. Have you used this form before?</p>

                <div v-if="!participantFlowChoice" class="choice-grid">
                    <button @click="setParticipantFlowChoice('yes')" class="choice-btn choice-yes">
                        <UserCheck :size="22" :stroke-width="1.5" />
                        <span class="choice-label">Yes, I have</span>
                        <span class="choice-sub">Continue with my profile</span>
                    </button>
                    <button @click="setParticipantFlowChoice('no')" class="choice-btn choice-no">
                        <UserPlus :size="22" :stroke-width="1.5" />
                        <span class="choice-label">No, I'm new</span>
                        <span class="choice-sub">Start preregistration</span>
                    </button>
                </div>

                <div v-if="participantFlowChoice === 'yes'" class="email-lookup">
                    <p class="lookup-hint">Enter your registered email address:</p>
                    <div class="lookup-row">
                        <div class="input-with-icon">
                            <Mail :size="14" :stroke-width="1.75" class="input-icon" />
                            <input
                                v-model="participantLookupEmail"
                                type="email"
                                placeholder="your@email.com"
                                class="pin-input"
                                @keyup.enter="lookupRegisteredParticipant"
                            />
                        </div>
                        <button
                            @click="lookupRegisteredParticipant"
                            :disabled="participantLookupLoading"
                            class="btn-primary"
                        >
                            <Loader2 v-if="participantLookupLoading" :size="14" :stroke-width="1.75" class="spin" />
                            <Search v-else :size="14" :stroke-width="1.75" />
                            <span>Find</span>
                        </button>
                    </div>
                </div>

                <div v-if="participantFlowChoice === 'no'" class="prereg-nudge">
                    <p class="nudge-text">Complete preregistration first to get started.</p>
                    <button @click="goToPreregistrationStep" class="btn-success">
                        Go to Preregistration
                        <ArrowRight :size="13" :stroke-width="2" />
                    </button>
                </div>

                <div v-if="participantLookupSuccess" class="inline-alert inline-success">
                    <CheckCircle :size="13" :stroke-width="1.75" />
                    {{ participantLookupSuccess }}
                </div>
                <div v-if="participantLookupError" class="inline-alert inline-danger">
                    <AlertCircle :size="13" :stroke-width="1.75" />
                    {{ participantLookupError }}
                </div>
            </div>

            <!-- ══ LOADING ══ -->
            <div v-if="workflowLoading" class="loading-state">
                <Loader2 :size="18" :stroke-width="1.75" class="spin" />
                <span>Loading forms…</span>
            </div>

            <!-- ══ ERROR ══ -->
            <div v-if="workflowError" class="alert alert-danger text-center">
                <AlertCircle :size="16" :stroke-width="1.75" class="alert-icon" />
                <div>
                    <p class="alert-title">Failed to load</p>
                    <p class="alert-body">{{ workflowError }}</p>
                </div>
            </div>

            <!-- ══ TABS — Desktop ══ -->
            <div v-if="workflowTabs.length > 1 && !workflowLoading" class="tabs-bar hidden md:flex">
                <button
                    v-for="(tab, i) in workflowTabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="tab-btn"
                    :class="{ 'tab-active': activeTab === tab.key, 'tab-disabled': tab.disabled }"
                    :disabled="tab.disabled"
                >
                    <span class="tab-index">{{ i + 1 }}</span>
                    <span class="tab-dot" :class="`dot-${tab.status}`" />
                    <span class="tab-label">{{ tab.label }}</span>
                </button>
            </div>

            <!-- ══ STEP INDICATOR — Mobile ══ -->
            <div v-if="workflowTabs.length > 1 && !workflowLoading" class="step-indicator md:hidden">
                <span class="step-prog">{{ workflowTabs.findIndex(t => t.key === activeTab) + 1 }} / {{ workflowTabs.length }}</span>
                <span class="step-cur-label">{{ workflowTabs.find(t => t.key === activeTab)?.label }}</span>
            </div>

            <!-- ══ FORM CONTENT ══ -->
            <div v-if="activeTab && !workflowLoading">

                <!-- Step countdown banner -->
                <div v-if="getStepCountdownMeta(getStep(activeTab))" class="step-countdown">
                    <div class="step-countdown-inner">
                        <Hourglass :size="13" :stroke-width="1.75" />
                        <span>{{ getStepCountdownMeta(getStep(activeTab)).label }}</span>
                    </div>
                    <span
                        class="step-countdown-value"
                        :class="getStepCountdownMeta(getStep(activeTab)).label === 'Closes in' ? 'countdown-warn' : 'countdown-info'"
                    >
                        {{ getStepCountdownMeta(getStep(activeTab)).value }}
                    </span>
                </div>

                <!-- Form shell -->
                <div class="form-shell">
                    <div class="form-shell-header">
                        <h3 class="form-title">{{ getStepTitle(activeTab) }}</h3>
                        <p v-if="getDescription(activeTab)" class="form-desc">{{ getDescription(activeTab) }}</p>
                    </div>

                    <div class="form-body">
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

                        <!-- Unavailable state -->
                        <div v-else class="unavailable-state">
                            <div class="unavail-icon-ring" :class="`ring-${activeStep?.status}`">
                                <component
                                    :is="getStepIcon(activeStep?.status)"
                                    :size="24"
                                    :stroke-width="1.5"
                                    class="unavail-icon"
                                    :class="`icon-${activeStep?.status}`"
                                />
                            </div>
                            <p class="unavail-title">{{ getStepMessage(getStep(activeTab)) }}</p>
                            <p v-if="activeStep?.status === 'locked'" class="unavail-sub">
                                Complete previous steps to unlock this form.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* ─── Design Tokens ─── */
.pin-guest-card {
    --pin-green: #1a7a4a;
    --pin-green-light: #e8f5ee;
    --pin-green-muted: #b4d9c5;
    --pin-green-dark: #115233;
    --pin-surface: #ffffff;
    --pin-surface-2: #f8faf9;
    --pin-surface-3: #f2f5f3;
    --pin-border: #e2ebe6;
    --pin-border-strong: #c8d9d1;
    --pin-text: #111c16;
    --pin-text-2: #3d5448;
    --pin-text-3: #6b8578;
    --pin-text-4: #9ab4a8;
    --pin-red: #c0392b;
    --pin-red-light: #fdf0ee;
    --pin-amber: #b45309;
    --pin-amber-light: #fef9ee;
    --pin-blue: #1d5fa8;
    --pin-blue-light: #eef4fc;
    --pin-radius: 12px;
    --pin-radius-sm: 8px;
    --pin-radius-xs: 6px;
    --pin-font-mono: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;

    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    background: var(--pin-surface-2);
    padding-bottom: 5rem;
}

@media (min-width: 768px) {
    .pin-guest-card { padding-bottom: 0; }
}

/* ─── Mobile Header ─── */
.mobile-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: var(--pin-surface);
    border-bottom: 1px solid var(--pin-border);
    padding: 0.625rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.mobile-header-inner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
    flex: 1;
}
.mobile-id-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    background: var(--pin-green-light);
    color: var(--pin-green-dark);
    font-size: 10px;
    font-weight: 600;
    font-family: var(--pin-font-mono);
    padding: 3px 7px;
    border-radius: 999px;
    flex-shrink: 0;
    letter-spacing: 0.04em;
}
.mobile-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--pin-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mobile-menu-btn {
    padding: 6px;
    color: var(--pin-text-2);
    background: transparent;
    border: none;
    cursor: pointer;
    flex-shrink: 0;
    border-radius: var(--pin-radius-xs);
    transition: background 0.15s;
}
.mobile-menu-btn:hover { background: var(--pin-surface-3); }

/* ─── Drawer ─── */
.drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
    background: rgba(0,0,0,0.45);
}
.drawer-panel {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 260px;
    background: var(--pin-surface);
    padding: 1.25rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--pin-border);
    margin-bottom: 0.25rem;
}
.drawer-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--pin-text);
    letter-spacing: 0.03em;
    text-transform: uppercase;
}
.drawer-close {
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--pin-text-3);
    padding: 4px;
    border-radius: var(--pin-radius-xs);
}
.drawer-steps { display: flex; flex-direction: column; gap: 4px; }
.drawer-step-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--pin-radius-sm);
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: background 0.15s;
    width: 100%;
}
.drawer-step-btn:hover:not(.disabled) { background: var(--pin-surface-3); }
.drawer-step-btn.active { background: var(--pin-green-light); }
.drawer-step-btn.disabled { opacity: 0.45; cursor: not-allowed; }
.step-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--pin-surface-3);
    color: var(--pin-text-2);
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.drawer-step-btn.active .step-num {
    background: var(--pin-green);
    color: #fff;
}
.step-label { font-size: 13px; color: var(--pin-text-2); flex: 1; }
.drawer-step-btn.active .step-label { color: var(--pin-green-dark); font-weight: 500; }

/* ─── Shell ─── */
.card-shell {
    max-width: 640px;
    margin: 0 auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}
@media (min-width: 768px) {
    .card-shell { padding: 1.5rem; }
}

/* ─── Event Card ─── */
.event-card {
    background: var(--pin-surface);
    border: 1px solid var(--pin-border);
    border-radius: var(--pin-radius);
    overflow: hidden;
}

/* Header block */
.event-header {
    padding: 1.25rem 1.25rem 1rem;
    border-left: 3px solid var(--pin-green);
    background: var(--pin-surface);
}
.event-header-content { display: flex; flex-direction: column; gap: 0.5rem; }
.event-id-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    background: var(--pin-green-light);
    color: var(--pin-green-dark);
    font-family: var(--pin-font-mono);
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 999px;
    letter-spacing: 0.04em;
    width: fit-content;
    margin-bottom: 2px;
}
.event-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--pin-text);
    line-height: 1.3;
    letter-spacing: -0.01em;
}
.event-desc {
    font-size: 13px;
    color: var(--pin-text-3);
    line-height: 1.6;
    margin: 0;
}

/* Status ribbon */
.status-ribbon {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 1.25rem;
    background: var(--pin-surface-2);
    border-top: 1px solid var(--pin-border);
    border-bottom: 1px solid var(--pin-border);
    gap: 0.75rem;
}
.status-left { display: flex; align-items: center; }
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    letter-spacing: 0.03em;
}
.badge-ongoing {
    background: #dcfce7;
    color: #15803d;
}
.badge-upcoming {
    background: #fef9c3;
    color: #92400e;
}
.badge-expired {
    background: #fee2e2;
    color: #991b1b;
}

/* Live ring animation */
.live-ring {
    position: relative;
    display: inline-flex;
    width: 8px;
    height: 8px;
}
.live-ring-ping {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: #4ade80;
    opacity: 0.75;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.live-ring-dot {
    position: relative;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #16a34a;
}
@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}
.dot-state-upcoming { background: #f59e0b; }
.dot-state-expired { background: #ef4444; }

/* Countdown ticker */
.countdown-ticker {
    display: flex;
    align-items: center;
    gap: 4px;
}
.ticker-unit {
    display: flex;
    align-items: baseline;
    gap: 1px;
}
.ticker-num {
    font-family: var(--pin-font-mono);
    font-size: 13px;
    font-weight: 600;
    color: var(--pin-text);
    letter-spacing: -0.02em;
    min-width: 22px;
    text-align: center;
}
.ticker-label {
    font-size: 10px;
    color: var(--pin-text-4);
    font-weight: 500;
}
.ticker-sep {
    font-size: 12px;
    color: var(--pin-border-strong);
    font-family: var(--pin-font-mono);
    padding: 0 1px;
}
.ticker-ended {
    font-size: 13px;
    color: var(--pin-text-4);
}

/* Dates grid */
.dates-grid {
    display: grid;
    grid-template-columns: 1fr 1px 1fr;
    border-top: 1px solid var(--pin-border);
}
.date-cell {
    padding: 0.875rem 1.25rem;
    text-align: center;
}
.date-divider {
    background: var(--pin-border);
}
.date-cell-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 11px;
    color: var(--pin-text-4);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
.date-cell-value {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--pin-text);
    margin: 0;
}
.date-cell-time {
    font-size: 12px;
    color: var(--pin-text-3);
    margin: 0;
    margin-top: 1px;
}

/* Venue */
.venue-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 0.875rem 1.25rem;
    border-top: 1px solid var(--pin-border);
}
.venue-icon { color: var(--pin-text-4); margin-top: 1px; flex-shrink: 0; }
.venue-text { display: flex; flex-direction: column; gap: 2px; }
.venue-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--pin-text);
    margin: 0;
}
.venue-details {
    font-size: 12px;
    color: var(--pin-text-3);
    line-height: 1.5;
    margin: 0;
}

/* Slots */
.slots-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--pin-border);
    background: var(--pin-surface-2);
}
.slots-info {
    display: flex;
    align-items: center;
    gap: 5px;
}
.slots-icon { color: var(--pin-text-4); }
.slots-text {
    font-size: 12.5px;
    color: var(--pin-text-3);
}
.slots-text strong {
    color: var(--pin-text);
    font-weight: 600;
}
.slots-bar-track {
    width: 80px;
    height: 5px;
    background: var(--pin-border);
    border-radius: 999px;
    overflow: hidden;
    flex-shrink: 0;
}
.slots-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s ease;
}
.slot-ok { background: var(--pin-green); }
.slot-low { background: #f59e0b; }
.slot-critical { background: #f97316; }
.slot-full { background: #ef4444; }

/* ─── Alerts ─── */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 0.875rem 1rem;
    border-radius: var(--pin-radius);
    border: 1px solid;
}
.alert-warning {
    background: var(--pin-amber-light);
    border-color: #fcd34d;
}
.alert-warning .alert-icon { color: var(--pin-amber); margin-top: 1px; }
.alert-warning .alert-title { color: #78350f; }
.alert-warning .alert-body { color: #92400e; }
.alert-danger {
    background: var(--pin-red-light);
    border-color: #fca5a5;
}
.alert-danger .alert-icon { color: var(--pin-red); margin-top: 1px; }
.alert-danger .alert-title { color: #7f1d1d; }
.alert-danger .alert-body { color: #991b1b; }
.alert-icon { flex-shrink: 0; }
.alert-title { font-size: 13.5px; font-weight: 600; margin: 0; }
.alert-body { font-size: 12.5px; margin: 2px 0 0; line-height: 1.5; }

/* ─── Section Card ─── */
.section-card {
    background: var(--pin-surface);
    border: 1px solid var(--pin-border);
    border-radius: var(--pin-radius);
    padding: 1rem 1.125rem;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}
.field-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--pin-text-3);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.participant-row { display: flex; align-items: center; gap: 8px; }
.btn-ghost-danger {
    padding: 7px;
    color: var(--pin-red);
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--pin-radius-xs);
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    flex-shrink: 0;
}
.btn-ghost-danger:hover {
    background: var(--pin-red-light);
    border-color: #fca5a5;
}

/* ─── Verify Card ─── */
.verify-card { gap: 0.875rem; }
.verify-header {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--pin-blue);
}
.verify-icon { color: var(--pin-blue); }
.verify-body { font-size: 13px; color: var(--pin-text-3); margin: 0; line-height: 1.6; }

/* Choice grid */
.choice-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.choice-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 1rem 0.75rem;
    border: 1.5px solid var(--pin-border-strong);
    border-radius: var(--pin-radius-sm);
    background: var(--pin-surface);
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    text-align: center;
}
.choice-btn:hover { border-color: var(--pin-green); background: var(--pin-green-light); }
.choice-yes:hover { color: var(--pin-green-dark); }
.choice-no:hover { border-color: var(--pin-green); color: var(--pin-green-dark); }
.choice-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--pin-text);
    display: block;
}
.choice-sub {
    font-size: 11px;
    color: var(--pin-text-4);
    display: block;
}

/* Email lookup */
.email-lookup { display: flex; flex-direction: column; gap: 8px; }
.lookup-hint { font-size: 12.5px; color: var(--pin-text-3); margin: 0; }
.lookup-row { display: flex; gap: 8px; }
.input-with-icon { position: relative; flex: 1; }
.input-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pin-text-4);
    pointer-events: none;
}
.pin-input {
    width: 100%;
    padding: 8px 10px 8px 32px;
    border: 1px solid var(--pin-border-strong);
    border-radius: var(--pin-radius-xs);
    font-size: 13px;
    color: var(--pin-text);
    background: var(--pin-surface);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}
.pin-input:focus {
    border-color: var(--pin-green);
    box-shadow: 0 0 0 3px rgba(26,122,74,0.12);
}
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 14px;
    background: var(--pin-green);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border: none;
    border-radius: var(--pin-radius-xs);
    cursor: pointer;
    transition: background 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-primary:hover { background: var(--pin-green-dark); }
.btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }

/* Prereg nudge */
.prereg-nudge {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--pin-surface-2);
    border-radius: var(--pin-radius-xs);
    border: 1px solid var(--pin-border);
    flex-wrap: wrap;
}
.nudge-text { font-size: 12.5px; color: var(--pin-text-3); margin: 0; }
.btn-success {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 13px;
    background: var(--pin-green);
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    border: none;
    border-radius: var(--pin-radius-xs);
    cursor: pointer;
    transition: background 0.15s;
    white-space: nowrap;
}
.btn-success:hover { background: var(--pin-green-dark); }

/* Inline alerts */
.inline-alert {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 10px;
    border-radius: var(--pin-radius-xs);
    font-size: 12.5px;
}
.inline-success { background: #f0fdf4; color: #15803d; }
.inline-danger { background: var(--pin-red-light); color: var(--pin-red); }

/* ─── Loading ─── */
.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 2.5rem 0;
    color: var(--pin-text-3);
    font-size: 13px;
}
.spin { animation: spin 0.9s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Tabs ─── */
.tabs-bar {
    background: var(--pin-surface);
    border: 1px solid var(--pin-border);
    border-radius: var(--pin-radius);
    padding: 4px;
    display: flex;
    gap: 3px;
    overflow-x: auto;
}
.tab-btn {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: var(--pin-radius-sm);
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 13px;
    color: var(--pin-text-3);
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.tab-btn:hover:not(.tab-disabled) {
    background: var(--pin-surface-3);
    color: var(--pin-text-2);
}
.tab-active {
    background: var(--pin-green) !important;
    color: #fff !important;
    font-weight: 600;
}
.tab-disabled { opacity: 0.45; cursor: not-allowed; }
.tab-index {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}
.tab-btn:not(.tab-active) .tab-index {
    background: var(--pin-surface-3);
    color: var(--pin-text-3);
}
.tab-label { overflow: hidden; text-overflow: ellipsis; }
.tab-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.tab-btn.tab-active .tab-dot { background: rgba(255,255,255,0.7); }

/* Step dots (shared) */
.dot-available { background: #22c55e; }
.dot-completed { background: #3b82f6; }
.dot-locked, .dot-not_yet_open, .dot-disabled, .dot-hidden { background: #d1d5db; }
.dot-expired, .dot-full { background: #ef4444; }

/* Step indicator mobile */
.step-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0.25rem;
}
.step-prog { font-size: 12px; color: var(--pin-text-4); }
.step-cur-label { font-size: 12.5px; font-weight: 600; color: var(--pin-green); }

/* ─── Step countdown ─── */
.step-countdown {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 1rem;
    background: var(--pin-surface);
    border: 1px solid var(--pin-border);
    border-radius: var(--pin-radius-sm);
    gap: 0.75rem;
}
.step-countdown-inner {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: var(--pin-text-3);
}
.step-countdown-value {
    font-family: var(--pin-font-mono);
    font-size: 13px;
    font-weight: 600;
}
.countdown-warn { color: var(--pin-amber); }
.countdown-info { color: var(--pin-blue); }

/* ─── Form Shell ─── */
.form-shell {
    background: var(--pin-surface);
    border: 1px solid var(--pin-border);
    border-radius: var(--pin-radius);
    overflow: hidden;
}
.form-shell-header {
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--pin-border);
    background: var(--pin-surface-2);
}
.form-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--pin-text);
    margin: 0;
    letter-spacing: -0.01em;
}
.form-desc {
    font-size: 12.5px;
    color: var(--pin-text-3);
    margin: 4px 0 0;
    line-height: 1.5;
}
.form-body { padding: 1.25rem; }

/* ─── Unavailable state ─── */
.unavailable-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    gap: 8px;
    text-align: center;
}
.unavail-icon-ring {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}
.ring-locked, .ring-disabled { background: var(--pin-surface-3); }
.ring-not_yet_open { background: var(--pin-amber-light); }
.ring-expired, .ring-full { background: var(--pin-red-light); }
.ring-completed { background: var(--pin-blue-light); }
.icon-locked, .icon-disabled { color: var(--pin-text-4); }
.icon-not_yet_open { color: var(--pin-amber); }
.icon-expired, .icon-full { color: var(--pin-red); }
.icon-completed { color: var(--pin-blue); }
.unavail-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--pin-text);
    margin: 0;
}
.unavail-sub {
    font-size: 12.5px;
    color: var(--pin-text-3);
    margin: 0;
}

/* ─── Transitions ─── */
.drawer-enter-active, .drawer-leave-active { transition: opacity 0.2s ease; }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }

/* ─── Dark Mode ─── */
.dark .pin-guest-card {
    --pin-surface: #111916;
    --pin-surface-2: #161e1a;
    --pin-surface-3: #1c2721;
    --pin-border: #243020;
    --pin-border-strong: #2d3d32;
    --pin-text: #e8f0eb;
    --pin-text-2: #a8c4b0;
    --pin-text-3: #6b9278;
    --pin-text-4: #415c4a;
    --pin-green-light: #0f2d1c;
    --pin-green-dark: #6dd49a;
    --pin-red-light: #2d1212;
    --pin-amber-light: #2d1f08;
    --pin-blue-light: #0d1f35;
}
.dark .badge-ongoing { background: #14532d; color: #86efac; }
.dark .badge-upcoming { background: #451a03; color: #fde68a; }
.dark .badge-expired { background: #450a0a; color: #fca5a5; }
.dark .inline-success { background: #14532d; color: #86efac; }
.dark .inline-danger { background: #2d1212; color: #fca5a5; }
</style>
