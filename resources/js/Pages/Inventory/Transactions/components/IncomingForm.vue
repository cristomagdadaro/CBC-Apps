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
        attachedComponents: {
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
            componentRows: [],
            rememberFormKey: 'incomingTransactionForm',
        }
    },
    emits: ['showNewItemForm'],
    methods: {
        async submitForm() {
            this.syncComponentsPayload();

            if (this.isUpdate) {
                await this.submitUpdate();
                return;
            }
            await this.submitCreate();
        },
        createEmptyComponentRow() {
            return {
                item_id: null,
                quantity: 1,
                unit: null,
                prri_component_no: null,
                expiration: null,
            };
        },
        addComponentRow() {
            this.componentRows.push(this.createEmptyComponentRow());
            this.syncComponentsPayload();
        },
        removeComponentRow(index) {
            this.componentRows.splice(index, 1);
            this.syncComponentsPayload();
        },
        onComponentItemChange(index, selectedValue) {
            const selectedItem = typeof selectedValue === 'object' && selectedValue !== null
                ? selectedValue
                : (this.$page.props?.items ?? []).find(item => item.id === selectedValue);

            this.componentRows[index].item_id = selectedItem?.id ?? selectedValue;
            this.componentRows[index].unit = selectedItem?.unit ?? null;
            this.syncComponentsPayload();
        },
        syncComponentsPayload() {
            if (!this.form) {
                return;
            }

            this.form.components = this.componentRows
                .filter(component => component.item_id && Number(component.quantity) > 0)
                .map(component => ({
                    item_id: component.item_id,
                    quantity: Number(component.quantity),
                    unit: component.unit,
                    prri_component_no: component.prri_component_no,
                    expiration: component.expiration,
                    barcode_prri: this.form.barcode_prri ?? null,
                }));
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
                this.componentRows = this.mapAttachedComponentsToRows(this.attachedComponentsList);
                this.syncComponentsPayload();
                return;
            }

            this.resetField(this.$page.props.data);
            this.componentRows = [];
            this.syncComponentsPayload();
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
        mapAttachedComponentsToRows(components = []) {
            if (!Array.isArray(components)) {
                return [];
            }

            return components.map(component => ({
                item_id: component.item_id ?? component?.item?.id ?? null,
                quantity: Number(component.quantity ?? 1),
                unit: component.unit ?? component?.item?.unit ?? null,
                prri_component_no: component.prri_component_no ?? null,
                expiration: component.expiration ?? null,
            }));
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
        attachedComponentsList() {
            return Array.isArray(this.attachedComponents) ? this.attachedComponents : [];
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

                const doc = printWindow.document;
                doc.open();

                const html = doc.createElement('html');
                const head = doc.createElement('head');
                const title = doc.createElement('title');
                title.textContent = 'Barcode';
                head.appendChild(title);

                const body = doc.createElement('body');
                body.style.margin = '96px';

                const barcodeImg = doc.createElement('img');
                barcodeImg.src = img.src;
                barcodeImg.alt = 'barcode generated';
                body.appendChild(barcodeImg);

                html.appendChild(head);
                html.appendChild(body);
                doc.appendChild(html);

                doc.close();
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
            this.form.components = [];
            this.componentRows = [];
            await this.generateBarcode();
            return;
        }

        this.componentRows = this.mapAttachedComponentsToRows(this.attachedComponentsList);
        this.syncComponentsPayload();
        this.renderBarcode();
    },
}
</script>

<template>
    <form v-if="!!form" @submit.prevent="submitForm"  class="grid grid-cols-2 gap-4 w-full">
        <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg h-fit">
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
                    required
                    :error="form.errors.personnel_id"
                    @selectedChange="form.personnel_id = $event"
                >
                    <template #icon>
                        <filter-icon class="h-4 w-4" />
                    </template>
                </custom-dropdown>
                <custom-dropdown
                    required
                    :disabled="isUpdate"
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
        <div class="flex flex-col gap-2 shadow-xl sm:rounded-lg sm:p-2 lg:p-4 bg-white dark:bg-gray-700 h-fit">
            <div class="flex items-center justify-between">
                <h3 class="font-bold uppercase leading-none py-2 mb-1 border-b"><span v-if="componentRows.length">{{ componentRows.length }}</span> Attached Components / Items</h3>
                <button type="button" class="px-2 py-1 text-xs rounded border border-gray-600 text-gray-700" @click="addComponentRow">
                    Add Component
                </button>
            </div>
            <p>Attach components under the same transaction for tracking and retrieval. E.g. One (1) Desktop Computer Set includes monitor, keyboard, mouse, MS Office, etc. Each component can be individually tracked. </p>

            <div v-for="(component, index) in componentRows" :key="`component-${index}`" class="grid grid-cols-2 gap-2 border border-gray-200 rounded p-2 items-end">
                <select-search-field
                    :api-link="'api.inventory.items.options'"
                    label="Component Item"
                    v-model="component.item_id"
                    class="col-span-2"
                    @update:model-value="onComponentItemChange(index, $event)"
                />
                <text-input
                    type="number"
                    label="Quantity"
                    v-model="component.quantity"
                    @update:model-value="syncComponentsPayload()"
                />
                <text-input label="Unit" v-model="component.unit" @update:model-value="syncComponentsPayload()" />
                <text-input
                    type="text"
                    label="PRRI Component No."
                    placeholder="00001"
                    v-model="component.prri_component_no"
                    @update:model-value="syncComponentsPayload()"
                />
                <date-input
                    type="date"
                    label="Expiry Date"
                    v-model="component.expiration"
                    @update:model-value="syncComponentsPayload()"
                />
                <div class="col-span-2 flex justify-end">
                    <button type="button" class="px-2 py-1 text-xs rounded border border-red-500 text-red-600" @click="removeComponentRow(index)">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>
