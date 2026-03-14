<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RentalVehicle from '@/Modules/domain/RentalVehicle';
import RentalsHeaderAction from '@/Pages/Rentals/components/RentalsHeaderAction.vue';
import ListOfRentalVehicleRequests from "@/Pages/Rentals/components/ListOfRentalVehicleRequests.vue";

export default {
    name: 'RentalsVehicleIndex',
    components: {
        RentalsHeaderAction,
        ListOfRentalVehicleRequests,
    },
    mixins: [ApiMixin],
    data() {
        return {
            rentalsFromApi: null,
            statusOptions: [
                { name: 'approved', label: 'Approved' },
                { name: 'rejected', label: 'Declined' },
                { name: 'pending', label: 'Pending' },
            ],
        };
    },
    beforeMount() {
        this.model = new RentalVehicle();
        this.setFormAction('get');
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
            this.form.filter = 'status';
            this.form.is_exact = true;
            this.form.page = 1;
            await this.searchRentals();
        },
    },
    watch: {
        'form.search': {
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
            <form v-if="!!form" class="flex gap-2 items-end" @submit.prevent="searchRentals">
                <div class="grid grid-rows-2 w-full">
                    <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                        <div class="flex flex-col gap-0.5">
                            <div class="text-xs text-gray-500 flex items-center justify-between">
                                <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                            </div>
                            <custom-dropdown
                                :with-all-option="false"
                                :show-clear="true"
                                @selectedChange="fetchDataFilterStatus($event)"
                                placeholder="Select a Status"
                                :options="statusOptions"
                            >
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
                            @searchBy="form.filter = $event"
                        />
                        <text-input placeholder="Search..." v-model="form.search" />
                        <search-btn type="submit" :disabled="processing" class="w-[10rem] text-center">
                            <span v-if="!processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                    <div v-if="rentalsFromApi" class="flex w-full gap-2 items-center">
                        <div class="flex gap-1 items-center w-full justify-center">
                            <paginate-btn @click="form.page = 1; searchRentals();" :disabled="form.page === 1">First</paginate-btn>
                            <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchRentals();" :disabled="form.page === 1">
                                <template #icon>
                                    <arrow-left class="h-auto w-6" />
                                </template>
                                Prev
                            </paginate-btn>
                            <div class="text-xs flex flex-col whitespace-nowrap text-center">
                                <span class="font-medium mx-1" title="current page and total pages">
                                    <span>{{ rentalsFromApi?.current_page }}</span> / <span>{{ rentalsFromApi?.last_page }}</span>
                                </span>
                            </div>
                            <paginate-btn
                                @click="form.page = Math.min(rentalsFromApi?.last_page, form.page + 1); searchRentals();"
                                :disabled="form.page === rentalsFromApi?.last_page"
                            >
                                Next
                                <template #icon>
                                    <arrow-right class="h-auto w-6" />
                                </template>
                            </paginate-btn>
                            <paginate-btn
                                @click="form.page = rentalsFromApi?.last_page; searchRentals();"
                                :disabled="form.page === rentalsFromApi?.last_page"
                            >
                                Last
                            </paginate-btn>
                        </div>
                    </div>
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg mt-3">
                <list-of-rental-vehicle-requests
                    v-if="rentalsFromApi && rentalsFromApi.total > 0 && !processing"
                    :rentals-data="rentalsFromApi.data"
                    @updated="searchRentals"
                />

                <div v-else-if="processing" class="text-center py-3 border border-AB rounded-lg">
                    Searching...
                </div>

                <div v-else-if="rentalsFromApi && rentalsFromApi.total === 0 && form.search" class="text-center py-3 border border-AB rounded-lg">
                    Request does not exist. Try using other filters.
                </div>

                <div v-else class="text-center py-3 border border-AB rounded-lg">
                    No rental requests available.
                </div>
            </div>

            <div v-if="rentalsFromApi && rentalsFromApi.data?.length" class="flex w-full gap-2 py-5 items-center">
                <div class="flex gap-1 items-center w-full justify-center">
                    <paginate-btn @click="form.page = 1; searchRentals();" :disabled="form.page === 1">First</paginate-btn>
                    <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchRentals();" :disabled="form.page === 1">
                        <template #icon>
                            <arrow-left class="h-auto w-6" />
                        </template>
                        Prev
                    </paginate-btn>
                    <div class="text-xs flex flex-col whitespace-nowrap text-center">
                        <span class="font-medium mx-1" title="current page and total pages">
                            <span>{{ rentalsFromApi?.current_page }}</span> / <span>{{ rentalsFromApi?.last_page }}</span>
                        </span>
                    </div>
                    <paginate-btn
                        @click="form.page = Math.min(rentalsFromApi?.last_page, form.page + 1); searchRentals();"
                        :disabled="form.page === rentalsFromApi?.last_page"
                    >
                        Next
                        <template #icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>
                    <paginate-btn
                        @click="form.page = rentalsFromApi?.last_page; searchRentals();"
                        :disabled="form.page === rentalsFromApi?.last_page"
                    >
                        Last
                    </paginate-btn>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
