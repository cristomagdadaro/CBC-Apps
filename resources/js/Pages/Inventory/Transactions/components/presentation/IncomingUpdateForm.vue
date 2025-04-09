<script>
import {Head, Link} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import {createCanvas} from "canvas";
import JsBarcode from "jsbarcode";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import TextField from "@/Components/TextField.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";

export default {
    name: "IngoingUpdateForm",
    components: {
        LoaderIcon,
        PrimaryButton,
        SecondaryButton,Link, TextField, AddIcon, FilterIcon, CustomDropdown, AuthenticatedLayout, Head},
    data() {
        return {
            api: null,
            noModelApi: null,
            form: {},
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
    computed: {
        items() {
            return this.$page.props.items.map(item => {
                return {
                    name: item.id,
                    label: item.name + ' (' + item.brand + ')',
                }
            });
        },
        show() {
            return this.$page.props.show;
        }
    },
    methods: {
        cancel() {
            if(this.show){
                this.form = Object.assign({}, this.show);
                this.select_storage = this.form.barcode.substring(4, 6);
            }
        },
        async submit() {
            const response = await this.api.put(this.form);
            if (response instanceof BaseResponse && response.status === 200) {
                this.$inertia.visit(route('transactions.index'));
            } else if (response instanceof ErrorResponse){
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
        this.api = new CoreApi(route('api.inventory.transactions.update', this.show.id));
        this.noModelApi = new CoreApi(route('api.inventory.transactions.genbarcode',{
            room: '01',
        }));
        this.form = Object.assign({}, this.show);
        if (this.form.barcode)
        this.select_storage = this.form.barcode.substring(4, 6);
    }
}
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold sm:text-xl text-sm text-gray-800 leading-tight uppercase">Incoming Transaction Update</h2>
        </template>
        <div class="py-4" v-if="noModelApi">
            <div class="flex flex-col gap-5 max-w-7xl mx-auto">
                <div v-if="api && form" class="flex flex-col gap-5 w-full max-w-7xl mx-auto bg-gray-200 p-5 rounded shadow">
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
                            <text-field type-input="number" required label="Quantity" name="quantity" id="quantity" v-model="form.quantity" :error="errors.quantity" />
                            <text-field type-input="number" label="Unit Price" name="unit_price" id="unit_price" v-model="form.unit_price" :error="errors.unit_price" />
                            <text-field required label="Unit" name="unit" id="unit" v-model="form.unit" :error="errors.unit" />
                            <text-field type-input="number" label="Total Cost" name="total_cost" id="total_cost" v-model="form.total_cost" :error="errors.total_cost" />
                            <text-field required label="Project Code" name="project_code" id="project_code" v-model="form.project_code" :error="errors.project_code" />
                            <text-field type-input="date" label="Expiration" name="expiration" id="expiration" v-model="form.expiration" :error="errors.expiration" />
                            <text-field type-input="longtext" label="Remarks" name="remarks" id="remarks" v-model="form.remarks" :error="errors.remarks" />
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
                    <div v-if="svgText" class="flex sm:flex-row flex-col gap-1">
                        <img id="barcode-image" :src="svgText" alt="SVG Image" />
                        <button class="p-1 bg-gray-300 rounded" @click.prevent="print">
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>

</style>
