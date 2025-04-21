<script>
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { QrcodeStream } from 'vue-qrcode-reader'
import SearchBox from "@/Pages/Inventory/Scan/components/searchBox.vue";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import Scanner from "@/Pages/Inventory/Scan/components/Scanner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ScannerHeaderActions from "@/Pages/Inventory/Scan/components/ScannerHeaderActions.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import TextInput from "@/Components/TextInput.vue";
import SearchBy from "@/Components/SearchBy.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import PaginateBtn from "@/Components/PaginateBtn.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import ListOfTransactions from "@/Pages/Inventory/Scan/components/ListOfTransactions.vue";

export default {
    name: "Scan",
    components: {
        ListOfTransactions,
        ListOfForms,
        ArrowRight, ArrowLeft, PaginateBtn,
        SearchBtn,
        SearchBy, TextInput,
        ScannerHeaderActions,
        DropdownLink, Dropdown,
        Scanner,
        FilterIcon,
        CustomDropdown,
        QrcodeStream,
        SearchBox,
        Head,
        AppLayout
    },
    computed: {
        Transaction() {
            return Transaction;
        },
    },
    mixins: [ApiMixin],
    data() {
        return {
            selectedScanner: "webcam",
            selectedDevice: null,
            devices: [],
            error: null,
            transactionsFromApi: [],
            params: {
                search: null,
                filter: null,
                is_exact: null,
                sort: 'item_id',
                paginate: true,
            },
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('get');
    },
    async mounted() {
        await this.searchEvent();
    },
    unmounted() {
        this.stopHandheldScanner();
    },
    methods: {
        startHandheldScanner() {
            window.addEventListener('keydown', this.handleScannerInput);
        },
        stopHandheldScanner() {
            window.removeEventListener('keydown', this.handleScannerInput);
        },
        handleScannerInput(event) {
            if (event.key === 'Enter') {
                this.decode = event.target.value;
            }
        },
        cameraChange(deviceInfo) {
            this.selectedDevice = this.devices.find(option => option.deviceId === deviceInfo.deviceId);
        },
        async searchEvent() {
            this.transactionsFromApi = await this.fetchData();

            this.form.search = null;
        }
    },
    watch: {
        selectedScanner(value) {
            if (value === 'webcam') {
                this.selectedDevice = this.devices[0];
                this.stopHandheldScanner();
            } else {
                this.selectedDevice = null;
                this.startHandheldScanner();
            }
        },
    },
}
</script>

<template>
    <AppLayout title="Scan">
        <template #header>
            <scanner-header-actions />
        </template>

        <div class="sm:py-4 p-0">
            <div class="flex sm:flex-row flex-col gap-5 max-w-7xl mx-auto border border-gray-400 sm:p-5 p-1 sm:rounded-md">
                <div class="flex flex-col gap-2 sm:border-r border-gray-400 sm:pr-5">
                    <div>
                        <label class="text-gray-600 text-center text-sm whitespace-nowrap">Select Scanning Device</label>
                        <Dropdown align="right" width="60">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center whitespace-nowrap justify-between px-3 py-3 border shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                    {{ selectedScanner === 'webcam' ? 'Camera':'Handheld Scanner' }}
                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="w-60">
                                    <DropdownLink as="button" @click.prevent="selectedScanner = 'webcam'">
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera text-blue-300" viewBox="0 0 16 16">
                                                <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                                                <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                            </svg>
                                            <span>Camera</span>
                                        </div>
                                    </DropdownLink>
                                    <DropdownLink as="button" @click.prevent="selectedScanner = 'handheld'">
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-disc text-red-300" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                                <path d="M10 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0M8 4a4 4 0 0 0-4 4 .5.5 0 0 1-1 0 5 5 0 0 1 5-5 .5.5 0 0 1 0 1m4.5 3.5a.5.5 0 0 1 .5.5 5 5 0 0 1-5 5 .5.5 0 0 1 0-1 4 4 0 0 0 4-4 .5.5 0 0 1 .5-.5"/>
                                            </svg>
                                            <span>Handheld Scanner</span>
                                        </div>
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <div v-if="selectedScanner === 'webcam'"  class="sm:max-w-96 max-w-full">
                        <div>
                            <label class="text-gray-600 text-center text-sm whitespace-nowrap">Available Camera Devices</label>
                            <Dropdown align="right" width="60">
                                <template #trigger>
                                    <button type="button" class="inline-flex items-center justify-between px-3 py-3 border shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ selectedDevice ? selectedDevice.label:'Choose a camera' }}
                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="w-60">
                                        <DropdownLink v-for="device in devices" as="button" @click.prevent="cameraChange(device)">
                                            <div class="flex items-center gap-1">
                                                <span>{{ device.label }}</span>
                                            </div>
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>


                        <div v-if="selectedDevice" class="flex justify-between select-none mt-2">
                            <label class="text-gray-600 text-center text-sm">Scanner View</label>
                            <span class="text-gray-600 text-center text-sm">QR/Barcode</span>
                        </div>
                        <div class="p-1 drop-shadow-lg bg-white my-2">
                            <scanner @decoded="form.search = $event"
                                     @error="error = $event"
                                     :selectedDevice="selectedDevice"
                                     @detectedDevices="devices =  $event"
                            />
                        </div>
                    </div>
                    <div v-else class="items-center">
                        <img class="w-28 mx-auto animate-pulse" src="/misc/img/handheld-scanner.png" alt="Handheld Scanner Icon">
                    </div>{{ error }}
                </div>
                <div class="flex flex-col gap-2 w-full overflow-x-auto">
                    <form v-if="!!form" @submit.prevent="searchEvent" class="flex flex-col gap-2 w-full">
                        <div class="flex gap-2 items-end">
                            <search-by :value="form.filter" :is-exact="form.is_exact" :options="Transaction.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                            <text-input placeholder="Search..." v-model="form.search" />
                            <search-btn type="submit" :disabled="model?.processing" class="w-[10rem] text-center">
                                <span v-if="!model?.processing">Search</span>
                                <span v-else>Searching</span>
                            </search-btn>
                        </div>
                        <div v-if="transactionsFromApi" class="flex w-full gap-2 items-center">
                            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                                <!-- First Button -->
                                <paginate-btn @click="form.page = 1" :disabled="form.page === 1">
                                    First
                                </paginate-btn>

                                <!-- Previous Button -->
                                <paginate-btn @click="form.page = Math.max(1, form.page - 1)" :disabled="form.page === 1">
                                    <template v-slot:icon>
                                        <arrow-left class="h-auto w-6" />
                                    </template>
                                    Prev
                                </paginate-btn>

                                <!-- Current Page Indicator -->
                                <div class="text-xs flex flex-col whitespace-nowrap text-center">
                                    <span class="font-medium mx-1" title="current page and total pages">
                                        <span>{{ transactionsFromApi?.current_page }}</span> / <span>{{ transactionsFromApi?.last_page }}</span>
                                    </span>
                                </div>

                                <!-- Next Button -->
                                <paginate-btn
                                    @click="form.page = Math.min(transactionsFromApi?.last_page, form.page + 1)"
                                    :disabled="form.page === transactionsFromApi?.last_page"
                                >
                                    Next
                                    <template v-slot:icon>
                                        <arrow-right class="h-auto w-6" />
                                    </template>
                                </paginate-btn>

                                <!-- Last Button -->
                                <paginate-btn
                                    @click="form.page = transactionsFromApi?.last_page"
                                    :disabled="form.page === transactionsFromApi?.last_page"
                                >
                                    Last
                                </paginate-btn>
                            </div>
                        </div>
                    </form>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <!-- Show forms when available -->
                        <list-of-transactions
                            v-if="transactionsFromApi && transactionsFromApi.total > 0"
                            :forms-data="transactionsFromApi.data"
                            @removeModel="transactionsFromApi.data = transactionsFromApi.data.filter(form => form.id !== $event.id)"
                        />

                        <!-- Show "Searching" when processing -->
                        <div v-else-if="model.processing" class="text-center py-3 border border-AB rounded-lg">
                            Searching...
                        </div>

                        <!-- Show "Form does not exist" when search was performed but no results -->
                        <div v-else-if="transactionsFromApi && transactionsFromApi.total === 0 && form.search" class="text-center py-3 border border-AB rounded-lg">
                            Not Found. Try using some filters.
                        </div>

                        <!-- Show "No forms available" when nothing was returned and no search was performed -->
                        <div v-else class="text-center py-3 border border-AB rounded-lg">
                            No data available.
                        </div>
                    </div>
                    <div v-if="transactionsFromApi && transactionsFromApi.data?.length" class="flex w-full gap-2 items-center mt-3">
                        <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                            <!-- First Button -->
                            <paginate-btn @click="form.page = 1" :disabled="form.page === 1">
                                First
                            </paginate-btn>

                            <!-- Previous Button -->
                            <paginate-btn @click="form.page = Math.max(1, form.page - 1)" :disabled="form.page === 1">
                                <template v-slot:icon>
                                    <arrow-left class="h-auto w-6" />
                                </template>
                                Prev
                            </paginate-btn>

                            <!-- Current Page Indicator -->
                            <div class="text-xs flex flex-col whitespace-nowrap text-center">
                                    <span class="font-medium mx-1" title="current page and total pages">
                                        <span>{{ transactionsFromApi?.current_page }}</span> / <span>{{ transactionsFromApi?.last_page }}</span>
                                    </span>
                            </div>

                            <!-- Next Button -->
                            <paginate-btn
                                @click="form.page = Math.min(transactionsFromApi?.last_page, form.page + 1)"
                                :disabled="form.page === transactionsFromApi?.last_page"
                            >
                                Next
                                <template v-slot:icon>
                                    <arrow-right class="h-auto w-6" />
                                </template>
                            </paginate-btn>

                            <!-- Last Button -->
                            <paginate-btn
                                @click="form.page = transactionsFromApi?.last_page"
                                :disabled="form.page === transactionsFromApi?.last_page"
                            >
                                Last
                            </paginate-btn>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
