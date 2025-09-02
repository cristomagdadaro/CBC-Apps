<script>
import {Head, Link} from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import SpinnerIcon from "@/Components/Icons/SpinnerIcon.vue";
import NavLink from "@/Components/NavLink.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import QrcodeVue from 'qrcode.vue';
import { createCanvas } from "canvas";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

export default {
    name: "Incoming",
    components: {
        AppLayout,
        TextInput,
        AddIcon,
        NavLink,
        SpinnerIcon,
        FilterIcon,
        QrcodeVue,
        CustomDropdown, Link, LoaderIcon, PrimaryButton, SecondaryButton, Head},
    data() {
        return {
            api: null,
            noModelApi: null,
            form: {
                item_id: null,
                barcode: null,
                transac_type: 'in',
                quantity: null,
                unit_price: null,
                unit: null,
                total_cost: null,
                project_code: null,
                supplier_id: null,
                user_id: this.currentUser,
                expiration: null,
                remarks: null,
            },
            errors: {},
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
        cancel() {
            this.form = {
                item_id: null,
                barcode: null,
                transac_type: 'in',
                quantity: null,
                unit_price: null,
                unit: null,
                total_cost: null,
                project_code: null,
                supplier_id: null,
                user_id: this.currentUser,
                expiration: null,
                remarks: null,
            }
        },
        async submit() {
            const response = await this.api.post(this.form)
            if (response.status === 201) {
                this.$inertia.visit(route('transactions.index'));
            } else if (response.status === 422) {
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        },
        async generateBarcode(room) {
            if (!room) {
                return;
            }
            this.noModelApi.setBaseUrl(route('api.inventory.transactions.genbarcode', {
                room: room,
            }));

            await this.noModelApi.get().then(response => {
                this.form.barcode = response.data.barcode;
                this.renderBarcode();
            });
        },
        fullName(personnel) {
            const parts = [];

            if (personnel.fname) parts.push(personnel.fname);
            if (personnel.mname) parts.push(personnel.mname[0] + '.');
            if (personnel.lname) parts.push(personnel.lname);
            if (personnel.suffix) parts.push(personnel.suffix);

            return parts.join(' ');
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
        currentUser() {
            return this.$page.props.auth.user.id;
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
        this.api = new CoreApi(route('api.inventory.transactions.store'));
        this.noModelApi = new CoreApi(route('api.inventory.transactions.genbarcode',{
            room: '01',
        }));

        this.form.barcode = this.preGenerateBarcode;
        this.form.user_id = this.currentUser;

        await this.generateBarcode();
    },
}
</script>

<template>
    <Head title="Incoming" />

    <app-layout>
        <template v-slot:header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Incoming</h2>
        </template>
        <div class="py-4" v-if="noModelApi">
            <div class="flex flex-col gap-5 max-w-7xl mx-auto">
                <div v-if="api" class="flex flex-col gap-5 w-full max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
                    <form @submit.prevent="submit">
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
                                    :error="errors.item_id"
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
                                :error="errors.barcode"
                                @selectedChange="generateBarcode($event)"
                            >
                                <template #icon>
                                    <filter-icon class="h-4 w-4" />
                                </template>
                            </custom-dropdown>
                            <text-input required label="Quantity" name="quantity" id="quantity" v-model="form.quantity" :error="errors.quantity" />
                            <text-input type-input="number" label="Unit Price" name="unit_price" id="unit_price" v-model="form.unit_price" :error="errors.unit_price" />
                            <text-input required label="Unit" name="unit" id="unit" v-model="form.unit" :error="errors.unit" />
                            <text-input type-input="number" label="Total Cost" name="total_cost" id="total_cost" v-model="form.total_cost" :error="errors.total_cost" />
                            <text-input required label="Project Code" name="project_code" id="project_code" v-model="form.project_code" :error="errors.project_code" />
                            <text-input type-input="date" label="Expiration" name="expiration" id="expiration" v-model="form.expiration" :error="errors.expiration" />
                            <text-input type-input="longtext" label="Remarks" name="remarks" id="remarks" v-model="form.remarks" :error="errors.remarks" />
                            <div v-if="svgText" class="flex sm:flex-row flex-col gap-1">
                                <img id="barcode-image" :src="svgText" alt="SVG Image" />
                                <button class="p-1 bg-gray-300 rounded" @click.prevent="print">
                                    Print
                                </button>
                            </div>
                        </div>
                        <div class="flex w-full justify-between mt-5">
                            <secondary-button @click="cancel" class="w-1/4">Clear</secondary-button>
                            <primary-button type="submit" class="w-1/4 text-center">
                                <div v-if="api.processing">
                                    <loader-icon class="w-5 h-5" />
                                </div>
                                <span v-else>Save</span>
                            </primary-button>
                        </div>
                    </form>
                </div>
                <div v-else>
                    Can't initialize the form
                </div>
                <div class="flex flex-row gap-5">
                    right
                </div>
            </div>
            <canvas id="barcodeCanvas" width="256" height="256" />
        </div>
    </app-layout>
</template>

<style scoped>

</style>
