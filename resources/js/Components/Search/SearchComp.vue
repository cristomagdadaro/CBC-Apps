<script>
import PaginateBtn from "@/Components/PaginateBtn.vue";
import InputError from "@/Components/InputError.vue";
import SearchBy from "@/Components/SearchBy.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";
import SearchBox from "@/Pages/Inventory/Scan/components/searchBox.vue";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel.js";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import {defineAsyncComponent} from "vue";

export default {
    name: "SearchComp",
    components: {SearchBox, ArrowRight, TextInput, SearchBtn, ArrowLeft, SearchBy, InputError, PaginateBtn},
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
            type: [Object, Function],
            required: false,
            /*default: defineAsyncComponent({
                loader: () => import("@/Components/CRCMDatatable/Layouts/DefaultBlankForm.vue"),
            }),*/
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
    async mounted() {
        this.apiResponse =  await this.fetchData();
    },
    watch: {
        'form.page': {
            handler(newVal, oldVal) {
                this.searchEvent();
            },
            deep: true,
        }
    },
    computed: {
        columns() {
            return this.propModel.getColumns()
                .map(column => column && column.visible ? { name: column.key, label: column.title } : null)
                .filter(Boolean);
        }
    },
    methods: {
        async searchEvent() {
            this.apiResponse = null;
            this.apiResponse = await this.fetchData();
            this.form.search = null;
            this.$emit("searchedData", this.apiResponse);
        },
    }
}
</script>

<template>
    <form v-if="!!form" class="flex gap-2 items-end select-none"  @submit.prevent="searchEvent">
        <div class="grid grid-rows-2 w-full">
            <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                <div class="flex gap-3 w-full">
                    <search-by
                        :value="form.filter"
                        :is-exact="form.is_exact"
                        :options="columns"
                        @searchBy="form.filter = $event"
                        @isExact="form.is_exact = $event"
                    />
                    <search-box class="w-full" v-model="form.search" />
                </div>
                <search-btn type="submit" :disabled="model?.processing" class="w-[10rem] text-center">
                    <span v-if="!model?.processing">Search</span>
                    <span v-else>Searching</span>
                </search-btn>
            </div>
            <div v-if="apiResponse" class="flex w-full gap-2 items-center">
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
                                        <span>{{ apiResponse?.current_page }}</span> / <span>{{ apiResponse?.last_page }}</span>
                                    </span>
                    </div>

                    <!-- Next Button -->
                    <paginate-btn
                        @click="form.page = Math.min(apiResponse?.last_page, form.page + 1)"
                        :disabled="form.page === apiResponse?.last_page"
                    >
                        Next
                        <template v-slot:icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>

                    <!-- Last Button -->
                    <paginate-btn
                        @click="form.page = apiResponse?.last_page"
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
    />
    <div v-if="apiResponse" class="flex w-full gap-2 items-center">
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
                                        <span>{{ apiResponse?.current_page }}</span> / <span>{{ apiResponse?.last_page }}</span>
                                    </span>
            </div>

            <!-- Next Button -->
            <paginate-btn
                @click="form.page = Math.min(apiResponse?.last_page, form.page + 1)"
                :disabled="form.page === apiResponse?.last_page"
            >
                Next
                <template v-slot:icon>
                    <arrow-right class="h-auto w-6" />
                </template>
            </paginate-btn>

            <!-- Last Button -->
            <paginate-btn
                @click="form.page = apiResponse?.last_page"
                :disabled="form.page === apiResponse?.last_page"
            >
                Last
            </paginate-btn>
        </div>
    </div>
</template>

<style scoped>

</style>
