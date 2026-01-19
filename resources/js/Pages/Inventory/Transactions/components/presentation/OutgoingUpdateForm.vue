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
import BaseResponse from "@/Modules/DataTable/domain/BaseResponse";
import ErrorResponse from "@/Modules/DataTable/domain/ErrorResponse";
import TextArea from "@/Components/TextArea.vue";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/presentation/TransactionReportAccordion.vue";

export default {
    name: "OutgoingUpdateForm",
    components: {
        TextArea,
        TransactionReportAccordion,
        CancelBtn, SubmitBtn, TextInput, TransactionHeaderAction, AppLayout, CustomDropdown, FilterIcon, Head},
    props: {
        data: Object,
        summary: Object,
        attachedReports: {
            type: Array,
            default: () => [],
        }
    },
    data() {
        return {
            api: null,
            form: {
                id: this.data.id,
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
        reportsList() {
            return Array.isArray(this.attachedReports) ? this.attachedReports : [];
        },
    },
    methods: {
        fullName(personnel) {
            return personnel.fname + ' ' + personnel.lname;
        },
        reset() {
            this.form = Object.assign({}, this.data);
        },
        async submit() {
            const response = await this.submitUpdate();
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
        this.setFormAction('update');
    },
    mounted() {
        if (!this.form.user_id) {
            this.form.user_id = this.$page.props.auth.user.id;
        }
    }
}
</script>

<template>
    <app-layout title="Outgoing" >
        <template #header>
            <transaction-header-action />
        </template>
        <div class="py-4" v-if="!!form">
            <div class="flex flex-col sm:p-5 p-3 sm:gap-3 gap-1 bg-white shadow rounded max-w-xl mx-auto">
                <div v-if="data" class="flex select-none justify-between items-center gap-5 py-2 px-1 md:px-4 border-b">
                    <div class="flex flex-col">
                        <span class="font-bold text-base md:text-lg whitespace-nowrap overflow-ellipsis overflow-hidden">
                            {{ summary.name }} {{ summary.description ? `(${summary.description})` : '' }}
                        </span>
                        <span class="text-sm text-gray-500">{{ summary.brand }}</span>
                        <span class="text-xs text-gray-500 leading-none" :class="{'text-red-600' : !summary.barcode}">{{ data.barcode || 'Warning! NO BARCODE' }}</span>
                    </div>
                    <div class="flex sm:gap-4 gap-1">
                        <div class="flex flex-col leading-none md:leading-relaxed">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(summary.remaining_quantity) }}
                                </span>
                            <span class="text-xs text-gray-400">
                                    Remaining
                                </span>
                        </div>
                        <div class="flex flex-col leading-none md:leading-relaxed">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(summary.total_outgoing) }}
                                </span>
                            <span class="text-xs text-gray-400">
                                    Consumed
                                </span>
                        </div>
                        <div class="flex flex-col leading-none md:leading-relaxed">
                                <span class="text-center text-gray-600">
                                    {{ (100-((summary.remaining_quantity/summary.total_ingoing) *100)).toFixed(2)  }}%
                                </span>
                            <span class="text-xs text-gray-400">
                                    Utilization
                                </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:gap-3 gap-1 sm:p-3 p-1 rounded">
                    <transaction-report-accordion
                        class="w-full mb-3"
                        :reports="reportsList"
                    />
                    <form @submit.prevent="submit" class="flex flex-col gap-3">
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="form.personnel_id"
                            :options="personnels"
                            placeholder="Select Personnel"
                            label="Personnel"
                            :error="form.errors.personnel_id"
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
                            :error="form.errors.quantity"
                        />

                        <text-input
                            required
                            type-input="number"
                            autocomplete="off"
                            label="Quantity"
                            name="quantity"
                            id="quantity"
                            v-model="form.quantity"
                            :error="form.errors.quantity"
                        />
                        <text-area
                            required
                            type-input="number"
                            autocomplete="off"
                            label="Purpose"
                            name="purpose"
                            id="purpose"
                            v-model="form.remarks"
                            :error="form.errors.remarks"
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
