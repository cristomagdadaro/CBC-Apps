<script>
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import ListOfUseRequests from "@/Pages/LabRequest/components/ListOfUseRequests.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import AccessUseHeaderActions from "@/Pages/LabRequest/components/AccessUseHeaderActions.vue";

export default {
    name: "AccessUseRequestsIndex",
    components: {
        AccessUseHeaderActions,
        CustomDropdown, FilterIcon,
        ListOfUseRequests,
        ArrowRight, ArrowLeft},
    mixins: [ApiMixin],
    data() {
        return {
            eventFormFromApi: null,
            statusOptions: [
                {name:'approved', label:'Approved'},
                {name:'rejected', label:'Rejected'},
                {name:'pending', label:'Pending'},
            ]
        }
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('get');
    },
    mounted() {
        this.searchEvent();
    },
    methods: {
        async searchEvent() {
            this.eventFormFromApi = await this.fetchData();
        },
        async fetchDataFilterStatus(filterVal) {
            this.form.search = filterVal;
            this.form.filter = 'request_status';
            this.form.is_exact = true;
            await this.searchEvent();
        }
    },
    watch: {
        'form.search': {
            handler(newVal, oldVal) {
                if (!newVal) {
                    this.form.filter = null;
                    this.form.is_exact = null;
                }
            },
            deep: true,
        }
    },
}
</script>

<template>
<app-layout title="Access Use Requests">
    <template #header>
        <access-use-header-actions />
    </template>

    <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
        <form v-if="!!form" class="flex gap-2 items-end"  @submit.prevent="searchEvent">
            <div class="grid grid-rows-2 w-full">
                <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                    <div class="flex flex-col gap-0.5">
                        <div class="text-xs text-gray-500 flex items-center justify-between">
                            <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                        </div>
                        <custom-dropdown :with-all-option="false" :show-clear="true" @selectedChange="fetchDataFilterStatus($event)"  placeholder="Select a Status" :options="statusOptions">
                            <template #icon>
                                <filter-icon class="h-4 w-4" />
                            </template>
                        </custom-dropdown>
                    </div>
                    <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                    <text-input v-if="form.filter !== 'event_id'" placeholder="Search..." v-model="form.search" />
                    <search-btn type="submit" :disabled="model?.processing" class="w-[10rem] text-center">
                        <span v-if="!model?.processing">Search</span>
                        <span v-else>Searching</span>
                    </search-btn>
                </div>
                <div v-if="eventFormFromApi" class="flex w-full gap-2 items-center">
                    <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                        <!-- First Button -->
                        <paginate-btn @click="form.page = 1; searchEvent();" :disabled="form.page === 1">
                            First
                        </paginate-btn>

                        <!-- Previous Button -->
                        <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent();" :disabled="form.page === 1">
                            <template v-slot:icon>
                                <arrow-left class="h-auto w-6" />
                            </template>
                            Prev
                        </paginate-btn>

                        <!-- Current Page Indicator -->
                        <div class="text-xs flex flex-col whitespace-nowrap text-center">
                            <span class="font-medium mx-1" title="current page and total pages">
                                <span>{{ eventFormFromApi?.current_page }}</span> / <span>{{ eventFormFromApi?.last_page }}</span>
                            </span>
                        </div>

                        <!-- Next Button -->
                        <paginate-btn
                            @click="form.page = Math.min(eventFormFromApi?.last_page, form.page + 1); searchEvent();"
                            :disabled="form.page === eventFormFromApi?.last_page"
                        >
                            Next
                            <template v-slot:icon>
                                <arrow-right class="h-auto w-6" />
                            </template>
                        </paginate-btn>

                        <!-- Last Button -->
                        <paginate-btn
                            @click="form.page = eventFormFromApi?.last_page; searchEvent();"
                            :disabled="form.page === eventFormFromApi?.last_page"
                        >
                            Last
                        </paginate-btn>
                    </div>
                </div>
            </div>
        </form>
        <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg">
            <!-- Show forms when available -->
            <list-of-use-requests
                v-if="eventFormFromApi && eventFormFromApi.total > 0 && !model.api.processing"
                :forms-data="eventFormFromApi.data"
                @removeModel="eventFormFromApi.data = eventFormFromApi.data.filter(form => form.id !== $event.id)"
                @updated="searchEvent"
            />

            <!-- Show "Searching" when processing -->
            <div v-else-if="model.api.processing" class="text-center py-3 border border-AB rounded-lg">
                Searching...
            </div>

            <!-- Show "Form does not exist" when search was performed but no results -->
            <div v-else-if="eventFormFromApi && eventFormFromApi.total === 0 && form.search" class="text-center py-3 border border-AB rounded-lg">
                Form does not exist. Try using some filters.
            </div>

            <!-- Show "No forms available" when nothing was returned and no search was performed -->
            <div v-else class="text-center py-3 border border-AB rounded-lg">
                No forms available.
            </div>
        </div>
        <div v-if="eventFormFromApi && eventFormFromApi.data?.length" class="flex w-full gap-2 py-5 items-center">
            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                <!-- First Button -->
                <paginate-btn @click="form.page = 1; searchEvent();" :disabled="form.page === 1">
                    First
                </paginate-btn>

                <!-- Previous Button -->
                <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent();" :disabled="form.page === 1">
                    <template v-slot:icon>
                        <arrow-left class="h-auto w-6" />
                    </template>
                    Prev
                </paginate-btn>

                <!-- Current Page Indicator -->
                <div class="text-xs flex flex-col whitespace-nowrap text-center">
                    <span class="font-medium mx-1" title="current page and total pages">
                        <span>{{ eventFormFromApi?.current_page }}</span> / <span>{{ eventFormFromApi?.last_page }}</span>
                    </span>
                </div>

                <!-- Next Button -->
                <paginate-btn
                    @click="form.page = Math.min(eventFormFromApi?.last_page, form.page + 1); searchEvent();"
                    :disabled="form.page === eventFormFromApi?.last_page"
                >
                    Next
                    <template v-slot:icon>
                        <arrow-right class="h-auto w-6" />
                    </template>
                </paginate-btn>

                <!-- Last Button -->
                <paginate-btn
                    @click="form.page = eventFormFromApi?.last_page; searchEvent();"
                    :disabled="form.page === eventFormFromApi?.last_page"
                >
                    Last
                </paginate-btn>
            </div>
        </div>
    </div>
</app-layout>
</template>

<style scoped>

</style>
