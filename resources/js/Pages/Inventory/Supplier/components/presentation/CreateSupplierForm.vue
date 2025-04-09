<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import TextField from "@/Components/TextField.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "CreateSupplierForm",
    components: {SecondaryButton, LoaderIcon, PrimaryButton, TextField, Head, AuthenticatedLayout},
    data() {
        return {
            api: null,
            form: {
                name: null,
                address: null,
                phone: null,
                email: null,
                description: null,
            },
            errors: {},
        }
    },
    mounted() {
        this.api = new CoreApi(route('api.inventory.suppliers.store'));
    },
    methods: {
        async submit() {
            const response = await this.api.post(this.form)
            if (response instanceof BaseResponse && response.status === 201) {
                window.history.back();
            } else if (response instanceof ErrorResponse && response.status === 422) {
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        cancel() {
            this.form = {
                name: null,
                address: null,
                phone: null,
                email: null,
                description: null,
            }
        }
    }
}
</script>

<template>
    <Head title="New Supplier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Register New Supplier</h2>
        </template>
        <div class="py-4">
            <div v-if="api" class="flex flex-row gap-5 max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
                <form @submit.prevent="submit" class="flex flex-col sm:w-fit w-full gap-2 mx-auto">
                    <div class="flex flex-col gap-1">
                        <text-field class="w-full" required label="Company Name" name="name" id="name" v-model="form.name" :error="errors.name" />
                        <div class="flex sm:flex-row flex-col gap-2">
                            <text-field type-input="email" class="w-full" required label="Email" name="email" id="email" v-model="form.email" :error="errors.email" />
                            <text-field class="w-full" required label="Phone" name="phone" id="phone" v-model="form.phone" :error="errors.phone" />
                        </div>
                        <text-field class="w-full" required label="Address" name="address" id="address" v-model="form.address" :error="errors.address" />
                        <text-field type-input="longtext" class="w-full" label="Description" name="description" id="description" v-model="form.description" :error="errors.description" />
                    </div>
                    <div class="flex justify-between mt-3">
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
