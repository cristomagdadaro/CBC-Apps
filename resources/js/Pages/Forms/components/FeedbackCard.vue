<script>
import SubformMixin from "@/Modules/mixins/SubformMixin";
import SubformResponse from "@/Modules/domain/SubformResponse";
import DtoResponse from "@/Modules/dto/DtoResponse";
import ProgressTabs from "@/Components/ProgressTabs.vue";
import LikertScale from "@/Components/LikertScale.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Checkbox from "@/Components/Checkbox.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "FeedbackCard",
    mixins: [SubformMixin],
    components: {
        ProgressTabs,
        LikertScale,
        PrimaryButton,
        InputLabel,
        InputError,
        Checkbox,
        TransitionContainer,
        SubmitBtn,
        CustomDropdown,
    },
    props: {
        responseData: {
            type: Object,
            default: null,
        },
        participantId: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            currentStep: 0,
        }
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
        },
        steps() {
            return [
                'Activity Evaluation',
                'Knowledge Gain & Comments',
            ];
        },
        participantsAsOptions() {
            return this.storedLocalHashedIds.map((data) => {
                return {
                    label: `${data.participant.name} (${data.event_id})`,
                    name: data.participant_hash,
                }
            })
        }
    },
    methods: {
        async handleSubmit() {
            if (this.isEditMode) {
                await this.handleUpdate();
            } else {
                await this.handleCreate();
            }
        },
        async handleCreate() {
            const retainParent = this.form.form_parent_id;
            const retainType = this.form.subform_type;
            try {
                const response = await this.submitCreate(false, 'form_parent_id');
                if (response?.status === 201) {
                    this.showSuccess = true;
                    this.$emit('createdModel', response.data);
                }
            } catch (e) {
                // handled in mixin
            } finally {
                this.form.form_parent_id = retainParent;
                this.form.subform_type = retainType;
            }
        },
        async handleUpdate() {
            const response = await this.submitUpdate(null, 'response_data');
            if (response instanceof DtoResponse) {
                this.showSuccess = true;
                this.$emit('updatedModel', response.data);
            }
        },
        nextStep() {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep += 1;
            }
        },
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep -= 1;
            }
        },
    },
    beforeMount() {
        this.model = new SubformResponse();
        if (this.isEditMode) {
            this.setFormAction('update');
            this.form.id = this.responseData.id;
            this.form.response_data = this.responseData.response_data || {};
        } else {
            this.setFormAction('create').response_data = SubformResponse.getSubformFields('feedback');
            this.form.form_parent_id = this.eventId;
            this.form.response_data.event_id = this.config?.event_id ?? this.eventId;
        }
        this.form.subform_type = 'feedback';
        if (this.participantId) {
            this.form.participant_id = this.participantId;
        }
    },
    watch: {
        'form.response_data.agreed_tc': {
            immediate: true,
            handler() {
                this.form?.clearErrors('agreed_tc');
            }
        }
    },
}
</script>

<template>
    <form v-if="form" @submit.prevent="handleSubmit()" class="py-3 select-none relative bg-white px-3 border-t" :class="{'border border-red-600 rounded-md': form.hasErrors}">
        <transition-container type="slide-top">
            <div v-show="showSuccess" class="absolute flex top-0 left-0 bg-AC w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow">
                <button @click.prevent="showSuccess = false" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <div class="text-xl bg-AC w-full flex flex-col gap-1 justify-center mb-1 py-2">


                    </div>
                    <span class="drop-shadow leading-none font-light">
                        Thank you for your feedback!
                    </span>
                    <span class="drop-shadow leading-none text-sm">
                        See you in our future activities!
                    </span>
                </div>
            </div>
        </transition-container>
        <div class="pb-3 pt-1">
            <label class="text-red-600 uppercase justify-center flex">{{ form.errors.suspended || form.errors.full || form.errors.expired }}</label>
            <h3 class="text-lg leading-tight uppercase font-extrabold">
                {{ isEditMode ? 'Update Feedback' : 'Post Activity Feedback Form' }}
            </h3>
            <p class="text-xs leading-none">
                Kindly provide the required and honest feedback. Fields marked with <span class="text-red-600">*</span> are required.
            </p>
        </div>
        <div class="mb-4">
            <ProgressTabs :steps="steps" v-model:current="currentStep" :clickable="false" />
        </div>

        <div class="flex flex-col gap-4">
            <!-- STEP 1: Activity Evaluation Likert items -->
            <div v-if="currentStep === 0" class="flex flex-col gap-5">
                <LikertScale name="clarity_objective" label="Clarity of objective" :required="true" v-model="form.response_data.clarity_objective" :error="form.errors.clarity_objective" @clear-error="form.clearErrors('clarity_objective')" />
                <LikertScale name="time_allotment" label="Time allotment (balance between lecture, discussion, activities and workshops)" :required="true" v-model="form.response_data.time_allotment" :error="form.errors.time_allotment" @clear-error="form.clearErrors('time_allotment')" />
                <LikertScale name="attainment_objective" label="Attainment of objective" :required="true" v-model="form.response_data.attainment_objective" :error="form.errors.attainment_objective" @clear-error="form.clearErrors('attainment_objective')" />
                <LikertScale name="relevance_usefulness" label="Relevance and usefulness to your job/function" :required="true" v-model="form.response_data.relevance_usefulness" :error="form.errors.relevance_usefulness" @clear-error="form.clearErrors('relevance_usefulness')" />
                <LikertScale name="overall_quality_content" label="Overall quality of content" :required="true" v-model="form.response_data.overall_quality_content" :error="form.errors.overall_quality_content" @clear-error="form.clearErrors('overall_quality_content')" />
                <LikertScale name="overall_quality_resource_persons" label="Overall quality of the Resource Persons" :required="true" v-model="form.response_data.overall_quality_resource_persons" :error="form.errors.overall_quality_resource_persons" @clear-error="form.clearErrors('overall_quality_resource_persons')" />
                <LikertScale name="time_management_organization" label="Time management and organization" :required="true" v-model="form.response_data.time_management_organization" :error="form.errors.time_management_organization" @clear-error="form.clearErrors('time_management_organization')" />
                <LikertScale name="support_staff" label="Support staff" :required="true" v-model="form.response_data.support_staff" :error="form.errors.support_staff" @clear-error="form.clearErrors('support_staff')" />
                <LikertScale name="overall_quality_activity_admin" label="Overall quality of the activity administration" :required="true" v-model="form.response_data.overall_quality_activity_admin" :error="form.errors.overall_quality_activity_admin" @clear-error="form.clearErrors('overall_quality_activity_admin')" />
            </div>

            <!-- STEP 2: Knowledge gain & comments -->
            <div v-if="currentStep === 1" class="flex flex-col gap-3">
                <LikertScale name="knowledge_gain" label="Knowledge gain (1-5)" :required="true" v-model="form.response_data.knowledge_gain" :error="form.errors.knowledge_gain" @clear-error="form.clearErrors('knowledge_gain')" />
                <div class="flex flex-col gap-1">
                    <InputLabel for="comments_event_coordination" value="Comments/Suggestions - Event Coordination" />
                    <textarea id="comments_event_coordination" v-model="form.response_data.comments_event_coordination" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" rows="3" @input="form.clearErrors('comments_event_coordination')" />
                    <InputError :message="form.errors.comments_event_coordination" />
                </div>
                <div class="flex flex-col gap-1">
                    <InputLabel for="other_topics" value="Other topics for future activities" />
                    <textarea id="other_topics" v-model="form.response_data.other_topics" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" rows="3" @input="form.clearErrors('other_topics')" />
                    <InputError :message="form.errors.other_topics" />
                </div>
                <div class="py-3 flex gap-2">
                    <Checkbox id="agreed_tc" :class="{'border border-red-600' : form.errors.agreed_tc}" v-model="form.response_data.agreed_tc" :checked="form.response_data.agreed_tc" />
                    <p class="text-xs leading-none" @click.prevent="form.response_data.agreed_tc = !form.response_data.agreed_tc">
                        By submitting this form you certify the accuracy of the information and consent to data processing.
                        <transition-container type="slide-bottom">
                            <InputError v-show="!!form.errors.agreed_tc" :message="form.errors.agreed_tc" />
                        </transition-container>
                    </p>
                </div>
            </div>

            <!-- Navigation / Submit -->
            <div class="flex items-center justify-between pt-4">
                <PrimaryButton type="button" v-if="currentStep > 0" @click.prevent="prevStep()">
                    Previous
                </PrimaryButton>

                <div class="flex-1" />

                <PrimaryButton type="button" v-if="currentStep < steps.length - 1" @click.prevent="nextStep()">
                    Next
                </PrimaryButton>

                <submit-btn v-else :disabled="model.api.processing" :processing="model.api.processing">
                    <span v-if="!model.api.processing">{{ isEditMode ? 'Update Feedback' : 'Submit Feedback' }}</span>
                    <span v-else>{{ isEditMode ? 'Updating...' : 'Submitting...' }}</span>
                </submit-btn>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
