<script>
import SubformMixin from "@/Modules/mixins/SubformMixin";
import LocationMixin from "@/Modules/mixins/LocationMixin";
import SubformResponse from "@/Modules/domain/SubformResponse";
import DtoResponse from "@/Modules/dto/DtoResponse";
export default {
    name: "PreregistrationQuizbeeTeamCard",
    mixins: [SubformMixin, LocationMixin],
    props: {
        responseData: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            proofFileName: null,
        };
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
        },
    },
    watch: {
        'form.response_data.region_address'(value) {
            if (!this.form) return;
            this.form.response_data.province_address = null;
            this.form.response_data.city_address = null;
            this.loadProvinces(value);
            this.locationCities = [];
        },
        'form.response_data.province_address'(value) {
            if (!this.form) return;
            this.form.response_data.city_address = null;
            this.loadCities(value, this.form.response_data.region_address);
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
            this.model.api.processing = true;
            this.form.clearErrors();

            try {
                const formData = this.buildFormData();
                const response = await this.fetchPostApi('api.subform.response.store', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                const dto = new DtoResponse(response);
                this.showSuccess = dto.status === 201 || dto.status === 200;

                if (this.showSuccess) {
                    this.registrationIDHashed = dto.data?.participant_hash ?? null;
                    this.$emit('createdModel', dto.data);
                }
            } catch (error) {
                this.checkError(error);
            } finally {
                this.model.api.processing = false;
            }
        },
        async handleUpdate() {
            const response = await this.submitUpdate(null, ['response_data','form_parent_id','subform_type']);
            if (response instanceof DtoResponse) {
                this.showSuccess = true;
                this.$emit('updatedModel', response.data);
            }
        },
        handleFileChange(event) {
            const file = event?.target?.files?.[0] ?? null;
            this.form.response_data.proof_of_enrollment = file;
            this.proofFileName = file?.name ?? null;
            this.form.clearErrors('proof_of_enrollment');
        },
        buildFormData() {
            const formData = new FormData();

            formData.append('subform_type', this.form.subform_type);
            formData.append('form_parent_id', this.form.form_parent_id);

            if (this.form.participant_id) {
                formData.append('participant_id', this.form.participant_id);
            }

            const responseData = this.form.response_data || {};

            Object.entries(responseData).forEach(([key, value]) => {
                if (value === null || value === undefined) return;

                if (value instanceof File) {
                    formData.append(`response_data[${key}]`, value);
                    return;
                }

                if (value instanceof Date) {
                    formData.append(
                        `response_data[${key}]`,
                        value.toISOString().slice(0, 19).replace('T', ' ')
                    );
                    return;
                }
                if (typeof value === 'object') {
                    formData.append(
                        `response_data[${key}]`,
                        JSON.stringify(value)
                    );
                    return;
                }

                if (typeof value === 'boolean') {
                    formData.append(
                        `response_data[${key}]`,
                        value ? '1' : '0'
                    );
                    return;
                }

                formData.append(`response_data[${key}]`, String(value));
            });

            return formData;
        }

    },
    beforeMount() {
        this.model = new SubformResponse();
        if (this.isEditMode) {
            this.setFormAction('update');
            this.form.id = this.responseData.id;
            // Ensure all response_data fields are preserved, including address fields
            this.form.response_data = Object.assign({}, this.responseData.response_data || {});
        } else {
            this.setFormAction('create').response_data = SubformResponse.getSubformFields('preregistration_quizbee');
            this.form.form_parent_id = this.eventId;
            this.form.response_data.region_address = 'REGION III';
            this.loadProvinces(this.form.response_data.region_address);
        }
        this.form.subform_type = 'preregistration_quizbee';
    },
    mounted() {
        //this.loadRegions();
        if (this.form?.response_data?.region_address) {
            this.loadProvinces(this.form.response_data.region_address);
        }
        if (this.form?.response_data?.province_address) {
            this.loadCities(this.form.response_data.province_address, this.form.response_data.region_address);
        }
    },
}
</script>

<template>
    <form v-if="form" @submit.prevent="handleSubmit()" class="py-3  relative bg-white px-3 border-t" :class="{'border border-red-600 rounded-md': form.hasErrors}">
        <transition-container type="slide-top">
            <div v-show="showSuccess" class="absolute flex top-0 left-0 bg-AB w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow">
                <button @click.prevent="showSuccess = false" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <span class="drop-shadow leading-none font-light">
                        Pre-registration Successful!
                    </span>
                    <span class="drop-shadow leading-none text-sm">
                        We will review your submission and contact your coach.
                    </span>
                </div>
            </div>
        </transition-container>

        <div class="pb-3 pt-1">
            <h3 class="text-lg leading-tight uppercase font-extrabold">
                {{ isEditMode ? 'Update Quiz Bee Team Registration' : 'Quiz Bee Team Registration' }}
            </h3>
            <p class="text-sm leading-tight">
                Provide the team and coach details for quiz bee participation. Fields marked with <span class="text-red-600">*</span> are required.
            </p>
            <label class="text-red-700 uppercase justify-center flex text-sm leading-tight">{{ form.errors.suspended || form.errors.full || form.errors.expired || form.errors.limit }}</label>
        </div>

        <div class="flex flex-col gap-3">
            <TextInput
                id="organization"
                v-model="form.response_data.organization"
                type="text"
                :error="form.errors.organization"
                placeholder="School / Organization*"
                autocomplete="organization"
                @input="form.clearErrors('organization')"
            />
            <div class="grid grid-cols-3 gap-2">
                <custom-dropdown
                    :value="form.response_data.region_address"
                    @selectedChange="form.response_data.region_address = $event"
                    :error="form.errors.region_address"
                    placeholder="Region"
                    disabled
                    :withAllOption="false"
                    :options="locationRegions.map(region => ({ name: region, label: region }))"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
                <custom-dropdown
                    :value="form.response_data.province_address"
                    @selectedChange="form.response_data.province_address = $event"
                    :error="form.errors.province_address"
                    placeholder="Province"
                    :withAllOption="false"
                    :options="locationProvinces.map(province => ({ name: province, label: province }))"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
                <custom-dropdown
                    :value="form.response_data.city_address"
                    @selectedChange="form.response_data.city_address = $event"
                    :error="form.errors.city_address"
                    placeholder="City"
                    :withAllOption="false"
                    :options="locationCities.map(city => ({ name: city.city ?? city, label: city.city ?? city }))"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
            </div>

            <TextInput
                id="team_name"
                v-model="form.response_data.team_name"
                type="text"
                :error="form.errors.team_name"
                placeholder="Team Name*"
                autocomplete="team_name"
                @input="form.clearErrors('team_name')"
            />

            <div class="grid grid-cols-2 gap-2">
                <TextInput
                    id="participant_1_name"
                    v-model="form.response_data.participant_1_name"
                    type="text"
                    :error="form.errors.participant_1_name"
                    placeholder="Participant 1 Name*"
                    autocomplete="participant_1_name"
                    @input="form.clearErrors('participant_1_name')"
                />
                <TextInput
                    id="participant_2_name"
                    v-model="form.response_data.participant_2_name"
                    type="text"
                    :error="form.errors.participant_2_name"
                    placeholder="Participant 2 Name*"
                    autocomplete="participant_2_name"
                    @input="form.clearErrors('participant_2_name')"
                />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <SelectSex
                    v-model="form.response_data.participant_1_sex"
                    :error="form.errors.participant_1_sex"
                    placeholder="Participant 1 Sex"
                />
                <SelectSex
                    v-model="form.response_data.participant_2_sex"
                    :error="form.errors.participant_2_sex"
                    placeholder="Participant 2 Sex"
                />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <custom-dropdown
                    :value="form.response_data.participant_1_gradelevel"
                    @selectedChange="form.response_data.participant_1_gradelevel = $event"
                    :error="form.errors.participant_1_gradelevel"
                    placeholder="Participant 1 Grade Level*"
                    :withAllOption="false"
                    :options="[{name: 'Grade 11', label: 'Grade 11'}]"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
                <custom-dropdown
                    :value="form.response_data.participant_2_gradelevel"
                    @selectedChange="form.response_data.participant_2_gradelevel = $event"
                    :error="form.errors.participant_2_gradelevel"
                    placeholder="Participant 2 Grade Level*"
                    :withAllOption="false"
                    :options="[{name: 'Grade 12', label: 'Grade 12'}]"
                >
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-600">Proof of Enrollment (PDF)*</label>
                <input
                    type="file"
                    accept="application/pdf"
                    class="text-xs"
                    @change="handleFileChange"
                />
                <span v-if="proofFileName" class="text-[11px] text-gray-500">Selected: {{ proofFileName }}</span>
                <transition-container type="slide-bottom">
                    <InputError v-show="!!form.errors.proof_of_enrollment" :message="form.errors.proof_of_enrollment" />
                </transition-container>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <TextInput
                    id="coach_name"
                    v-model="form.response_data.coach_name"
                    type="text"
                    :error="form.errors.coach_name"
                    placeholder="Coach Name*"
                    autocomplete="coach_name"
                    @input="form.clearErrors('coach_name')"
                />
                <TextInput
                    id="coach_email"
                    v-model="form.response_data.coach_email"
                    type="email"
                    :error="form.errors.coach_email"
                    placeholder="Coach Email*"
                    autocomplete="coach_email"
                    @input="form.clearErrors('coach_email')"
                />
            </div>

            <TextInput
                id="coach_phone"
                v-model="form.response_data.coach_phone"
                type="text"
                :error="form.errors.coach_phone"
                placeholder="Coach Phone*"
                autocomplete="coach_phone"
                @input="form.clearErrors('coach_phone')"
            />

            <div class="py-3 flex gap-2">
                <Checkbox id="agreed_tc" :class="{'border border-red-600' : form.errors.agreed_tc}" v-model="form.response_data.agreed_tc" :checked="form.response_data.agreed_tc" autocomplete="agreed_tc"/>
                <p class="text-xs leading-none" @click.prevent="form.response_data.agreed_tc = !form.response_data.agreed_tc">
                    By submitting this form, you consent to the DA-Crop Biotechnology Center collecting and using your data in accordance with our privacy policy.
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.agreed_tc" class="" :message="form.errors.agreed_tc" />
                    </transition-container>
                </p>
            </div>

            <submit-btn :disabled="model.api.processing" :processing="model.api.processing">
                <span v-if="!model.api.processing">{{ isEditMode ? 'Update' : 'Submit' }}</span>
                <span v-else>{{ isEditMode ? 'Updating' : 'Submitting' }}</span>
            </submit-btn>
        </div>
    </form>
</template>

<style scoped>

</style>
