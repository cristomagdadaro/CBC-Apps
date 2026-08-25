<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RentalVenue from "@/Modules/domain/RentalVenue";
import RentalsHeaderAction from "@/Pages/Rentals/components/RentalsHeaderAction.vue";
import ListOfRentalVenueRequests from "@/Pages/Rentals/components/ListOfRentalVenueRequests.vue";

export default {
    name: "RentalsVenueIndex",
    components: {
        RentalsHeaderAction,
        ListOfRentalVenueRequests,
    },
    mixins: [ApiMixin],
    data() {
        return {
            rentalsFromApi: null,
            statusOptions: [
                { name: "approved", label: "Approved" },
                { name: "in_progress", label: "In Progress" },
                { name: "rejected", label: "Rejected" },
                { name: "cancelled", label: "Cancelled" },
                { name: "completed", label: "Completed" },
                { name: "pending", label: "Pending" },
            ],
        };
    },
    beforeMount() {
        this.model = new RentalVenue();
        this.setFormAction("get");
        this.form.per_page = 15;
    },
    mounted() {
        this.searchRentals();
    },
    methods: {
        async searchRentals() {
            this.rentalsFromApi = await this.fetchData();
        },
        async fetchDataFilterStatus(filterVal) {
            this.form.search = filterVal;
            this.form.filter = "status";
            this.form.is_exact = true;
            this.form.page = 1;
            await this.searchRentals();
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
    <Head title="Rental Services" />

    <AppLayout>
        <template #header>
            <rentals-header-action />
        </template>

        <div class="default-container pt-5">
            <form
                v-if="!!form"
                class="flex items-end gap-2"
                @submit.prevent="searchRentals">
                <div class="grid w-full grid-rows-2">
                    <div class="flex w-full items-end gap-2 px-2 lg:px-0">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                            </div>
                            <custom-dropdown
                                :with-all-option="false"
                                :show-clear="true"
                                @selectedChange="fetchDataFilterStatus($event)"
                                placeholder="Select a Status"
                                :options="statusOptions"
                                :show-valid-indicator="false">
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
                            placeholder="Search..."
                            v-model="form.search" />
                        <search-btn
                            type="submit"
                            :disabled="processing"
                            class="w-[10rem] text-center">
                            <span v-if="!processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                    <div
                        v-if="rentalsFromApi"
                        class="flex w-full items-center gap-2">
                        <div class="flex w-full items-center justify-center gap-1">
                            <paginate-btn
                                @click="
                                    form.page = 1;
                                    searchRentals();
                                "
                                :disabled="form.page === 1">
                                First
                            </paginate-btn>
                            <paginate-btn
                                @click="
                                    form.page = Math.max(1, form.page - 1);
                                    searchRentals();
                                "
                                :disabled="form.page === 1">
                                <template #icon>
                                    <arrow-left class="h-auto w-6" />
                                </template>
                                Prev
                            </paginate-btn>
                            <div class="flex flex-col whitespace-nowrap text-center text-xs">
                                <span
                                    class="mx-1 font-medium"
                                    title="current page and total pages">
                                    <span>{{ rentalsFromApi?.current_page }}</span>
                                    /
                                    <span>{{ rentalsFromApi?.last_page }}</span>
                                </span>
                            </div>
                            <paginate-btn
                                @click="
                                    form.page = Math.min(rentalsFromApi?.last_page, form.page + 1);
                                    searchRentals();
                                "
                                :disabled="form.page === rentalsFromApi?.last_page">
                                Next
                                <template #icon>
                                    <arrow-right class="h-auto w-6" />
                                </template>
                            </paginate-btn>
                            <paginate-btn
                                @click="
                                    form.page = rentalsFromApi?.last_page;
                                    searchRentals();
                                "
                                :disabled="form.page === rentalsFromApi?.last_page">
                                Last
                            </paginate-btn>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-3 overflow-hidden bg-white sm:rounded-lg dark:bg-gray-800">
                <list-of-rental-venue-requests
                    v-if="rentalsFromApi && rentalsFromApi.total > 0 && !processing"
                    :rentals-data="rentalsFromApi.data"
                    @updated="searchRentals" />

                <div
                    v-else-if="processing"
                    class="rounded-lg border border-AB py-3 text-center">
                    Searching...
                </div>

                <div
                    v-else-if="rentalsFromApi && rentalsFromApi.total === 0 && form.search"
                    class="rounded-lg border border-AB py-3 text-center">
                    Request does not exist. Try using other filters.
                </div>

                <div
                    v-else
                    class="rounded-lg border border-AB py-3 text-center">
                    No rental requests available.
                </div>
            </div>

            <div
                v-if="rentalsFromApi && rentalsFromApi.data?.length"
                class="flex w-full items-center gap-2 py-5">
                <div class="flex w-full items-center justify-center gap-1">
                    <paginate-btn
                        @click="
                            form.page = 1;
                            searchRentals();
                        "
                        :disabled="form.page === 1">
                        First
                    </paginate-btn>
                    <paginate-btn
                        @click="
                            form.page = Math.max(1, form.page - 1);
                            searchRentals();
                        "
                        :disabled="form.page === 1">
                        <template #icon>
                            <arrow-left class="h-auto w-6" />
                        </template>
                        Prev
                    </paginate-btn>
                    <div class="flex flex-col whitespace-nowrap text-center text-xs">
                        <span
                            class="mx-1 font-medium"
                            title="current page and total pages">
                            <span>{{ rentalsFromApi?.current_page }}</span>
                            /
                            <span>{{ rentalsFromApi?.last_page }}</span>
                        </span>
                    </div>
                    <paginate-btn
                        @click="
                            form.page = Math.min(rentalsFromApi?.last_page, form.page + 1);
                            searchRentals();
                        "
                        :disabled="form.page === rentalsFromApi?.last_page">
                        Next
                        <template #icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>
                    <paginate-btn
                        @click="
                            form.page = rentalsFromApi?.last_page;
                            searchRentals();
                        "
                        :disabled="form.page === rentalsFromApi?.last_page">
                        Last
                    </paginate-btn>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
