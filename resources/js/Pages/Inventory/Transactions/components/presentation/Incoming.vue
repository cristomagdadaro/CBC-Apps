<script>
import {Head, Link} from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import SpinnerIcon from "@/Components/Icons/SpinnerIcon.vue";
import NavLink from "@/Components/NavLink.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import QrcodeVue from 'qrcode.vue';
import { createCanvas } from "canvas";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import JsBarcode from "jsbarcode";
import DateInput from "@/Components/DateInput.vue";
import TextArea from "@/Components/TextArea.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";
import TransactionHeaderAction
    from "@/Pages/Inventory/Transactions/components/presentation/TransactionHeaderAction.vue";

export default {
    name: "Incoming",
    components: {
        TransactionHeaderAction,
        ResetBtn,
        TextArea,
        DateInput,
        SubmitBtn,
        AppLayout,
        TextInput,
        AddIcon,
        NavLink,
        SpinnerIcon,
        FilterIcon,
        QrcodeVue,
        CustomDropdown, Link, LoaderIcon, PrimaryButton, SecondaryButton, Head},
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            noModelApi: null,
            barcodeCanvas: null,
            svgText: '',
            select_storage: null,
            storage_locations: [
                {
                    name: '01',
                    label: 'Central Bodega',
                },{
                    name: '02',
                    label: 'Storage Room Chemicals',
                },{
                    name: '03',
                    label: 'Consumables in Box',
                },{
                    name: '04',
                    label: 'Consumables',
                },{
                    name: '05',
                    label: 'Chemicals in MGL',
                },{
                    name: '06',
                    label: 'Chemicals in GTL',
                },
            ]
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
    computed: {
        items() {
            return this.$page.props.items.map(item => {
                return {
                    name: item.id,
                    label: item.name + ' (' + item.brand + ')',
                }
            });
        },
        preGenerateBarcode() {
            return this.$page.props.barcode;
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
        }
    },
    watch: {
        'form.item_id': function (val) {
            this.form.unit_price = val ? val.unit_price : null;
            this.form.unit = val ? val.unit : null;
            this.form.total_cost = val ? val.unit_price * this.form.quantity : null;
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
        this.setFormAction('create');

        this.form.transac_type = 'incoming';
        this.form.barcode = this.preGenerateBarcode;
        this.form.user_id = this.$page.props.auth.user.id;

        await this.generateBarcode();
    },
}
</script>

<template>
    <app-layout title="Incoming Transaction Details">
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col gap-2 mx-auto w-full">
                    <div class="flex flex-col">
                        <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Incoming Transaction Form</h2>
                        <p>Please use this form to submit details of an incoming transaction.</p>
                    </div>
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
                    <custom-dropdown
                        required
                        :with-all-option="false"
                        :value="select_storage"
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
                        <text-input required type="number" label="Quantity" v-model="form.quantity" :error="form.errors.quantity" />
                        <text-input type="number" label="Unit Price" v-model="form.unit_price" :error="form.errors.unit_price" />
                        <text-input required label="Unit" v-model="form.unit" :error="form.errors.unit" />
                        <text-input type="number" label="Total Cost" v-model="form.total_cost" :error="form.errors.total_cost" />
                    </div>

                    <date-input type="date" label="Expiration" v-model="form.expiration" :error="form.errors.expiration" />
                    <text-area label="Remarks" v-model="form.remarks" :error="form.errors.remarks" />
                    <div v-if="svgText" class="flex sm:flex-row flex-col gap-1 w-full items-center relative">
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
                        <span v-if="model.api.processing">Saving</span>
                        <span v-else>Save</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </app-layout>
</template>

<style scoped>

</style>
