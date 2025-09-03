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

export default {
    name: "Incoming",
    components: {
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
    <app-layout title="Incoming Transaction">
        <template v-slot:header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Incoming</h2>
        </template>

        <div class="py-4" > {{ form }}
            <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-5xl mx-auto">
                <div class="grid sm:grid-cols-3 grid-cols-1 gap-2 mx-auto w-full">
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
                            <Link :href="route('items.create')" class="h-fit w-full py-3 shadow flex items-center justify-center bg-white text-gray-600 rounded gap-1 text-sm px-2">
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
                    <text-input required type-input="number" label="Quantity" name="quantity" id="quantity" v-model="form.quantity" :error="form.errors.quantity" />
                    <text-input type-input="number" label="Unit Price" name="unit_price" id="unit_price" v-model="form.unit_price" :error="form.errors.unit_price" />
                    <text-input required label="Unit" name="unit" id="unit" v-model="form.unit" :error="form.errors.unit" />
                    <text-input type-input="number" disabled label="Total Cost" name="total_cost" id="total_cost" v-model="form.total_cost" :error="form.errors.total_cost" />
                    <text-input required label="Project Code" name="project_code" id="project_code" v-model="form.project_code" :error="form.errors.project_code" />
                    <date-input type-input="date" label="Expiration" name="expiration" id="expiration" v-model="form.expiration" :error="form.errors.expiration" />
                    <text-area label="Remarks" name="remarks" id="remarks" v-model="form.remarks" :error="form.errors.remarks" />
                </div>
                <div class="flex w-full justify-between mt-5">
                    <secondary-button @click="resetForm" class="w-1/4">Clear</secondary-button>
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Saving</span>
                        <span v-else>Save</span>
                    </submit-btn>
                </div>
            </form>
            <div v-if="svgText" class="flex sm:flex-row flex-col gap-1">
                <img id="barcode-image" :src="svgText" alt="SVG Image" />
                <button class="p-1 bg-gray-300 rounded" @click.prevent="print">
                    Print
                </button>
            </div>
        </div>
    </app-layout>
</template>

<style scoped>

</style>
