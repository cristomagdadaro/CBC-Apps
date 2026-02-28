<script>
import SubformResponse from "@/Modules/domain/SubformResponse";
import DtoResponse from "@/Modules/dto/DtoResponse";

export default {
    name: "BaseFormCard",
    props: {
        responseData: {
            type: Object,
            default: null,
        },
        participantId: {
            type: String,
            default: null,
        },
        config: {
            type: Object,
            default: null,
        },
        eventId: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            showSuccess: false,
            model: null,
            form: null,
        };
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
        },
        registrationIDHashed() {
            return this.model?.response?.participant_hash ?? null;
        },
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
            const response = await this.submitCreate();
            if (response?.status === 201) {
                this.model.response = response.data;
                this.showSuccess = true;
                this.$emit('createdModel', response.data);
            }
        },
        async handleUpdate() {
            const response = await this.submitUpdate(null, 'response_data');
            if (response instanceof DtoResponse) {
                this.showSuccess = true;
                this.$emit('updatedModel', response.data);
            }
        },
        initializeForm(subformType, excludeFields = []) {
            this.model = new SubformResponse();
            if (this.isEditMode) {
                this.setFormAction('update');
                this.form.id = this.responseData.id;
                this.form.response_data = Object.assign({}, this.responseData.response_data || {});
            } else {
                this.setFormAction('create').response_data = SubformResponse.getSubformFields(subformType);
                this.form.form_parent_id = this.eventId;
                this.form.response_data.event_id = this.config?.event_id ?? this.eventId;
            }
            this.form.subform_type = subformType;
            if (this.participantId) {
                this.form.participant_id = this.participantId;
            }
        },
    },
};
</script>

<template>
    <form v-if="form" @submit.prevent="handleSubmit()" class="py-3 relative bg-white px-3" :class="{'border border-red-600 rounded-md': form.hasErrors}">
        <transition-container type="slide-top">
            <div v-show="showSuccess" class="absolute flex top-0 left-0 bg-AB w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow">
                <button @click.prevent="showSuccess = false" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <div class="text-xl w-full flex flex-col gap-1 justify-center mb-1 py-2">
                        {{ registrationIDHashed }}
                        <qrcode-vue
                            v-if="registrationIDHashed"
                            :value="registrationIDHashed"
                            size="200"
                            level="H"
                            class="flex justify-center mx-auto"
                        />
                    </div>
                    <span class="text-sm">Your registration has been submitted successfully</span>
                    <span class="leading-tight text-xs">You may share this code to verify your submission</span>
                </div>
            </div>
        </transition-container>

        <!-- Form content is provided by child component via slot -->
        <slot></slot>
    </form>
</template>

<style scoped>
</style>
