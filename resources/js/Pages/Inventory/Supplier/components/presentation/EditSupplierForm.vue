<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import TextField from "@/Components/TextField.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {Head} from "@inertiajs/vue3";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "EditSupplierForm",
    components: {Head, SecondaryButton, AuthenticatedLayout, TextField, LoaderIcon, PrimaryButton},
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
        this.api = new CoreApi(route('api.inventory.suppliers.update', this.$page.props.show.id));

        if(this.$page.props.show){
            this.form = this.$page.props.show;
        }
    },
    methods: {
        async submit() {
            const response = await this.api.put(this.form)
            if (response instanceof BaseResponse && response.status === 200) {
                window.history.back();
            } else if (response instanceof ErrorResponse && response.status === 422) {
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        cancel() {
            this.form = {
                name: this.$page.props.show.name,
                phone: this.$page.props.show.phone,
                email: this.$page.props.show.email,
                address: this.$page.props.show.address,
                description: this.$page.props.show.description,
            };
        }
    }
}
</script>

<template>
    <Head title="Update Supplier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Update Supplier</h2>
        </template>
        <div class="py-4">
            <div v-if="api" class="flex flex-col gap-5 max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
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
                        <secondary-button @click="cancel">Reset</secondary-button>
                        <primary-button type="submit" class="text-center">
                            <div v-if="api.processing">
                                <loader-icon class="w-5 h-5" />
                            </div>
                            <span v-else>Update</span>
                        </primary-button>
                    </div>
                    <div class="flex flex-col w-full text-xs text-gray-400 border-t border-gray-500 pt-1">
                        <span>
                            Date Created: {{ $page.props.show.created_at }}
                        </span>
                        <span>
                            Last Updated: {{ $page.props.show.updated_at }}
                        </span>
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
