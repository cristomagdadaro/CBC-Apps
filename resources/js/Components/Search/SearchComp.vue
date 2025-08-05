<script>
import PaginateBtn from "@/Components/PaginateBtn.vue";
import InputError from "@/Components/InputError.vue";
import SearchBy from "@/Components/SearchBy.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SearchBox from "@/Pages/Inventory/Scan/components/searchBox.vue";
import {defineAsyncComponent} from "vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import DtoResponse from "@/Modules/dto/DtoResponse.js";

export default {
    name: "SearchComp",
    components: {
        CancelBtn, ConfirmationModal, DeleteBtn,
        CustomDropdown,
        FilterIcon,
        SearchBox, ArrowRight, TextInput, SearchBtn, ArrowLeft, SearchBy, InputError, PaginateBtn},
    mixins: [ApiMixin],
    props: {
        propModel: {
            type: Function,
            required: true,
        },
        action: {
            type: String,
            default: "get",
        },
        cardSlot: {
            type: Object,
            required: false,
            default: defineAsyncComponent({
                loader: () => import("@/Components/DefaultBlankForm.vue"),
            }),
        },
    },
    data() {
        return {
            apiResponse: null,
        }
    },
    beforeMount() {
        this.model = new this.propModel();
        this.setFormAction(this.action);
    },
    computed: {
        columns() {
            return this.propModel.getColumns()
                .map(column => column && column.visible ? { name: column.key, label: column.title } : null)
                .filter(Boolean);
        },
        perPageOptions() {
            return [
                {name:10, label:'10'},
                {name:25, label:'25'},
                {name:50, label:'50'},
                {name:100, label:'100'},
            ]
        }
    },
    methods: {
        async searchEvent() {
            this.apiResponse = await this.fetchData();
            this.$emit("searchedData", this.apiResponse);
        },
        async handleDelete()
        {
            const response = await this.submitDelete();
            if (response instanceof DtoResponse)
            {
                this.confirmDelete = false;
                this.$emit("deletedModel", response.data);
            }
        }
    }
}
</script>

<template>
    <form v-if="!!form" class="flex gap-2 items-end select-none"  @submit.prevent="searchEvent">
        <div class="flex flex-col w-full gap-2">
            <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                <div class="flex gap-3 w-full">
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between select-none">
                            <label class="text-gray-600 text-xs">Per Page</label>
                        </div>
                        <custom-dropdown :show-clear="false"
                                         :value="form.per_page"
                                         :options="perPageOptions"
                                         :withAllOption="false"
                                         @selectedChange="form.per_page=$event; searchEvent();"
                        >
                            <template #icon>
                                <filter-icon class="h-4 w-4" />
                            </template>
                        </custom-dropdown>
                    </div>
                    <search-by
                        :value="form.filter"
                        :is-exact="form.is_exact"
                        :options="columns"
                        @searchBy="form.filter = $event"
                        @isExact="form.is_exact = $event"
                    />
                    <search-box class="w-full" v-model="form.search" />
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between select-none">
                            <label class="text-gray-600 text-xs">&nbsp;</label>
                        </div>
                        <search-btn type="submit" :disabled="model?.processing" class="w-[10rem] text-center h-full hover:bg-AB duration-150">
                            <span v-if="!model?.processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                </div>
            </div>
            <div v-if="apiResponse" class="flex w-full gap-2 items-center">
                <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                    <!-- First Button -->
                    <paginate-btn @click="form.page = 1; searchEvent()" :disabled="form.page === 1">
                        First
                    </paginate-btn>

                    <!-- Previous Button -->
                    <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent()" :disabled="form.page === 1">
                        <template v-slot:icon>
                            <arrow-left class="h-auto w-6" />
                        </template>
                        Prev
                    </paginate-btn>

                    <!-- Current Page Indicator -->
                    <div class="text-xs flex flex-col whitespace-nowrap text-center">
                                    <span class="font-medium mx-1" title="current page and total pages">
                                        <span>{{ apiResponse?.current_page }}</span> / <span>{{ apiResponse?.last_page }}</span>
                                    </span>
                    </div>

                    <!-- Next Button -->
                    <paginate-btn
                        @click="form.page = Math.min(apiResponse?.last_page, form.page + 1); searchEvent()"
                        :disabled="form.page === apiResponse?.last_page"
                    >
                        Next
                        <template v-slot:icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>

                    <!-- Last Button -->
                    <paginate-btn
                        @click="form.page = apiResponse?.last_page; searchEvent()"
                        :disabled="form.page === apiResponse?.last_page"
                    >
                        Last
                    </paginate-btn>
                </div>
            </div>
        </div>
    </form>
    <component v-if="cardSlot"
               :is="cardSlot"
               :apiResponse="apiResponse"
               :processing="model.api.processing"
               :model="propModel"
               @confirmDelete="confirmDelete = true; toDelete = $event"
               class="my-2"
    />
    <div v-if="apiResponse" class="flex w-full gap-2 items-center">
        <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
            <!-- First Button -->
            <paginate-btn @click="form.page = 1; searchEvent()" :disabled="form.page === 1">
                First
            </paginate-btn>

            <!-- Previous Button -->
            <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent()" :disabled="form.page === 1">
                <template v-slot:icon>
                    <arrow-left class="h-auto w-6" />
                </template>
                Prev
            </paginate-btn>

            <!-- Current Page Indicator -->
            <div class="text-xs flex flex-col whitespace-nowrap text-center">
                <span class="font-medium mx-1" title="current page and total pages">
                    <span>{{ apiResponse?.current_page }}</span> / <span>{{ apiResponse?.last_page }}</span>
                </span>
            </div>

            <!-- Next Button -->
            <paginate-btn
                @click="form.page = Math.min(apiResponse?.last_page, form.page + 1); searchEvent()"
                :disabled="form.page === apiResponse?.last_page"
            >
                Next
                <template v-slot:icon>
                    <arrow-right class="h-auto w-6" />
                </template>
            </paginate-btn>

            <!-- Last Button -->
            <paginate-btn
                @click="form.page = apiResponse?.last_page; searchEvent()"
                :disabled="form.page === apiResponse?.last_page"
            >
                Last
            </paginate-btn>
        </div>
    </div>
    <confirmation-modal :show="confirmDelete" @close="confirmDelete = false">
        <template v-slot:title>
            Are you sure you want to remove this data?
        </template>

        <template v-slot:content>
            This will permanently delete <b>{{ toDelete.fullName }} ({{ toDelete.id }})</b> from the database.
        </template>

        <template v-slot:footer>
            <div class="flex justify-between w-full">
                <delete-btn @close="confirmDelete = false" @click="handleDelete" :class="{'animate-pulse':model.processing}">
                    <span v-if="!model.processing">
                        Confirm
                    </span>
                    <span v-else>
                        Deleting
                    </span>
                </delete-btn>
                <label v-if="form" class="text-red-600 text-sm">{{ form.errors.event_id}}</label>
                <cancel-btn @click="confirmDelete = false">
                    Cancel
                </cancel-btn>
            </div>
        </template>
    </confirmation-modal>
</template>

<style scoped>

</style>
