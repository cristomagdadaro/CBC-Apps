<script>
import {createCanvas} from "canvas";
import JsBarcode from "jsbarcode";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";

export default {
    name: "IngoingUpdateForm",
    components: {
        AuditInfoCard,
        TransactionReportAccordion,
        TransactionHeaderAction
    },
    props: {
        attachedReports: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            api: null,
            noModelApi: null,
            barcodeCanvas: null,
            svgText: '',
        }
    },
    computed: {
        storage_locations() {
            if (!this.$page.props?.storage_locations)
                return [];
            return this.$page.props.storage_locations.map(location => {
                return {
                    name: location.name,
                    label: location.label,
                }
            });
        },
        items() {
            if (!this.$page.props?.items)
                return [];
            return this.$page.props.items.map(item => {
                const supplement = item.brand || item.description;

                return {
                    name: item.id,
                    label: item.name + (supplement ? ' (' + supplement + ')' : ''),
                }
            });
        },
        personnels() {
            if (!this.$page.props?.personnels)
                return [];
            return this.$page.props.personnels.map(personnel => {
                const fullName = [
                    personnel.lname,
                    ', ',
                    personnel.fname,
                    personnel.mname,
                    personnel.suffix,
                    ' (',
                    personnel.employee_id,
                    ')'
                ].filter(Boolean).join(' ');

                return {
                    name: personnel.id,
                    label: fullName,
                };
            });
        },
        show() {
            return this.$page.props.show;
        },
        selectedStorage() {
            return this.form.barcode.substring(4, 6);
        },
        attachedReportsList() {
            const reports = this.attachedReports ?? this.$page?.props?.attachedReports;
            return Array.isArray(reports) ? reports : [];
        }
    },
    methods: {
        async generateBarcode(room) {
            if (!room) {
                return;
            }

            await this.fetchGetApi('api.inventory.transactions.genbarcode', { room: room }).then(response => {
                this.form.barcode = response.data.barcode;
                this.renderBarcode();
            });;
        },
        renderBarcode(){
            const canvas = createCanvas(256, 256);
            JsBarcode(canvas, this.form.barcode, {
                displayValue: true,
                fontSize: 20,
                textMargin: 5,
                width:2,
                height: 60,
            });
            this.svgText = canvas.toDataURL();
        }
    },
    watch: {
        'form.barcode': {
            handler(val) {
                this.renderBarcode();
            },
            deep: true
        },
    },
    mounted() {
        this.form.transac_type = 'incoming';
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('update');
    }
}
</script>

<template>
    <AppLayout title="Update Transaction Details">
        <template #header>
            <TransactionHeaderAction />
        </template>
        <form v-if="!!form" @submit.prevent="submitUpdate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Update Incoming Transaction Details</h2>
                    <p>Please use this form to update details of an incoming transaction.</p>
                </div>
                <transaction-report-accordion
                    class="w-full"
                    :reports="attachedReportsList"
                />
                <div class="flex flex-col gap-2 mx-auto w-full">
                    <div class="flex flex-row gap-2 h-fit">
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="form.item_id"
                            :options="items"
                            placeholder="Select Item"
                            label="Item"
                            :error="form.errors.item_id"
                            @selectedChange="form.item_id = $event"
                            class="w-3/4"
                        >
                            <template #icon>
                                <filter-icon class="h-4 w-4" />
                            </template>
                        </custom-dropdown>
                        <div class="flex items-end">
                            <Link :href="route('items.create')" class="h-fit w-full py-2.5 border border-gray-700 flex items-center justify-center bg-white text-gray-600 rounded gap-1 text-sm px-2">
                                <add-icon class="h-5 w-5" />
                                <span class="whitespace-nowrap">New Item</span>
                            </Link>
                        </div>
                    </div>
                    <text-input type="text" label="Project Code" required v-model="form.project_code" :error="form.errors.project_code" />
                    <custom-dropdown
                        searchable
                        :with-all-option="false"
                        :value="form.personnel_id"
                        :options="personnels"
                        placeholder="Select Personnel"
                        label="Accountable Personnel"
                        :error="form.errors.personnel_id"
                        @selectedChange="form.personnel_id = $event"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                    <custom-dropdown
                        required
                        :with-all-option="false"
                        :value="selectedStorage"
                        :options="storage_locations"
                        placeholder="Select Storage"
                        label="Storage Location"
                        :error="form.errors.barcode"
                        @selectedChange="generateBarcode($event)"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                    <div class="grid grid-cols-2 gap-2">
                        <text-input label="PRRI Barcode" v-model="form.barcode_prri" :error="form.errors.barcode_prri" />
                        <text-input label="PAR No" v-model="form.par_no" :error="form.errors.par_no" />
                        <text-input label="Condition" v-model="form.condition" :error="form.errors.condition" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <text-input type="number" label="Quantity" v-model="form.quantity" :error="form.errors.quantity" />
                        <text-input type="number" label="Unit Price" v-model="form.unit_price" :error="form.errors.unit_price" />
                        <text-input label="Unit" v-model="form.unit" :error="form.errors.unit" />
                        <text-input type="number" label="Total Cost" v-model="form.total_cost" :error="form.errors.total_cost" />
                    </div>

                    <date-input type="date" label="Expiration" v-model="form.expiration" :error="form.errors.expiration" />
                    <text-area label="PR Details/Remarks" v-model="form.remarks" :error="form.errors.remarks" />

                    <div v-if="svgText" class="flex sm:flex-row flex-col gap-1 w-full relative">
                        <img id="barcode-image" :src="svgText" alt="SVG Image" class="w-full" />
                        <button class="px-5 py-2 bg-gray-300 hover:scale-105 active:scale-100 rounded h-fit absolute bottom-3 right-4" @click.prevent="print">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-auto h-5" viewBox="0 0 16 16">
                                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex gap-1 justify-between">
                    <reset-btn @click="resetField($page.props.data)">
                        Reset
                    </reset-btn>
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Updating</span>
                        <span v-else>Update</span>
                    </submit-btn>
                </div>
                <audit-info-card
                    :audit-logs="$page.props.auditLogs"
                    :created-at="$page.props.data.created_at"
                    :updated-at="$page.props.data.updated_at"
                />
            </div>
        </form>
    </AppLayout>

</template>

<style scoped>

</style>
