<script>
import DtTable from "@/Components/DataTable/presentation/components/DtTable.vue";
import DtTheadRow from "@/Components/DataTable/presentation/components/DtThead.vue";
import DtThead from "@/Components/DataTable/presentation/components/DtThead.vue";
import DtTbody from "@/Components/DataTable/presentation/components/DtTbody.vue";
import DtRowHead from "@/Components/DataTable/presentation/components/DtRowHead.vue";
import DtRowBody from "@/Components/DataTable/presentation/components/DtRowBody.vue";
import DtHead from "@/Components/DataTable/presentation/components/DtHead.vue";
import DtData from "@/Components/DataTable/presentation/components/DtData.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import CaretDown from "@/Components/Icons/CaretDown.vue";
import DtLinkButton from "@/Components/DataTable/presentation/components/DtLinkButton.vue";
import EditIcon from "@/Components/Icons/EditIcon.vue";
import DeleteIcon from "@/Components/Icons/DeleteIcon.vue";
import CheckallIcon from "@/Components/Icons/CheckallIcon.vue";
import Checkbox from "@/Components/Checkbox.vue";

export default {
    name: 'DataTable',
    components: {
        Checkbox,
        CheckallIcon,
        DeleteIcon,
        EditIcon,
        DtLinkButton,
        CaretDown,
        CustomDropdown, LoaderIcon, DtData, DtHead, DtRowBody, DtThead, DtRowHead, DtTbody, DtTheadRow, DtTable},
    props: {
        title: {
            type: String,
            default: 'DataTable'
        },
        appendActions: {
            type: Boolean,
            default: false
        },
        baseWebUrl: {
            type: String,
            required: false,
        },
        baseApiUrl: {
            type: [String, null],
            required: false,
        },
        baseModel: {
            type: [Function, BaseModel],
            required: false,
        },
        editLink: {
            type: String,
            required: false,
        },
        deleteLink: {
            type: String,
            required: false,
        },
        params: {
            type: Object,
            required: false,
        },
        selectableRows: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        BaseResponse() {
            return BaseResponse
        },
        selected(){
            return this.dt.selected;
        },
        current_page() {
            return this.dt.response['meta']['current_page'];
        },
        last_page() {
            return this.dt.response['meta']['last_page'];
        },
        next_page() {
            return this.dt.response['meta']['current_page'] + 1;
        },
        prev_page() {
            return this.dt.response['meta']['current_page'] - 1;
        },
        first_page() {
            return 1;
        },
        total_pages() {
            return this.dt.response['meta']['last_page'];
        },
        total_entries() {
            return this.dt.response['meta']['total'];
        },
        meta_from() {
            return this.dt.response['meta']['from'];
        },
        meta_to() {
            return this.dt.response['meta']['to'];
        },
        showIconText() {
            return this.$store.state.showTextWithIcon;
        },
        displayedHeaders() {
            if (this.appendActions) {
                return [
                    ...this.dt.model.getColumns(),
                    {
                        title: 'Actions',
                        key: null,
                        align: 'left',
                        sortable: true,
                        visible: true,
                    }
                ]
            } else {
                return this.dt.model.getColumns();
            }
        },
        data() {
            return this.dt.response['data'];
        },
    },
    data() {
        return {
            dt: null,
            perPage: [
                {label: '10', name: '10', selected: true},
                {label: '25', name: '25', selected: false},
                {label: '50', name: '50', selected: false},
                {label: '100', name: '100', selected: false},
            ],
            showDeleteModal: false,
        }
    },
    methods: {
        showData(colName){
            return Array.from(this.dt.model.getColumns()).find(col => col.key === colName).visible && true;
        },
        async deleteItem(id) {
            await this.dt.delete(id);
            this.$emit('selectedDataRowChanged', null);
        },
        showDeleteModalFunc(id) {
            this.dt.selected = id;
            this.showDeleteModal = true;
        },
        selectedDataRowChange(event, data) {
            if (!event.ctrlKey){
                this.dt.deselectAll();
                this.dt.addSelected(data.id);
            }
            else
                this.dt.addSelected(data.id);
            this.$emit('selectedDataRowChanged', data);
        },
        selectOneRow(data) {
            this.dt.deselectAll();
            this.dt.addSelected(data.id);
        },
        displayDataBasedOnHeadersOrder(data) {
            return this.displayedHeaders.reduce((result, header) => {
                if (header.visible && data.hasOwnProperty(header.key)) {
                    result[header.key] = data[header.key];
                }
                return result;
            }, {});
        },
        sortColumn(key) {
            if (key)
                this.dt.sortFunc({sort: key});
        }
    },
    async mounted() {
        if (this.baseApiUrl){
            this.dt = new DataTableApi(this.baseApiUrl, this.baseModel, this.params);
            await this.dt.init();
        }
    },
    watch: {
        async baseUrl() {
            if (this.baseApiUrl){
                this.dt = new DataTableApi(this.baseApiUrl, this.baseModel);
                await this.dt.init();
            }
        },
        params: {
           async handler(newValue) {
               if (this.baseApiUrl){
                   Object.assign(this.dt.request.params, newValue);
               }
                await this.dt.refresh();
           },
          deep: true
        }
    },
    setup() {
        return {
            DataTableApi,
        }
    },
    beforeUnmount() {
        this.dt = null;
    },
}
</script>

<template>
    <div v-if="dt" class="w-full overflow-x-auto max-h-screen overflow-hidden overflow-y-auto">
        <div class="w-full bg-gray-200 flex flex-row items-center justify-between p-2 gap-2 select-none" v-if="!!dt.response['meta']">
            <div class="text-gray-500 text-sm whitespace-nowrap">
                Showing {{ meta_from }} - {{ meta_to }} of {{ total_entries }}
            </div>
            <div class="flex gap-2">
                <custom-dropdown
                    :value="dt.request.params.per_page"
                    :withAllOption="false"
                    :options="perPage"
                    @selectedChange="dt.perPageFunc({ per_page: $event })"
                >
                    <template #icon>
                        <caret-down  class="h-4 w-4" />
                    </template>
                </custom-dropdown>
                <button
                    class="bg-gray-200 text-sm text-gray-700 px-2 py-0.5 rounded shadow duration-100 hover:bg-gray-400 hover:text-gray-100"
                    :disabled="current_page === first_page"
                    @click="dt.firstPage()"
                >
                    First
                </button>
                <button
                    class="bg-gray-200 text-sm text-gray-700 px-2 py-0.5 rounded shadow duration-100 hover:bg-gray-400 hover:text-gray-100"
                    :disabled="current_page === first_page"
                    @click="dt.prevPage()"
                >
                    Prev
                </button>
                <div class="text-gray-500 text-sm flex items-center whitespace-nowrap">
                    <input class="border-none p-0 m-0 max-w-fit-content bg-transparent text-gray-500 text-sm w-10 text-center rounded shadow mx-0.5"
                           type="text"
                           name="current_page"
                           @keydown.enter="dt.goToPage($event.target.value <= last_page ? $event.target.value : last_page)"
                           :value="current_page"
                           @focusout="$event.target.value = current_page"
                           id="current_page"> / {{ last_page }}
                </div>
                <button
                    class="bg-gray-200 text-sm text-gray-700 px-2 py-0.5 rounded shadow duration-100 hover:bg-gray-400 hover:text-gray-100"
                    :disabled="current_page === last_page"
                    @click="dt.nextPage()"
                >
                    Next
                </button>
                <button
                    class="bg-gray-200 text-sm text-gray-700 px-2 py-0.5 rounded shadow duration-100 hover:bg-gray-400 hover:text-gray-100"
                    :disabled="current_page === last_page"
                    @click="dt.lastPage()"
                >
                    Last
                </button>
            </div>
        </div>
        <dt-table>
            <dt-thead>
                <dt-row-head class="bg-gray-500 text-white uppercase">
                    <dt-head class="whitespace-nowrap">
                        #
                    </dt-head>
                    <dt-head
                        class="whitespace-nowrap hover:bg-gray-600"
                        v-for="header in displayedHeaders"
                        :key="header.id || header.key"
                        v-show="header.visible"
                        :is-filtered="header.key === dt.request.params.filter && !!header.key"
                        @click="sortColumn(header.key)"
                    >
                            {{ header.title }}
                    </dt-head>
                </dt-row-head>
            </dt-thead>
            <dt-tbody>
                <dt-row-body
                    v-if="data && data.length && !dt.processing"
                    v-for="row in data"
                    :key="row.id"
                    :selected="selectableRows? dt.isSelected(row.id): false"
                    @click="selectableRows?selectedDataRowChange($event, row):null"
                >
                    <dt-data>
                        <div class="flex gap-1">
                            {{ meta_from + data.indexOf(row) }}
                        </div>
                    </dt-data>
                    <template
                        v-for="(value, key) in displayDataBasedOnHeadersOrder(row)"
                        :key="value"
                    >
                            <dt-data
                                v-if="showData(key)"
                            >
                                {{ value }}
                            </dt-data>
                    </template>
                    <dt-data v-if="appendActions">
                        <div class="flex flex-row gap-2 justify-evenly">
                            <dt-link-button :href="baseWebUrl + '/' + row.id" class="text-yellow-400">
                                <edit-icon class="w-4 h-auto" />
                            </dt-link-button>
                            <dt-link-button @click="showDeleteModalFunc(row.id)" class="text-red-400">
                                <delete-icon class="w-4 h-auto" />
                            </dt-link-button>
                        </div>
                    </dt-data>
                </dt-row-body>
                <dt-row-body v-else-if="dt.processing">
                    <dt-data :colspan="displayedHeaders.length">
                        <div class="flex items-center gap-1 justify-center py-5 bg-gray-200">
                            <loader-icon />
                            <span>Fetching data...</span>
                        </div>
                    </dt-data>
                </dt-row-body>
                <dt-row-body v-else>
                    <dt-data :colspan="displayedHeaders.length">
                        No data available
                    </dt-data>
                </dt-row-body>
            </dt-tbody>
        </dt-table>
        <div v-if="showDeleteModal" @click="showDeleteModal = false" class="absolute overflow-hidden w-full top-0 left-0 bg-gray-500 bg-opacity-50 z-[99]">
            <div class="min-h-screen z-[999] overflow-hidden">
                <div class="bg-white w-1/3 mx-auto mt-20 p-5 rounded shadow">
                    <div class="flex flex-col gap-2">
                        <h2 class="text-lg text-gray-800">Are you sure you want to delete this item?</h2>
                        <div class="flex justify-between">
                            <button @click="showDeleteModal = false" class="bg-gray-200 text-gray-700 px-2 py-1 rounded shadow duration-100 hover:bg-gray-400 hover:text-gray-100">Cancel</button>
                            <button @click="deleteItem(selected)" class="bg-red-500 text-white px-2 py-1 rounded shadow duration-100 hover:bg-red-700 hover:text-gray-100">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
