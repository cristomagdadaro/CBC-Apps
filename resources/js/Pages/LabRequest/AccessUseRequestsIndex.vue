<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import ListOfUseRequests from "@/Pages/LabRequest/components/ListOfUseRequests.vue";
import AccessUseHeaderActions from "@/Pages/LabRequest/components/AccessUseHeaderActions.vue";
import PaginationControls from "@/Pages/LabRequest/components/PaginationControls.vue";

export default {
    name: "AccessUseRequestsIndex",
    components: {
        AccessUseHeaderActions,
        ListOfUseRequests,
        PaginationControls,
    },
    mixins: [ApiMixin],
    data() {
        return {
            eventFormFromApi: null,
            statusOptions: [
                { name: "released", label: "Released" },
                { name: "returned", label: "Returned" },
                { name: "approved", label: "Approved" },
                { name: "rejected", label: "Rejected" },
                { name: "pending", label: "Pending" },
            ],
        };
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction("get");
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
            this.form.filter = "request_status";
            this.form.is_exact = true;
            await this.searchEvent();
        },
        async changePage(page) {
            this.form.page = page;
            await this.searchEvent();
        },
    },
    watch: {
        "form.search": {
            handler(newVal) {
                if (!newVal) {
                    this.form.filter = null;
                    this.form.is_exact = null;
                }
            },
            deep: true,
        },
    },
};
</script>

<template>
    <app-layout title="Access Use Requests">
        <template #header>
            <access-use-header-actions />
        </template>

        <div class="default-container pt-5">
            <form
                v-if="!!form"
                class="flex items-end gap-2"
                @submit.prevent="searchEvent">
                <div class="grid w-full grid-rows-2">
                    <div class="flex w-full items-end gap-2 px-2 lg:px-0">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                            </div>
                            <custom-dropdown
                                :with-all-option="false"
                                :show-clear="true"
                                :show-valid-indicator="false"
                                @selectedChange="fetchDataFilterStatus($event)"
                                placeholder="Select a Status"
                                :options="statusOptions">
                                <template #icon>
                                    <filter-icon class="h-4 w-4" />
                                </template>
                            </custom-dropdown>
                        </div>
                        <search-by
                            :value="form.filter"
                            :is-exact="form.is_exact"
                            :options="model.constructor.getFilterColumns()"
                            @isExact="form.is_exact = $event"
                            @searchBy="form.filter = $event" />
                        <text-input
                            v-if="form.filter !== 'event_id'"
                            placeholder="Search..."
                            v-model="form.search" />
                        <search-btn
                            type="submit"
                            :disabled="model?.processing"
                            class="w-[10rem] text-center">
                            <span v-if="!model?.processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                    <div
                        v-if="eventFormFromApi"
                        class="flex w-full items-center gap-2">
                        <pagination-controls
                            :current-page="eventFormFromApi?.current_page"
                            :last-page="eventFormFromApi?.last_page"
                            @change="changePage" />
                    </div>
                </div>
            </form>
            <div class="overflow-hidden bg-white sm:rounded-lg dark:bg-gray-800">
                <list-of-use-requests
                    v-if="eventFormFromApi && eventFormFromApi.total > 0 && !model.api.processing"
                    :forms-data="eventFormFromApi.data"
                    @removeModel="eventFormFromApi.data = eventFormFromApi.data.filter((form) => form.id !== $event.id)"
                    @updated="searchEvent" />

                <!-- Show "Searching" when processing -->
                <div
                    v-else-if="model.api.processing"
                    class="rounded-lg border border-AB py-3 text-center">
                    Searching...
                </div>

                <!-- Show "Form does not exist" when search was performed but no results -->
                <div
                    v-else-if="eventFormFromApi && eventFormFromApi.total === 0 && form.search"
                    class="rounded-lg border border-AB py-3 text-center">
                    Form does not exist. Try using some filters.
                </div>

                <!-- Show "No forms available" when nothing was returned and no search was performed -->
                <div
                    v-else
                    class="rounded-lg border border-AB py-3 text-center">
                    No forms available.
                </div>
            </div>
            <div
                v-if="eventFormFromApi && eventFormFromApi.data?.length"
                class="flex w-full items-center gap-2 py-5">
                <pagination-controls
                    :current-page="eventFormFromApi?.current_page"
                    :last-page="eventFormFromApi?.last_page"
                    @change="changePage" />
            </div>
        </div>
    </app-layout>
</template>

<style scoped></style>
