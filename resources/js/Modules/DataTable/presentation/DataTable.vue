<script>
import DtTable from "@/Modules/DataTable/presentation/components/DtTable.vue";
import DtTheadRow from "@/Modules/DataTable/presentation/components/DtThead.vue";
import DtThead from "@/Modules/DataTable/presentation/components/DtThead.vue";
import DtTbody from "@/Modules/DataTable/presentation/components/DtTbody.vue";
import DtRowHead from "@/Modules/DataTable/presentation/components/DtRowHead.vue";
import DtRowBody from "@/Modules/DataTable/presentation/components/DtRowBody.vue";
import DtHead from "@/Modules/DataTable/presentation/components/DtHead.vue";
import DtData from "@/Modules/DataTable/presentation/components/DtData.vue";
import DtLinkButton from "@/Modules/DataTable/presentation/components/DtLinkButton.vue";

export default {
    name: 'DataTable',
    components: {
        DtLinkButton,
        DtData,
        DtHead,
        DtRowBody,
        DtThead,
        DtRowHead,
        DtTbody,
        DtTheadRow,
        DtTable,
    },
    props: {
        apiResponse: {
            type: Object,
        },
        processing: {
            type: Boolean,
            default: false,
        },
        appendActions: {
            type: Boolean,
            default: true
        },
        model: {
            type: Function,
        },
        enableExport: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        dt() {
            return this.apiResponse;
        },
        displayedHeaders() {
            if (this.model)
                return this.model.getColumns();
        },
        displayedRows() {
            if (this.dt && this.dt?.data?.length)
                return this.dt.data;
        }
    },
    data() {
        return {
            perPage: [
                {label: '10', name: '10', selected: true},
                {label: '25', name: '25', selected: false},
                {label: '50', name: '50', selected: false},
                {label: '100', name: '100', selected: false},
            ],
            showConfirmModal: false,
            rowPendingDelete: null,
        };
    },
    methods: {
        getNestedValue(obj, path) {
            return path.split('.').reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : null), obj);
        },
        confirmAction(data) {
            this.$emit('confirmDelete', data);
        },
        openDeleteConfirm(row) {
            this.rowPendingDelete = row;
            this.showConfirmModal = true;
        },
        performDelete() {
            const row = this.rowPendingDelete;
            this.showConfirmModal = false;
            if (!row) return;

            // Emit delete event to parent (SearchComp) to handle delete and refresh
            this.$emit('delete-record', row);
            this.rowPendingDelete = null;
        },
        exportToCSV() {
            if (!this.displayedRows || !this.displayedHeaders) return;

            const headers = this.displayedHeaders.filter(h => h.visible).map(h => h.title);
            const rows = this.displayedRows.map(row =>
                this.displayedHeaders.filter(h => h.visible).map(h => this.getNestedValue(row, h.key) || '')
            );

            const csvContent = [headers, ...rows].map(row =>
                row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
            ).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'datatable-export.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        },
        getShowPageRoute(row) {
            const showPage = this.dt?.data?.[0]?.showPage;
            const id = row?.identifier?.()?.id ?? row.id;
            return showPage && id ? route(showPage, id) : '#';
        },
    }
}
</script>

<template>
    <div v-if="dt" class="relative w-full overflow-x-auto max-h-screen overflow-hidden overflow-y-auto">
        <!-- Confirmation Modal -->
        <delete-confirmation-modal
            :show="showConfirmModal"
            :is-processing="false"
            title="Confirm Delete"
            message="Are you sure you want to delete this record? This action cannot be undone."
            :item-name="rowPendingDelete?.fullName || rowPendingDelete?.name || ''"
            @confirm="performDelete"
            @close="showConfirmModal = false"
        />

        <!-- Export Controls -->
        <div class="mb-4 flex items-center gap-4">
            <button
                v-if="enableExport"
                @click="exportToCSV"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors"
            >
                Export to CSV
            </button>
        </div>

        <transition-container type="fade">
            <div v-show="processing" class="absolute w-full h-full">
                <div class="flex items-center gap-1 justify-center py-5 bg-gray-200 h-full bg-opacity-90">
                    <loader-icon />
                    <span>Fetching data...</span>
                </div>
            </div>
        </transition-container>

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
                    >
                            {{ header.title }}
                    </dt-head>
                    <dt-head v-if="appendActions" class="whitespace-nowrap">
                        Action
                    </dt-head>
                </dt-row-head>
            </dt-thead>
            <dt-tbody>
                <template v-if="displayedRows && displayedRows.length">
                    <dt-row-body
                        v-for="row in displayedRows"
                        :key="row?.identifier?.()?.id ?? row?.id ?? displayedRows.indexOf(row)"
                    >
                        <dt-data class="text-center border-y border-gray-500 lg:p-0 p-1">
                            {{ (dt.from ? dt.from : 1) + displayedRows.indexOf(row) }}
                        </dt-data>
                        <template
                            v-for="head in displayedHeaders"
                            :key="head.key"
                        >
                                <dt-data
                                    v-if="head.visible"
                                    :class="getNestedValue(row, head.align)"
                                    class="border border-gray-500 px-2"
                                    :title="getNestedValue(row, head.key)"
                                >
                                    {{ getNestedValue(row, head.key) }}
                                </dt-data>
                        </template>
                        <dt-data v-if="appendActions" class="border-y border-gray-500">
                            <div class="flex flex-row gap-2 justify-evenly">
                                <dt-link-button
                                    v-if="dt?.data?.[0]?.showPage && row?.identifier?.()?.id"
                                    :href="getShowPageRoute(row)"
                                    class="text-yellow-400"
                                >
                                    <edit-icon class="w-4 h-auto" />
                                </dt-link-button>
                                <dt-link-button @click="openDeleteConfirm(row)" class="text-red-400">
                                    <delete-icon class="w-4 h-auto" />
                                </dt-link-button>
                            </div>
                        </dt-data>
                    </dt-row-body>
                </template>
                <dt-row-body v-else>
                    <dt-data v-if="displayedHeaders" :colspan="displayedHeaders.length" class="text-center border-b border-gray-500">
                        No data available
                    </dt-data>
                </dt-row-body>
            </dt-tbody>
        </dt-table>
    </div>
</template>
