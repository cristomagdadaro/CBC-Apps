<script>
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import QrcodeVue, { QrcodeCanvas, QrcodeSvg } from 'qrcode.vue'
import Participant from "@/Modules/domain/Participant";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "RegistrationCard",
    mixins: [ApiMixin, FormLocalMixin],
    components: {
        CustomDropdown,
        SubmitBtn,
        TransitionContainer, PrimaryButton, Checkbox, Dropdown, DropdownLink, InputError, InputLabel, TextInput, QrcodeVue, QrcodeCanvas, QrcodeSvg},
    props: {
      eventId: [String, Number],
        config: Object,
    },
    data() {
        return {
            model: null,
            showSuccess: false,
            registrationIDHashed: null,
            value: 'https://example.com',
            size: 200,
        }
    },
    methods: {
        async handleCreate() {
            const response = await this.submitCreate(null, 'event_id');
            this.showSuccess = response.status === 201;
            if (response instanceof DtoResponse) {
                this.registrationIDHashed = response.data.participant_hash;
                this.saveLocalHashedIds(response.data);
                this.$emit('createdModel', response.data);
            }
        }
    },
    beforeMount() {
        this.model = new Participant();
        this.setFormAction('create');
        this.form.event_id = this.eventId;
    },
    watch: {
        'form.agreed_tc': {
            immediate: true,
            handler() {
                this.form?.clearErrors('agreed_tc');
            }
        }
    }
}
</script>

<template>
    <form v-if="form" @submit.prevent="handleCreate()" class="py-4 select-none relative bg-white px-3 border-t border-gray-800 mt-3" :class="{'border border-red-600 rounded-md': form.hasErrors}">
        <transition-container type="slide-top">
            <div v-show="showSuccess" class="absolute flex top-0 left-0 bg-AC w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow">
                <button @click.prevent="showSuccess = false" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <div class="text-xl bg-AC w-full flex flex-col gap-1 justify-center mb-1 py-2">
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
                        Registration Successful!
                    </span>
                    <span class="drop-shadow leading-none text-sm">
                        Check your email or take a screenshot
                    </span>
                </div>
            </div>
        </transition-container>
        <div class="pb-3 pt-1">
            <label class="text-red-600 uppercase justify-center flex">{{ form.errors.suspended || form.errors.full || form.errors.expired }}</label>
            <h3 class="text-lg leading-tight uppercase font-extrabold">
                Register Now!
            </h3>
            <p class="text-xs leading-none">
                Kindly provide the required and correct details. Fields marked with <span class="text-red-600">*</span> are required.
            </p>
        </div>
        <div class="flex flex-col gap-3">
            <div class="flex flex-row gap-2 items-center">
                <TextInput
                    id="name"
                    v-model="form.name"
                    :error="form.errors.name"
                    autofocus
                    placeholder="Name*"
                    autocomplete="name"
                    @input="form.clearErrors('name')"
                />
                <div class="w-[15rem] relative border border-gray-600 rounded-md">
                    <Dropdown align="right" width="60">
                        <template #trigger>
                            <button type="button" :class="{'border-red-500' : form.errors.sex}" class="inline-flex items-center justify-between px-3 py-3 border shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                {{ form.sex ?? 'Sex' }}
                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="w-60">
                                <DropdownLink as="button" @click.prevent="form.sex = 'Male'" @click="form.clearErrors('sex')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-male text-blue-300" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/>
                                        </svg>
                                        <span>Male</span>
                                    </div>
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.sex = 'Female'" @click="form.clearErrors('sex')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-female text-red-300" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5"/>
                                        </svg>
                                        <span>Female</span>
                                    </div>
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.sex = 'Prefer not to say'" @click="form.clearErrors('sex')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-balloon-heart text-yellow-300" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398"/>
                                        </svg>
                                        <span>Prefer not to say</span>
                                    </div>
                                </DropdownLink>
                            </div>
                        </template>
                    </Dropdown>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.sex" class="absolute -top-1 left-3" :message="form.errors.sex" />
                    </transition-container>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <TextInput
                    id="age"
                    v-model="form.age"
                    type="number"
                    :error="form.errors.age"
                    placeholder="Age"
                    autocomplete="age"
                    @input="form.clearErrors('age')"
                />
                <div :class="{'border-red-500' : form.errors.is_ip}" class="w-full relative px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white rounded-md border border-gray-600 " @click.prevent="form.is_ip = !form.is_ip">
                    <label class="text-xs">Are you a member of indigenous people?</label>
                    <Checkbox id="is_ip" v-model="form.is_ip" :checked="form.is_ip" autofocus autocomplete="is_ip"/>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.is_ip" class="absolute -top-1 left-3" :message="form.errors.is_ip" />
                    </transition-container>
                </div>
                <div :class="{'border-red-500' : form.errors.is_pwd}" class="w-full relative px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white rounded-md border border-gray-600 " @click.prevent="form.is_pwd = !form.is_pwd">
                    <label class="text-xs">Are you a person with disability?</label>
                    <Checkbox id="is_pwd" v-model="form.is_pwd" :checked="form.is_pwd" autofocus autocomplete="is_pwd"/>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.is_pwd" class="absolute -top-1 left-3" :message="form.errors.is_pwd" />
                    </transition-container>
                </div>
            </div>
            <TextInput
                id="organization"
                v-model="form.organization"
                type="text"
                :error="form.errors.organization"
                placeholder="Organization/Agency*"
                autocomplete="organization"
                @input="form.clearErrors('organization')"
            />
            <TextInput
                id="designation"
                v-model="form.designation"
                type="text"
                :error="form.errors.designation"
                placeholder="Designation/Position"
                autocomplete="designation"
                @input="form.clearErrors('designation')"
            />
            <div class="grid grid-cols-2 gap-2">
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="text"
                    :error="form.errors.email"
                    placeholder="Email*"
                    autocomplete="email"
                    @input="form.clearErrors('email')"
                />
                <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    :error="form.errors.phone"
                    placeholder="Phone*"
                    autocomplete="phone"
                    @input="form.clearErrors('phone')"
                />
            </div>
            <div class="grid grid-cols-3 gap-2">
                <TextInput
                    id="city"
                    v-model="form.city_address"
                    type="text"
                    :error="form.errors.city_address"
                    placeholder="City"
                    autocomplete="city"
                    @input="form.clearErrors('city_address')"
                />
                <TextInput
                    id="province"
                    v-model="form.province_address"
                    type="text"
                    :error="form.errors.province_address"
                    placeholder="Province"
                    autocomplete="province"
                    @input="form.clearErrors('province_address')"
                />
                <TextInput
                    id="country"
                    v-model="form.country_address"
                    type="text"
                    :error="form.errors.country_address"
                    placeholder="Country"
                    autocomplete="country"
                    @input="form.clearErrors('country_address')"
                />
            </div>
            <div class="flex flex-col gap-2">
                <custom-dropdown v-if="config.config.attendance_type_required" placeholder="Are you attending Online or In-person?" :required="config.config.attendance_type_required" :withAllOption="false" :options="[{name: 'Online', label: 'Online'}, {name: 'In-person', label: 'In-person'}]" />
                <div class="py-3 flex gap-2">
                    <Checkbox id="agreed_tc" :class="{'border border-red-600' : form.errors.agreed_tc}" v-model="form.agreed_tc" :checked="form.agreed_tc" autocomplete="agreed_tc"/>
                    <p class="text-xs leading-none" @click.prevent="form.agreed_tc = !form.agreed_tc">
                        By submitting this form, you consent to the DA-Crop Biotechnology Center collecting and using your data in accordance with our privacy policy.
                        <transition-container type="slide-bottom">
                            <InputError v-show="!!form.errors.agreed_tc" class="" :message="form.errors.agreed_tc" />
                        </transition-container>
                    </p>
                </div>
            </div>
            <submit-btn :disabled="model.api.processing" :processing="model.api.processing">
                <span v-if="!model.api.processing">Register</span>
                <span v-else>Registering</span>
            </submit-btn>
        </div>
    </form>
</template>

<style scoped>

</style>
