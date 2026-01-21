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

export default {
    name: "ListOfParticipants",
    components: {
        DataTable, CustomDropdown, FilterIcon,
        ListOfForms, ArrowRight, TextInput, SearchBtn, ArrowLeft, PaginateBtn, InputError, SearchBy, SearchComp},
    props: {
        eventId: {
            type: String,
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
    },
    data() {
        return {
            eventFormFromApi: null,
            selectedFormType: null,
        }
    },
    beforeMount() {
        this.model = new SubformResponse();
        const baseSearchFields = {
            ...this.model.api.getSearchFields(),
            filter_by_parent_column: null,
            filter_by_parent_id: null,
        };
        this.model.api.setSearchFields(baseSearchFields);
        this.setFormAction('get');
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

            this.form.filter_by_parent_column = 'form_parent_id';
            this.form.filter_by_parent_id = this.eventId;
            this.form.per_page = 'all';

            if (this.selectedFormType) {
                this.form.filter = 'subform_type';
                this.form.search = this.selectedFormType;
                this.form.is_exact = true;
            } else {
                this.form.filter = null;
                this.form.search = null;
            }

            this.eventFormFromApi = await this.fetchData();
            this.decorateResponseRows();
        },
        async attachedFormChange(selected) {
            this.selectedFormType = selected;
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
    <div>
        <custom-dropdown
            label="Attached Form"
            :options="formList"
            :value="selectedFormType"
            :withAllOption="true"
            @selectedChange="attachedFormChange"
            placeholder="Select an attached form"
        >
            <template #icon>
                <filter-icon class="h-4 w-4" />
            </template>
        </custom-dropdown>
        <h2 class="text-2xl font-semibold mb-4">List of Respondents</h2>
        <p class="mb-6">Total: {{ eventFormFromApi?.data?.length || 0 }}</p>
    </div>
    <data-table
        :api-response="eventFormFromApi"
        :model="ResponseModel"
        :processing="model?.api?.processing"
        :append-actions="false"
        enableExport
    />
</template>

<style scoped>

</style>
