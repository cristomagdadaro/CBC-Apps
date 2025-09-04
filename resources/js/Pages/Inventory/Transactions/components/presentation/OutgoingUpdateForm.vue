<script>
import {Head} from "@inertiajs/vue3";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/presentation/TransactionHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import TextInput from "@/Components/TextInput.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";

export default {
    name: "OutgoingUpdateForm",
    components: {CancelBtn, SubmitBtn, TextInput, TransactionHeaderAction, AppLayout, CustomDropdown, FilterIcon, Head},
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
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('create');
    },
}
</script>

<template>
    <app-layout title="Outgoing" >
        <template #header>
            <transaction-header-action />
        </template>
        <div class="py-4" v-if="!!form">
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
                        <text-input
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
                        <text-input
                            required
                            label="Project Code"
                            name="project_code"
                            id="project_code"
                            v-model="form.project_code"
                            :error="form.errors.project_code"
                        />
                        <text-input
                            required
                            type-input="number"
                            autocomplete="off"
                            label="Quantity"
                            name="quantity"
                            id="quantity"
                            v-model="form.quantity"
                            :error="errors.quantity"
                        />
                        <div class="flex gap-1 justify-between">
                            <cancel-btn @click="resetForm">
                                Cancel
                            </cancel-btn>
                            <submit-btn :disabled="model.api.processing">
                                <span v-if="model.api.processing">Saving</span>
                                <span v-else>Save</span>
                            </submit-btn>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>

</template>

<style scoped>

</style>
