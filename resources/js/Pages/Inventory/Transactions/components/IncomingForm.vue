<script>
import QrcodeVue from 'qrcode.vue';
import { createCanvas } from "canvas";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";

export default {
    name: "IncomingForm",
    props: {
        attachedReports: {
            type: Array,
            default: () => [],
        },
    },
    components: {
        TransactionHeaderAction,
        TransactionReportAccordion,
        AuditInfoCard,
        QrcodeVue,
    },
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            noModelApi: null,
            barcodeCanvas: null,
            svgText: '',
            showNewItemForm: false,
        }
    },
    emits: ['showNewItemForm'],
    methods: {
        async submitForm() {
            if (this.isUpdate) {
                await this.submitUpdate();
                return;
            }

            await this.submitCreate();
        },
        async generateBarcode(room) {
            if (!room) {
                return;
            }

            await this.fetchGetApi('api.inventory.transactions.genbarcode', { room: room }).then(response => {
                this.form.barcode = response.data.barcode;
                this.renderBarcode();
            });
        },
        resetIncomingForm() {
            if (this.isUpdate) {
                this.resetField(this.data);
                return;
            }

            this.resetField(this.$page.props.data);
        },
        renderBarcode() {
            const canvas = createCanvas(256, 256);
            JsBarcode(canvas, this.form.barcode, {
                displayValue: true,
                fontSize: 20,
                textMargin: 5,
                width: 2,
                height: 60,
            });
            this.svgText = canvas.toDataURL();
        },
    },
    computed: {
        isUpdate() {
            return !!this.data?.id;
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
        preGenerateBarcode() {
            return this.$page.props.barcode;
        },
        selectedStorage() {
            if (this.form?.barcode && this.form.barcode.length >= 6) {
                return this.form.barcode.substring(4, 6);
            }

            return null;
        },
        attachedReportsList() {
            return Array.isArray(this.attachedReports) ? this.attachedReports : [];
        },
        print() {
            const img = document.getElementById('barcode-image');

            if (!img || !img.src) {
                console.error('Barcode image element or source is missing.');
                return;
            }

            try {
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    throw new Error('Failed to open print window.');
                }

                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Barcode</title>
                        </head>
                        <body style="margin: 96px;">
                            <img src="${img.src}"  alt="barcode generated"/>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
                printWindow.close();
            } catch (error) {
                console.error('Error while printing:', error);
            }
        },
        toggleShowNewItemForm() {
            this.showNewItemForm = !this.showNewItemForm;
            this.$emit('showNewItemForm', this.showNewItemForm);
        },
    },
    watch: {
        'form.barcode': function () {
            this.renderBarcode();
        },
        'form.item_id': function (val) {
            const selectedItem = typeof val === 'object' && val !== null
                ? val
                : (this.$page.props?.items ?? []).find(item => item.id === val);

            if (!selectedItem) {
                if (!val) {
                    this.form.unit_price = null;
                    this.form.unit = null;
                    this.form.total_cost = null;
                }

                return;
            }

            this.form.unit_price = selectedItem.unit_price ?? null;
            this.form.unit = selectedItem.unit ?? this.form.unit;
            this.form.total_cost = this.form.quantity && this.form.unit_price
                ? this.form.unit_price * this.form.quantity
                : null;
        },
        'form.quantity': function (val) {
            this.form.total_cost = this.form.unit_price ? this.form.unit_price * val : null;
        },
        'form.unit_price': function (val) {
            this.form.total_cost = this.form.quantity ? val * this.form.quantity : null;
        },
    },
    async mounted() {
        this.model = new Transaction();
        this.setFormAction(this.isUpdate ? 'update' : 'create');

        if (!this.form.transac_type) {
            this.form.transac_type = 'incoming';
        }

        if (!this.isUpdate) {
            this.form.barcode = this.preGenerateBarcode;
            this.form.user_id = this.$page.props?.auth?.user?.id;
            await this.generateBarcode();
            return;
        }

        this.renderBarcode();
    },
}
</script>

<template>
    <table class="w-1/4 bg-white dark:bg-gray-800 border-collapse border m-5 rounded-lg">
        <thead class="bg-AA text-white">
            <tr>
                <th class="border px-2 py-1 text-center" colspan="2">Storage Location Reference</th>
            </tr>
            <tr>
                <th class="border px-2 py-1 text-center">Room #</th>
                <th class="border px-2 py-1">Label</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="location in storage_locations" :key="location.name">
                <td class="border px-2 py-1 text-sm text-center">{{ location.name }}</td>
                <td class="border px-2 py-1 text-sm">{{ location.label }}</td>
            </tr>
        </tbody>
    </table>
    <form v-if="!!form" @submit.prevent="submitForm"  class="py-12 max-w-xl mx-auto">
        <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg h-fit">
            <div class="flex flex-col gap-2 mx-auto w-full h-fit">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">{{ isUpdate ? 'Update Incoming Transaction Details' : 'Incoming Transaction Form' }}</h2>
                    <p>{{ isUpdate ? 'Please use this form to update details of an incoming transaction.' : 'Please use this form to submit details of an incoming transaction.' }}</p>
                </div>
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="w-full"
                    :reports="attachedReportsList"
                />
                <div class="flex flex-row gap-2 h-fit">
                    <select-search-field :disabled="isUpdate" required :api-link="'api.inventory.items.options'"  :error="form.errors.item_id" label="Item" v-model="form.item_id" />
                    <div v-if="!isUpdate" class="flex items-end">
                        <button v-if="!showNewItemForm" @click.prevent="toggleShowNewItemForm" class="h-fit w-full py-2.5 border border-gray-700 flex items-center justify-center bg-white text-gray-600 rounded gap-1 text-sm px-2">
                            <add-icon class="h-5 w-5" />
                            <span class="whitespace-nowrap">New Item</span>
                        </button>

                        <button v-else @click.prevent="toggleShowNewItemForm" class="h-fit w-full py-2.5 border border-gray-700 flex items-center justify-center bg-white text-red-600 rounded gap-1 text-sm px-2">
                            <delete-icon class="h-5 w-5" />
                            <span class="whitespace-nowrap">Close Item Form</span>
                        </button>
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
                    <text-input label="Condition" v-model="form.condition" :error="form.errors.condition" class="col-span-2" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <text-input required type="number" label="Quantity" v-model="form.quantity" :error="form.errors.quantity" />
                    <text-input type="number" label="Unit Price" v-model="form.unit_price" :error="form.errors.unit_price" />
                    <text-input required label="Unit" v-model="form.unit" :error="form.errors.unit" />
                    <text-input type="number" label="Total Cost" v-model="form.total_cost" :error="form.errors.total_cost" />
                </div>

                <date-input type="date" label="Expiration" v-model="form.expiration" :error="form.errors.expiration" />
                <text-area label="PR Details/Remarks" v-model="form.remarks" :error="form.errors.remarks" />
                <div v-if="svgText && selectedStorage" class="flex sm:flex-row flex-col gap-1 w-full items-center relative">
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
                <reset-btn @click="resetIncomingForm">
                    Reset
                </reset-btn>
                <submit-btn :disabled="model.api.processing">
                    <span v-if="model.api.processing">{{ isUpdate ? 'Updating' : 'Saving' }}</span>
                    <span v-else>{{ isUpdate ? 'Update' : 'Save' }}</span>
                </submit-btn>
            </div>
            <audit-info-card
                v-if="isUpdate"
                :audit-logs="$page.props.auditLogs"
                :created-at="data?.created_at"
                :updated-at="data?.updated_at"
            />
        </div>
    </form>
</template>

<style scoped>

</style>
