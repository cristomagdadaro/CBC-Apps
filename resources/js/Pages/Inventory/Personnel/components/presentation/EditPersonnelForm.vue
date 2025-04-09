<script>

import {defineComponent} from "vue";
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextField from "@/Components/TextField.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import CaretDown from "@/Components/Icons/CaretDown.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import Dropdown from "@/Components/Dropdown.vue";
import SelectInput from "@/Components/SelectInput.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default defineComponent({
    components: {
        LoaderIcon,
        SelectInput,
        Dropdown,
        CaretDown,
        CustomDropdown, SecondaryButton, PrimaryButton, TextField, AuthenticatedLayout, Head},
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
        this.api = new CoreApi(route('api.inventory.personnels.update', this.$page.props.show.id));

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
                fname: this.$page.props.show.fname,
                mname: this.$page.props.show.mname,
                lname: this.$page.props.show.lname,
                suffix: this.$page.props.show.suffix,
                position: this.$page.props.show.position,
                phone: this.$page.props.show.phone,
                email: this.$page.props.show.email,
                address: this.$page.props.show.address,
            };
        }
    }
})
</script>

<template>
    <Head title="Update Personnel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Update Personnel</h2>
        </template>
        <div class="py-4">
            <div v-if="api" class="flex flex-col gap-5 max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
                <form @submit.prevent="submit" class="flex flex-col gap-2 mx-auto">
                    <div class="flex flex-row gap-1">
                        <text-field class="w-full" required label="First Name" name="fname" id="fname" v-model="form.fname" :error="errors.fname" />
                        <text-field class="w-full" label="Middle Name" name="mname" id="mname" v-model="form.mname" :error="errors.mname" />
                        <text-field class="w-full" required label="Last Name" name="lname" id="lname" v-model="form.lname" :error="errors.lname" />
                        <text-field class="w-1/8" label="Suffix" name="suffix" id="suffix" v-model="form.suffix" :error="errors.suffix" />
                    </div>
                    <div class="flex flex-row gap-1">
                        <text-field class="w-full" required label="Position" name="position" id="position" v-model="form.position" :error="errors.position" />
                        <text-field class="w-full" label="Phone" name="phone" id="phone" v-model="form.phone" :error="errors.phone" />
                        <text-field class="w-full" type-input="email" label="Email" name="email" id="email" v-model="form.email" :error="errors.email" />
                    </div>
                    <text-field label="Address" name="address" id="address" v-model="form.address" :error="errors.address" />
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
