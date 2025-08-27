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
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";

export default {
    name: 'DataTable',
    components: {
        CancelBtn, ConfirmationModal, DeleteBtn,
        TransitionContainer,
        Checkbox,
        CheckallIcon,
        DeleteIcon,
        EditIcon,
        DtLinkButton,
        CaretDown,
        CustomDropdown, LoaderIcon, DtData, DtHead, DtRowBody, DtThead, DtRowHead, DtTbody, DtTheadRow, DtTable},
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
        }
    },
    methods: {
        getNestedValue(obj, path) {
            return path.split('.').reduce((acc, part) => acc && acc[part], obj);
        },
        confirmAction(data)
        {
            this.$emit('confirmDelete', data);
        },
        getShowPageRoute(row) {
            const showPage = this.dt?.data?.[0]?.showPage;
            const id = row.identifier?.()?.id;
            return showPage && id ? route(showPage, id) : '#';
        }
    }
}
</script>

<template>
    <div v-if="dt" class="relative w-full overflow-x-auto max-h-screen overflow-hidden overflow-y-auto">
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
                <dt-row-body
                    v-if="displayedRows && displayedRows.length"
                    v-for="row in displayedRows"
                    :key="row.id"
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
                                :class="head.align"
                                class="border border-gray-500 px-2"
                                :title="getNestedValue(row, head.key)"
                            >
                                {{ getNestedValue(row, head.key) }}
                            </dt-data>
                    </template>
                    <dt-data v-if="appendActions" class="border-y border-gray-500">
                        <div class="flex flex-row gap-2 justify-evenly">
                            <dt-link-button
                                v-if="dt?.data?.[0]?.showPage && row.identifier?.()?.id"
                                :href="getShowPageRoute(row)"
                                class="text-yellow-400"
                            >
                                <edit-icon class="w-4 h-auto" />
                            </dt-link-button>
                            <dt-link-button @click="confirmAction(row)" class="text-red-400">
                                <delete-icon class="w-4 h-auto" />
                            </dt-link-button>
                        </div>
                    </dt-data>
                </dt-row-body>
                <dt-row-body v-else>
                    <dt-data v-if="displayedHeaders" :colspan="displayedHeaders.length" class="text-center border-b border-gray-500">
                        No data available
                    </dt-data>
                </dt-row-body>
            </dt-tbody>
        </dt-table>
    </div>
</template>
