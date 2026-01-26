<script>
import SearchComp from "@/Components/Search/SearchComp.vue";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SubformResponse from "@/Modules/domain/SubformResponse";
import SearchBy from "@/Components/SearchBy.vue";
import InputError from "@/Components/InputError.vue";
import PaginateBtn from "@/Components/PaginateBtn.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import Form from "@/Modules/domain/Form.js";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import SubformRequirement from "@/Modules/domain/SubformRequirement";

export default {
    name: "ListOfParticipants",
    components: {
        DataTable, CustomDropdown, FilterIcon,
        ListOfForms, ArrowRight, TextInput, SearchBtn, ArrowLeft, PaginateBtn, InputError, SearchBy, SearchComp},
    props: {
        eventId: {
            type: String,
        },
        formParentId: {
            type: String,
        },
        formClass: {
            type: [Function, Object],
            default: () => {}
        }
    },
    mixins: [ApiMixin],
    computed: {
        Form() {
            return Form
        },
        DataTable() {
            return DataTable
        },
        ResponseModel() {
            return SubformResponse;
        },
        formList() {
            const labelMap = {
                pre_registration: 'Pre-registration',
                pre_registration_biotech: 'Pre-registration + Quiz Bee',
                registration: 'Registration',
                pre_test: 'Pre-test',
                post_test: 'Post-test',
                feedback: 'Feedback',
            };

            const slugMap = {
                pre_registration: 'preregistration',
                pre_registration_biotech: 'preregistration_biotech',
                registration: 'registration',
                pre_test: 'pretest',
                post_test: 'posttest',
                feedback: 'feedback',
            };

            return (this.$page.props?.data?.requirements ?? []).map((item, index) => ({
                label: labelMap[item.form_type] ?? item.form_type,
                name: slugMap[item.form_type] ?? item.form_type,
                selected: index === 0,
            }));
        },
        formRequirementsOption() {
            return this.$page.props?.subformRequirements ?? [];
        },
        ParentForm() {
            return new Form(this.formClass) || Form;
        }
    },
    data() {
        return {
            eventFormFromApi: null,
            selectedFormType: null,
        }
    },
    beforeMount() {
        this.model = new SubformResponse();
        this.setFormAction('get');
        console.log(this.ParentForm);
    },
    async mounted() {
        this.selectedFormType = this.formList?.[0]?.name ?? null;
        await this.searchEvent();
    },
    methods: {
        async searchEvent() {
            if (!this.eventId) {
                this.eventFormFromApi = null;
                return;
            }
            await this.fetchGetApi('api.subform.response.index', { filter_by_parent_id: this.eventId, filter_by_parent_column: 'event_id'}, SubformResponse).then((response) => {
                this.eventFormFromApi = response;
            });
            this.decorateResponseRows();
        },
        async attachedFormChange(selected) {
            if (!this.form) {
                console.warn('Form not initialized yet');
                return;
            }
            this.form.filter_by_parent_id = selected;
            console.log(this.form.data());
            await this.searchEvent();
        },
        decorateResponseRows() {
            if (!this.eventFormFromApi?.data) {
                return;
            }

            this.eventFormFromApi.data = this.eventFormFromApi.data.map(response => {
                response.respondent_name = this.extractRespondentName(response.response_data);
                response.response_preview = this.formatResponsePreview(response.response_data);
                return response;
            });
        },
        extractRespondentName(responseData) {
            if (!responseData || typeof responseData !== 'object') {
                return 'N/A';
            }

            return responseData.name
                ?? responseData.full_name
                ?? responseData.organization
                ?? responseData.school
                ?? responseData.email
                ?? 'N/A';
        },
        formatResponsePreview(responseData) {
            if (!responseData || typeof responseData !== 'object') {
                return '';
            }

            const hiddenKeys = ['agreed_tc'];
            const pairs = Object.entries(responseData)
                .filter(([key, value]) => !hiddenKeys.includes(key) && value !== null && value !== '')
                .slice(0, 8)
                .map(([key, value]) => `${this.toLabel(key)}: ${this.normalizeValue(value)}`);

            return pairs.join(' | ');
        },
        toLabel(key) {
            return key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());
        },
        normalizeValue(value) {
            if (Array.isArray(value)) {
                return value.join(', ');
            }

            if (typeof value === 'boolean') {
                return value ? 'Yes' : 'No';
            }

            if (value && typeof value === 'object') {
                return JSON.stringify(value);
            }

            return value ?? '';
        },
    }
}
</script>

<template>
    <div class="sm:px-6 lg:px-8">{{ model.api.getSearchFields() }} 
        <form v-if="!!form" class="flex gap-2 items-end"  @submit.prevent="searchEvent">
            <div class="grid grid-rows-2 w-full">
                <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                    <div class="flex flex-col gap-0.5">
                        <div class="text-xs text-gray-500 flex items-center justify-between">
                            <span class="flex gap-0.5 whitespace-nowrap">Filter by Form</span>
                        </div>
                        <custom-dropdown :with-all-option="false" :show-clear="true" :value="form.form_parent_id" @selectedChange="attachedFormChange($event)"  placeholder="Select a Form" :options="formRequirementsOption">
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
            <data-table
                v-if="eventFormFromApi && eventFormFromApi.total > 0 && !model.api.processing"
                :api-response="eventFormFromApi"
                :model="ResponseModel"
                :processing="model?.api?.processing"
                :append-actions="false"
                enableExport
            />
            
            <div v-else-if="model.api.processing" class="text-center py-3 border border-AB rounded-lg">
                Searching...
            </div>
            <div v-else-if="eventFormFromApi && eventFormFromApi.total === 0 && form.search" class="text-center py-3 border border-AB rounded-lg">
                Response does not exist. Try using some filters.
            </div>

            <div v-else class="text-center py-3 border border-AB rounded-lg">
                No Response available.
            </div>
        </div>
        <div v-if="eventFormFromApi && eventFormFromApi.data?.length" class="flex w-full gap-2 py-5 items-center">
            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                <paginate-btn @click="form.page = 1; searchEvent();" :disabled="form.page === 1">
                    First
                </paginate-btn>
                <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent();" :disabled="form.page === 1">
                    <template v-slot:icon>
                        <arrow-left class="h-auto w-6" />
                    </template>
                    Prev
                </paginate-btn>
                <div class="text-xs flex flex-col whitespace-nowrap text-center">
                    <span class="font-medium mx-1" title="current page and total pages">
                        <span>{{ eventFormFromApi?.current_page }}</span> / <span>{{ eventFormFromApi?.last_page }}</span>
                    </span>
                </div>
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
</template>

<style scoped>

</style>
