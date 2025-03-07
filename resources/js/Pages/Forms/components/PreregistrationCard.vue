<script>
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Form from "@/Modules/domain/Form";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

export default {
    name: "PreregistrationCard",
    components: {TransitionContainer, PrimaryButton, Checkbox, Dropdown, DropdownLink, InputError, InputLabel, TextInput},
    props: {
      eventId: [String, Number],
    },
    data() {
        return {
            form: useForm(Form.createFields),
            model: null,
            showSuccess: false,
            registrationIDHashed: null,
        }
    },
    mounted() {
        this.model = new Form();
    },
    methods: {
        async submitRegistrationCard() {
            if (this.eventId)
                this.form.event_id = this.eventId;
            else
            {
                alert("No event id is provided");
                return;
            }
            const response = await this.model.postIndex(this.form.data());
            this.checkResponseStatus(response);
        },
        checkResponseStatus(response) {
            this.showSuccess = response.status === 'success';
            this.registrationIDHashed = response.participant_hash;
        }
    },
}
</script>

<template>
    <form  @submit.prevent="submitRegistrationCard" class="px-1 py-4 select-none relative border-t border-gray-800 mt-3">
        <transition-container type="pop-in">
            <div v-show="showSuccess" class="absolute flex top-0 left-0 bg-AC rounded-md bg-opacity-75 w-full h-full z-50 text-white text-xl font-medium justify-center items-center">
                <button @click.prevent="showSuccess = false; form.reset()" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <div class="drop-shadow text-5xl bg-AC w-full">
                        {{ registrationIDHashed }}
                    </div>
                    <span class="drop-shadow leading-none font-light">
                        Registeration Successful!
                    </span>
                    <span class="drop-shadow leading-none text-sm">
                        Save
                    </span>
                </div>
            </div>
        </transition-container>
        <div class="flex flex-col gap-2">
            <div class="flex flex-row gap-2 items-center">
                <div class="w-full">
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full"
                        required
                        autofocus
                        placeholder="Name"
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div class="w-[10rem]">
                    <Dropdown align="right" width="60">
                        <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ form.sex ?? 'Sex' }}
                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </span>
                        </template>

                        <template #content>
                            <div class="w-60">
                                <DropdownLink as="button" @click.prevent="form.sex = 'Male'">
                                    Male
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.sex = 'Female'">
                                    Female
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.sex = 'Prefer not to say'">
                                    Prefer not to say
                                </DropdownLink>
                            </div>
                        </template>
                    </Dropdown>
                    <InputError class="mt-2" :message="form.errors.sex" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="w-full">
                    <TextInput
                        id="age"
                        v-model="form.age"
                        type="number"
                        class="mt-1 w-full"
                        required
                        placeholder="Age"
                        autocomplete="age"
                    />
                    <InputError class="mt-2" :message="form.errors.age" />
                </div>
                <div class="w-full px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white border rounded-md shadow-sm" @click.prevent="form.is_ip = !form.is_ip">
                    <label class="text-xs">Are you a member indigenous people?</label>
                    <Checkbox v-model="form.is_ip" :checked="form.is_ip" class="mt-1" autofocus autocomplete="is_ip"/>
                    <InputError class="mt-2" :message="form.errors.is_ip" />
                </div>
                <div class="w-full px-2 py-0.5 flex text-center leading-none lg:flex-row flex-col-reverse items-center lg:gap-2 bg-white border rounded-md shadow-sm" @click.prevent="form.is_pwd = !form.is_pwd">
                    <label class="text-xs">Are you a person with disability?</label>
                    <Checkbox v-model="form.is_pwd" :checked="form.is_pwd" class="mt-1" autofocus autocomplete="is_pwd"/>
                    <InputError class="mt-2" :message="form.errors.is_pwd" />
                </div>
            </div>
            <div class="w-full">
                <TextInput
                    id="organization"
                    v-model="form.organization"
                    type="text"
                    class="mt-1 w-full"
                    required
                    placeholder="Organization/Agency"
                    autocomplete="organization"
                />
                <InputError class="mt-2" :message="form.errors.organization" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div class="w-full">
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="text"
                        class="mt-1 w-full"
                        required
                        placeholder="Email"
                        autocomplete="email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div class="w-full">
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="mt-1 w-full"
                        required
                        placeholder="Phone"
                        autocomplete="phone"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
            </div>
            <div class="py-3">
                <p class="text-xs leading-none">
                    By submitting this form, you consent to the DA-Crop Biotechnology Center collecting and using your data in accordance with our privacy policy.
                </p>
            </div>
            <primary-button>
                Register
            </primary-button>
        </div>
    </form>
</template>

<style scoped>

</style>
