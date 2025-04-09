<script>

import {defineComponent} from "vue";
import {Head, Link} from "@inertiajs/vue3";
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
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default defineComponent({
    components: {
        AddIcon, Link,
        FilterIcon,
        LoaderIcon,
        SelectInput,
        Dropdown,
        CaretDown,
        CustomDropdown, SecondaryButton, PrimaryButton, TextField, AuthenticatedLayout, Head},
    props: {
        show: Object,
    },
    data() {
        return {
            api: null,
            form: {
                name: '',
                brand: '',
                category_id: '',
                description: '',
                image: '',
            },
            errors: {},
        }
    },
    mounted() {
        this.api = new CoreApi(route('api.inventory.items.update', this.show.id));
        if(this.$page.props.show){
            this.form = this.show;
        }
    },
    computed: {
        suppliers() {
            return this.$page.props.suppliers.map(supplier => {
                return {
                    name: supplier.id,
                    label: supplier.name,
                }
            });
        },
        categories() {
            return this.$page.props.categories.map(categories => {
                return {
                    name: categories.id,
                    label: categories.name,
                }
            });
        },

    },
    methods: {
        async submit() {
            const response = await this.api.put(this.form)
            if (response instanceof BaseResponse && response.status === 200) {
                this.$inertia.visit(route('items.index'));
            } else if (response instanceof ErrorResponse){
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        cancel() {
            this.form = {
                name: this.show.name,
                brand: this.show.brand,
                category_id: this.show.category_id,
                supplier_id: this.show.supplier_id,
                description: this.show.description,
                image: this.show.image,
            };
        }
    }
})
</script>

<template>
    <Head title="New Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Update Item</h2>
        </template>
        <div class="py-4">
            <div v-if="api" class="flex flex-col gap-5 max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
                <form @submit.prevent="submit" class="flex flex-col gap-2 mx-auto">
                    <text-field required label="Name" name="name" id="name" v-model="form.name" :error="errors.name" />
                    <text-field required label="Brand" name="brand" id="brand" v-model="form.brand" :error="errors.brand" />
                    <div class="flex flex-row gap-2">
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="form.supplier_id"
                            :options="suppliers"
                            placeholder="Select Supplier"
                            label="Supplier"
                            class="w-3/4"
                            :error="errors.supplier_id"
                            @selectedChange="form.supplier_id = $event"
                        >
                            <template #icon>
                                <filter-icon class="h-4 w-4" />
                            </template>
                        </custom-dropdown>
                        <div class="flex items-end">
                            <Link :href="route('suppliers.create')" class="h-fit w-full py-3 shadow flex items-center justify-center bg-white text-gray-600 rounded gap-1 text-sm px-2">
                                <add-icon class="h-5 w-5" />
                                <span class="whitespace-nowrap">New Supplier</span>
                            </Link>
                        </div>
                    </div>
                    <custom-dropdown
                        required
                        searchable
                        :with-all-option="false"
                        :value="form.category_id"
                        :options="categories"
                        placeholder="Select Category"
                        label="Category"
                        :error="errors.category_id"
                        @selectedChange="form.category_id = $event"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                    <text-field label="Description" name="description" id="description" type-input="longtext" v-model="form.description" :error="errors.description" />
                    <text-field label="Image" name="image" id="image" type-input="file" v-model="form.image" :error="errors.image" />
                    <div v-if="form.image" class="w-full select-none shadow bg-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md border p-2 justify-center flex">
                        <img :src="form.image" @click.right.prevent="null" draggable="false" class="max-w-80 w-1/2 bg-transparent" alt="image">
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
                            Date Created: {{ show.created_at }}
                        </span>
                        <span>
                            Last Updated: {{ show.updated_at }}
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
