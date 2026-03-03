<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import {defineAsyncComponent} from "vue";
import DtoResponse from "@/Modules/dto/DtoResponse.js";

export default {
    name: "SearchComp",
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
            confirmDelete: false,
            toDelete: null,
            searchDebounceTimer: null,
            searchDebounceDelay: 350,
            latestRequestId: 0,
            autoSearchReady: false,
        }
    },
    beforeMount() {
        this.model = new this.propModel();
        this.setFormAction(this.action);
    },
    async mounted() {
        await this.searchEvent();
        this.autoSearchReady = true;
    },
    beforeUnmount() {
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
            this.searchDebounceTimer = null;
        }
    },
    watch: {
        'form.search'() {
            if (!this.autoSearchReady) return;
            this.debouncedSearch(true);
        },
        'form.filter'() {
            if (!this.autoSearchReady) return;
            this.searchEvent({ page: 1 });
        },
        'form.is_exact'() {
            if (!this.autoSearchReady) return;
            this.searchEvent({ page: 1 });
        },
        'form.per_page'() {
            if (!this.autoSearchReady) return;
            this.searchEvent({ page: 1 });
        },
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
                {name:'*', label:'All'},
            ]
        }
    },
    methods: {
        debouncedSearch(resetPage = false) {
            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }

            this.searchDebounceTimer = setTimeout(() => {
                this.searchEvent(resetPage ? { page: 1 } : {});
            }, this.searchDebounceDelay);
        },
        triggerImmediateSearch(resetPage = false) {
            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
                this.searchDebounceTimer = null;
            }

            this.searchEvent(resetPage ? { page: 1 } : {});
        },
        async searchEvent(params = {}) {
            // merge params into form
            Object.keys(params).forEach(key => {
                this.form[key] = params[key];
            });

            const currentRequestId = ++this.latestRequestId;
            const response = await this.fetchData();

            if (currentRequestId !== this.latestRequestId) {
                return;
            }

            this.apiResponse = response;
            this.$emit("searchedData", this.apiResponse);
        },
        async handleDelete()
        {
            if (!this.toDelete) {
                return;
            }

            const response = await this.submitDelete();
            if (response instanceof DtoResponse)
            {
                this.confirmDelete = false;
                this.toDelete = null;
                this.$emit("deletedModel", response.data);
                // Auto-refresh data after successful delete
                await this.searchEvent();
            }
        },
        async handleDataTableDelete(row)
        {
            // Set the row to delete and open confirmation modal from DataTable action
            this.toDelete = row;
            this.confirmDelete = true;
        }
    }
}
</script>

<template>
    <form v-if="!!form" class="flex gap-2 items-end "  @submit.prevent="triggerImmediateSearch(true)">
        <div class="flex flex-col w-full gap-2">
            <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                <div class="flex gap-3 w-full">
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between ">
                            <label class="text-gray-600 text-xs">Per Page</label>
                        </div>
                        <custom-dropdown :show-clear="false"
                            :value="form.per_page"
                            :options="perPageOptions"
                            :withAllOption="false"
                            @selectedChange="form.per_page = $event"
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
                    <search-box class="w-full" v-model="form.search" @keydown.enter.prevent="triggerImmediateSearch(true)"/>
                    <div class="flex flex-col gap-0.5">
                        <div class="flex justify-between ">
                            <label class="text-gray-600 text-xs">&nbsp;</label>
                        </div>
                        <search-btn type="submit" :disabled="model?.processing || processing" class="w-[10rem] text-center h-full hover:bg-AB duration-150">
                            <span v-if="!model?.processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                </div>
            </div>
            <div v-if="apiResponse" class="flex w-full gap-2 items-center">
                <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                    <!-- First Button -->
                    <paginate-btn @click="form.page = 1; triggerImmediateSearch()" :disabled="form.page === 1 || processing">
                        First
                    </paginate-btn>

                    <!-- Previous Button -->
                    <paginate-btn @click="form.page = Math.max(1, form.page - 1); triggerImmediateSearch()" :disabled="form.page === 1 || processing">
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
                        :disabled="form.page === apiResponse?.last_page || processing"
                    >
                        Next
                        <template v-slot:icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>

                    <!-- Last Button -->
                    <paginate-btn
                        @click="form.page = apiResponse?.last_page; searchEvent()"
                        :disabled="form.page === apiResponse?.last_page || processing"
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
        @searchEvent="searchEvent"
        @confirmDelete="confirmDelete = true; toDelete = $event"
        @delete-record="handleDataTableDelete"
        class="my-2"
    />
    <div v-if="apiResponse" class="flex w-full gap-2 items-center">
        <delete-confirmation-modal
            v-if="confirmDelete"
            :show="confirmDelete"
            :is-processing="model.api.processing"
            title="Confirm Delete"
            :message="`Are you sure you want to delete this record? This action cannot be undone.`"
            :item-name="toDelete?.fullName ? `${toDelete.fullName} (${toDelete.id})` : ''"
            @confirm="handleDelete"
            @close="confirmDelete = false; toDelete = null"
        />
    </div>
</template>

<style scoped>

</style>
