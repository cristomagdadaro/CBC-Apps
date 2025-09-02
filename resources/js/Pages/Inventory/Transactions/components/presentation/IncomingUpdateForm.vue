<script>
import {Head, Link} from "@inertiajs/vue3";
import {createCanvas} from "canvas";
import JsBarcode from "jsbarcode";
import QrcodeVue from 'qrcode.vue';
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/presentation/PersonnelHeaderActions.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import TextArea from "@/Components/TextArea.vue";

export default {
    name: "IngoingUpdateForm",
    components: {
        TextArea,
        ResetBtn, AppLayout, TextInput, SubmitBtn, PersonnelHeaderActions,
        LoaderIcon,
        PrimaryButton,
        SecondaryButton,Link, AddIcon, FilterIcon, CustomDropdown, Head},
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
        /*this.noModelApi = new CoreApi(route('api.inventory.transactions.genbarcode',{
            room: '01',
        }));*/
        this.form = Object.assign({}, this.show);
        if (this.form.barcode)
        this.select_storage = this.form.barcode.substring(4, 6);
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('update');
    },
}
</script>

<template>
    <AppLayout title="Update Transaction Details">
        <template #header>
            <h2 class="font-semibold sm:text-xl text-sm text-gray-800 leading-tight uppercase">Incoming Transaction Update</h2>
        </template>
        <form v-if="!!form" @submit.prevent="submitUpdate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
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
                    <div class="grid grid-cols-2 gap-2">
                        <text-input type="number" label="Quantity" v-model="form.quantity" :error="form.errors.quantity" />
                        <text-input type="number" label="Unit Price" v-model="form.unit_price" :error="form.errors.unit_price" />
                        <text-input label="Unit" v-model="form.unit" :error="form.errors.unit" />
                        <text-input type="number" label="Total Cost" v-model="form.total_cost" :error="form.errors.total_cost" />
                    </div>

                    <text-input label="Project Code" v-model="form.project_code" :error="form.errors.project_code" />
                    <text-input type="date" label="Expiration" v-model="form.expiration" :error="form.errors.expiration" />
                    <text-area label="Remarks" v-model="form.remarks" :error="form.errors.remarks" />
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
                <div class="flex flex-col w-full text-xs text-gray-400 border-t border-gray-500 pt-1">
                        <span>
                            Date Created: {{  $page.props.data.created_at }}
                        </span>
                    <span>
                            Last Updated: {{  $page.props.data.updated_at }}
                        </span>
                </div>
            </div>
        </form>
        <div class="flex flex-row gap-5">
            <div v-if="svgText" class="flex sm:flex-row flex-col gap-1">
                <img id="barcode-image" :src="svgText" alt="SVG Image" />
                <button class="p-1 bg-gray-300 rounded" @click.prevent="print">
                    Print
                </button>
            </div>
        </div>
    </AppLayout>

</template>

<style scoped>

</style>
