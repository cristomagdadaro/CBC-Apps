<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import PreregistrationQuizBeeCard from "@/Pages/Forms/components/PreregistrationQuizBeeCard.vue";
import PreregistrationQuizbeeTeamCard from "@/Pages/Forms/components/PreregistrationQuizbeeTeamCard.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import RegistrationCard from "@/Pages/Forms/components/RegistrationCard.vue";
import FeedbackCard from "@/Pages/Forms/components/FeedbackCard.vue";
import { mergeFormStyleTokens } from "@/Modules/shared/formStyleTokens";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";

export default {
    name: "GuestCard",
    components: { FeedbackCard, RegistrationCard, PreregistrationCard, PreregistrationQuizBeeCard, PreregistrationQuizbeeTeamCard },
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
                    key: step.form_type,
                    label: `Step ${index + 1}`,
                    disabled: step.status !== 'available',
                }));
        },
        activeStep() {
            return this.workflowSteps.find((step) => step.form_type === this.activeTab) || null;
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
        this.selectedParticipantHash = this.participantHashes?.slice(-1)?.[0] ?? null;
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
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        clearInterval(this.formCountdownIntervalId);
    },
    methods: {
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
            this.loadWorkflow();
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
            this.$emit('createdModel', payload);
            this.loadWorkflow();
        },
        setActiveTabFromWorkflow() {
            if (!this.workflowSteps?.length) {
                return;
            }

            const available = this.workflowSteps.find((step) => step.status === 'available');
            this.activeTab = available?.form_type ?? this.workflowSteps[0]?.form_type ?? null;
        },
        whatForm(formType) {
            if (!this.data || !Array.isArray(this.data.requirements) || this.data.requirements.length <= 0) return null;
            return this.data.requirements.find((requirement) => requirement.form_type === formType) || null;
        },
        getStep(formType) {
            return this.workflowSteps.find((step) => step.form_type === formType) || null;
        },
        getRequirementFormId(formType) {
            const step = this.getStep(formType) ?? this.whatForm(formType);
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

                    <div v-if="activeKey === 'preregistration'">
                        <preregistration-card
                            v-if="activeStep?.status === 'available'"
                            :event-id="getRequirementFormId('preregistration')"
                            :config="getStep('preregistration')"
                            @createdModel="handleCreatedModel"
                        />
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep('preregistration')) }}
                        </div>
                    </div>

                    <div v-if="activeKey === 'preregistration_biotech'">
                        <preregistration-quiz-bee-card
                            v-if="activeStep?.status === 'available'"
                            :event-id="getRequirementFormId('preregistration_biotech')"
                            :config="getStep('preregistration_biotech')"
                            @createdModel="handleCreatedModel"
                        />
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep('preregistration_biotech')) }}
                        </div>
                    </div>

                    <div v-if="activeKey === 'preregistration_quizbee'">
                        <preregistration-quizbee-team-card
                            v-if="activeStep?.status === 'available'"
                            :event-id="getRequirementFormId('preregistration_quizbee')"
                            :config="getStep('preregistration_quizbee')"
                            @createdModel="handleCreatedModel"
                        />
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep('preregistration_quizbee')) }}
                        </div>
                    </div>

                    <div v-if="activeKey === 'registration'">
                        <registration-card
                            v-if="activeStep?.status === 'available'"
                            :event-id="getRequirementFormId('registration')"
                            :participant-id="selectedParticipantHash"
                            :config="getStep('registration')"
                            @createdModel="handleCreatedModel"
                        />
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep('registration')) }}
                        </div>
                    </div>

                    <div v-if="activeKey === 'feedback'">
                        <feedback-card
                            v-if="activeStep?.status === 'available'"
                            :event-id="getRequirementFormId('feedback')"
                            :participant-id="selectedParticipantHash"
                            :config="getStep('feedback')"
                            @createdModel="handleCreatedModel"
                        />
                        <div v-else class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            {{ getStepMessage(getStep('feedback')) }}
                        </div>
                    </div>
                    <div v-if="['preregistration', 'preregistration_biotech', 'preregistration_quizbee', 'registration', 'feedback'].indexOf(activeKey) === -1">
                        <div class="bg-AB text-white p-3 rounded-md shadow leading-none uppercase text-center">
                            This step type is not yet supported in the guest UI.
                        </div>
                    </div>
                </template>
            </TabNavigation>

        </div>

    </div>
</template>

<style scoped>

</style>
