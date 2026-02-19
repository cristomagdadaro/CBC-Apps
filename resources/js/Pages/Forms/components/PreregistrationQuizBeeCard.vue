<script>
import SubformMixin from "@/Modules/mixins/SubformMixin";
import SubformResponse from "@/Modules/domain/SubformResponse";
import DtoResponse from "@/Modules/dto/DtoResponse";

export default {
    name: "PreregistrationQuizBeeCard",
    mixins: [SubformMixin],
    props: {
        responseData: {
            type: Object,
            default: null,
        },
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
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
        async handleUpdate() {
            const response = await this.submitUpdate(null, ['response_data','form_parent_id','subform_type']);
            if (response instanceof DtoResponse) {
                this.showSuccess = true;
                this.$emit('updatedModel', response.data);
            }
        },
    },
    beforeMount() {
        this.model = new SubformResponse();
        if (this.isEditMode) {
            this.setFormAction('update');
            this.form.id = this.responseData.id;
            // Ensure all response_data fields are preserved, including address fields
            this.form.response_data = Object.assign({}, this.responseData.response_data || {});
        } else {
            this.setFormAction('create').response_data = SubformResponse.getSubformFields('preregistration_biotech');
            this.form.response_data.event_id = this.eventId;
            this.form.form_parent_id = this.eventId;
            this.form.response_data.region_address = 'REGION III';
        }
        this.form.subform_type = 'preregistration_biotech';
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
                    <div class="text-xl w-full flex flex-col gap-1 justify-center mb-1 py-2">
                        {{ registrationIDHashed }}
                        <qrcode-vue
                            v-if="registrationIDHashed"
                            :value="registrationIDHashed"
                            :size="size"
                            level="H"
                            render-as="canvas"
                            class="mx-auto border-4 shadow"
                            ref="qrcodeCanvas"
                        />
                    </div>
                    <span class="drop-shadow leading-none font-light">
                        Pre-registration Successful!
                    </span>
                    <span class="drop-shadow leading-none text-sm">
                        Check your email or take a screenshot
                    </span>
                </div>
            </div>
        </transition-container>
        <div class="pb-3 pt-1">
            <h3 class="text-lg leading-tight uppercase font-extrabold">
                {{ isEditMode ? 'Update Pre-registration' : 'Pre-register Now!' }}
            </h3> 
            <p class="text-sm leading-tight">
                Tell us where you're studying and whether you'd like to compete in tomorrow's biotech quiz bee. Fields marked with <span class="text-red-600">*</span> are required.
            </p>
            <label class="text-red-700 uppercase justify-center flex text-sm leading-tight">{{ form.errors.suspended || form.errors.full || form.errors.expired || form.errors.limit }}</label>
        </div>
        <div class="flex flex-col gap-3">
            <div class="flex flex-row gap-2 items-center">
                <TextInput
                    id="name"
                    v-model="form.response_data.name"
                    :error="form.errors.name"
                    autofocus
                    placeholder="Full Name*"
                    autocomplete="name"
                    @input="form.clearErrors('name')"
                />
                <SelectSex
                    v-model="form.response_data.sex"
                    :error="form.errors.sex"
                    placeholder="Select Sex"
                />
            </div>
            <div class="grid grid-cols-3 gap-2">
                <TextInput
                    id="age"
                    v-model="form.response_data.age"
                    type="number"
                    :error="form.errors.age"
                    placeholder="Age"
                    autocomplete="age"
                    @input="form.clearErrors('age')"
                />
                <div :class="{'border-red-500' : form.errors.is_ip}" class="w-full relative px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white rounded-md border border-gray-600 " @click.prevent="form.response_data.is_ip = !form.response_data.is_ip">
                    <label class="text-xs">Are you a member of indigenous people?</label>
                    <Checkbox id="is_ip" v-model="form.response_data.is_ip" :checked="form.response_data.is_ip" autofocus autocomplete="is_ip"/>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.is_ip" class="absolute -top-1 left-3" :message="form.errors.is_ip" />
                    </transition-container>
                </div>
                <div :class="{'border-red-500' : form.errors.is_pwd}" class="w-full relative px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white rounded-md border border-gray-600 " @click.prevent="form.response_data.is_pwd = !form.response_data.is_pwd">
                    <label class="text-xs">Are you a person with disability?</label>
                    <Checkbox id="is_pwd" v-model="form.response_data.is_pwd" :checked="form.response_data.is_pwd" autofocus autocomplete="is_pwd"/>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.is_pwd" class="absolute -top-1 left-3" :message="form.errors.is_pwd" />
                    </transition-container>
                </div>
            </div>
            <TextInput
                id="school"
                v-model="form.response_data.organization"
                type="text"
                :error="form.errors.organization"
                placeholder="Name of School*"
                autocomplete="organization"
                @input="form.clearErrors('organization')"
            />
            <TextInput
                id="designation"
                v-model="form.response_data.designation"
                type="text"
                :error="form.errors.designation"
                placeholder="Grade Level / Position"
                autocomplete="designation"
                @input="form.clearErrors('designation')"
            />
            <div class="grid grid-cols-2 gap-2">
                <TextInput
                    id="email"
                    v-model="form.response_data.email"
                    type="text"
                    :error="form.errors.email"
                    placeholder="Email*"
                    autocomplete="email"
                    @input="form.clearErrors('email')"
                />
                <TextInput
                    id="phone"
                    v-model="form.response_data.phone"
                    type="text"
                    :error="form.errors.phone"
                    placeholder="Phone*"
                    autocomplete="phone"
                    @input="form.clearErrors('phone')"
                />
            </div>
             <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <SelectRegion
                    v-model="form.response_data.region_address"
                    :error="form.errors.region_address"
                    disabled
                    />
                <SelectProvince
                    v-model="form.response_data.province_address"
                    :error="form.errors.province_address"
                    :region="form.response_data.region_address"
                />
                <SelectCity
                    v-model="form.response_data.city_address"
                    :error="form.errors.city_address"
                    :region="form.response_data.region_address"
                    :province="form.response_data.province_address"
                />
            </div>
            <div class="grid grid-cols-1 gap-2">
                <TextInput
                    id="country"
                    v-model="form.response_data.country_address"
                    type="text"
                    :error="form.errors.country_address"
                    placeholder="Country"
                    autocomplete="country"
                    @input="form.clearErrors('country_address')"
                />
            </div>
            <div class="flex flex-col gap-2">
                <custom-dropdown v-if="config?.config?.attendance_type_required" :value="form.response_data.attendance_type" @selectedChange="form.response_data.attendance_type = $event"  :error="form.errors.attendance_type" placeholder="Are you attending Online or In-person?" :required="config?.config?.attendance_type_required" :withAllOption="false" :options="[{name: 'Online', label: 'Online'}, {name: 'In-person', label: 'In-person'}]">
                    <template #icon>
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </template>
                </custom-dropdown>
                <div :class="{'border-red-500' : form.errors.join_quiz_bee}" class="relative flex gap-3 items-center px-3 py-2 bg-white rounded-md border border-gray-600" @click.prevent="form.response_data.join_quiz_bee = !form.response_data.join_quiz_bee">
                    <Checkbox id="join_quiz_bee" v-model="form.response_data.join_quiz_bee" :checked="form.response_data.join_quiz_bee" autocomplete="join_quiz_bee" />
                    <div class="flex flex-col text-left leading-tight">
                        <span class="text-xs font-semibold uppercase">Do you want to join the Biotech Quiz Bee tomorrow (March 6, 2026)?</span>
                        <span class="text-[11px] text-gray-600">Toggle check to receive instructions and the contest details.</span>
                    </div>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.join_quiz_bee" class="absolute -top-1 left-3" :message="form.errors.join_quiz_bee" />
                    </transition-container>
                </div>
                <CertifySection :agreed_tc="form.response_data.agreed_tc" :agreed_updates="form.response_data.agreed_updates" :errors="form.errors" @update:agreed_tc="form.response_data.agreed_tc = $event" @update:agreed_updates="form.response_data.agreed_updates = $event" />
            </div>
            <submit-btn :disabled="model.api.processing" :processing="model.api.processing">
                <span v-if="!model.api.processing">{{ isEditMode ? 'Update' : 'Register' }}</span>
                <span v-else>{{ isEditMode ? 'Updating' : 'Registering' }}</span>
            </submit-btn>
        </div>
    </form>
</template>

<style scoped>

</style>
