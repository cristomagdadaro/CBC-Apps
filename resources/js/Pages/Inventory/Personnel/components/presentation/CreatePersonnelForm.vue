<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import TextField from "@/Components/TextField.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "CreatePersonnelForm",
    components: {SelectInput, PrimaryButton, SecondaryButton, TextField, LoaderIcon, Head, AuthenticatedLayout},
    data() {
        return {
            api: null,
            form: {
                fname: null,
                mname: null,
                lname: null,
                suffix: null,
                position: null,
                phone: null,
                address: null,
                email: null,
            },
            errors: {},
        }
    },
    mounted() {
        this.api = new CoreApi(route('api.inventory.personnels.store'));
    },
    methods: {
        async submit() {
            const response = await this.api.post(this.form)
            if (response instanceof BaseResponse && response.status === 201) {
                this.$inertia.visit(route('personnels.index'));
            } else if (response instanceof ErrorResponse && response.status === 422) {
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        cancel() {
            this.form = {
                fname: null,
                mname: null,
                lname: null,
                suffix: null,
                position: null,
                phone: null,
                address: null,
                email: null,
            }
        }
    }
}
</script>

<template>
    <Head title="New Personnel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Register New Personnel</h2>
        </template>
        <div class="py-4">
            <div v-if="api" class="flex flex-row gap-5 max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
                <form @submit.prevent="submit" class="flex flex-col sm:w-fit w-full gap-2 mx-auto">
                    <div class="flex sm:flex-row flex-col gap-1">
                        <text-field class="w-full" required label="First Name" name="fname" id="fname" v-model="form.fname" :error="errors.fname" />
                        <text-field class="w-full" label="Middle Name" name="mname" id="mname" v-model="form.mname" :error="errors.mname" />
                        <text-field class="w-full" required label="Last Name" name="lname" id="lname" v-model="form.lname" :error="errors.lname" />
                        <text-field class="w-1/8" label="Suffix" name="suffix" id="suffix" v-model="form.suffix" :error="errors.suffix" />
                    </div>
                    <div class="flex sm:flex-row flex-col gap-1">
                        <text-field class="w-full" required label="Position" name="position" id="position" v-model="form.position" :error="errors.position" />
                        <text-field class="w-full" label="Phone" name="phone" id="phone" v-model="form.phone" :error="errors.phone" />
                        <text-field class="w-full" type-input="email" label="Email" name="email" id="email" v-model="form.email" :error="errors.email" />
                    </div>
                    <text-field label="Address" name="address" id="address" v-model="form.address" :error="errors.address" />
                    <div class="flex justify-between mt-5">
                        <secondary-button @click="cancel" class="w-1/4">Clear</secondary-button>
                        <primary-button type="submit" class="w-1/4 text-center">
                            <div v-if="api.processing">
                                <loader-icon class="w-5 h-5" />
                            </div>
                            <span v-else>Save</span>
                        </primary-button>
                    </div>
                </form>
            </div>
            <div v-else>
                Can't initialize the form
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
