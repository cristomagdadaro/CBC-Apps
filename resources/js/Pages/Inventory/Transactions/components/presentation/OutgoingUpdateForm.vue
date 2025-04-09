<script>
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import TextField from "@/Components/TextField.vue";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "OutgoingUpdateForm",
    components: {TextField, CustomDropdown, FilterIcon, AuthenticatedLayout, Head},
    props: {
        show: Object,
    },
    data() {
        return {
            api: null,
            errors: {},
            form: {
                id: this.show.id,
                personnel_id: null,
                quantity: 0,
                item_id: null,
                unit: null,
                remarks: null,
            },
        }
    },
    computed: {
        personnels() {
            return this.$page.props.personnels.map(personnel => {
                return {
                    name: personnel.id,
                    label: this.fullName(personnel),
                }
            });
        },
    },
    methods: {
        fullName(personnel) {
            return personnel.fname + ' ' + personnel.lname;
        },
        reset() {
            this.form = Object.assign({}, this.show);
        },
        async submit() {
            const response = await this.api.put(this.form)
            if (response instanceof BaseResponse && response.status === 200) {
                this.$inertia.visit(route('transactions.index'));
            } else if (response instanceof ErrorResponse){
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        formatNumber(value){
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
    },
    mounted() {
        this.api = new CoreApi(route('api.inventory.transactions.outgoing', this.show.id));
        if(this.show){
            this.reset();
        }
    },
}
</script>

<template>
    <Head title="Outgoing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold sm:text-xl text-sm text-gray-800 leading-tight uppercase">Outgoing Transaction Update</h2>
        </template>
        <div class="py-4" v-if="api">
            <div class="flex flex-col sm:p-5 p-2 sm:gap-3 gap-1 bg-white shadow rounded max-w-xl mx-auto">
                <div v-if="show" class="flex select-none justify-between items-center gap-5 py-2 px-4 border-b">
                    <div class="flex flex-col">
                        <span class="font-bold text-lg whitespace-nowrap overflow-ellipsis overflow-hidden">
                            {{ show.name }} ({{ show.unit }})
                        </span>
                        <span class="text-sm text-gray-500">{{ show.brand }}</span>
                    </div>
                    <div class="flex sm:gap-4 gap-1">
                        <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(show.remaining_quantity) }}
                                </span>
                            <span class="text-xs text-gray-400">
                                    Remaining
                                </span>
                        </div>
                        <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(show.total_outgoing) }}
                                </span>
                            <span class="text-xs text-gray-400">
                                    Consumed
                                </span>
                        </div>
                        <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ (100-((show.remaining_quantity/show.total_ingoing) *100)).toFixed(2)  }}%
                                </span>
                            <span class="text-xs text-gray-400">
                                    Utilization
                                </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:gap-3 gap-1 border sm:p-3 p-1 rounded bg-gray-100">
                    <form @submit.prevent="submit" class="flex flex-col gap-3">
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="form.personnel_id"
                            :options="personnels"
                            placeholder="Select Personnel"
                            label="Personnel"
                            :error="errors.personnel_id"
                            @selectedChange="form.personnel_id = $event"
                            class="w-full"
                        >
                            <template #icon>
                                <filter-icon class="h-4 w-4" />
                            </template>
                        </custom-dropdown>
                        <text-field
                            required
                            type-input="number"
                            autocomplete="off"
                            label="Quantity"
                            name="quantity"
                            id="quantity"
                            class="hidden"
                            v-model="form.quantity"
                            :error="errors.quantity"
                        />
                        <text-field
                            required
                            type-input="number"
                            autocomplete="off"
                            label="Quantity"
                            name="quantity"
                            id="quantity"
                            v-model="form.quantity"
                            :error="errors.quantity"
                        />
                        <div class="flex justify-between">
                            <button @click="reset" type="button" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 active:scale-95 duration-75">Reset</button>
                            <button @click="submit" type="button" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 active:scale-95 duration-75">
                                <span v-if="!api.processing">Submit</span>
                                <span v-else>Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>

</style>
