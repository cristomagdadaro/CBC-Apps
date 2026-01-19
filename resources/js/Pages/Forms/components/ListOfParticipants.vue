<script>
import SearchComp from "@/Components/Search/SearchComp.vue";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import Participant from "@/Modules/domain/Participant";
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
        Participants() {
            return Participant;
        },
        formList() {
            const labelMap = {
                pre_registration: 'Pre-registration',
                registration: 'Registration',
                pre_test: 'Pre-test',
                post_test: 'Post-test',
                feedback: 'Feedback',
            };

            return (this.$page.props?.data?.requirements ?? []).map(item => ({
                label: labelMap[item.form_type] ?? item.form_type,
                name: item.id,
            }));
        },
    },
    data() {
        return {
            eventFormFromApi: null,
        }
    },
    beforeMount() {
        this.model = new Participant();
        this.setFormAction('get');
    },
    async mounted() {
        this.form.filter = 'event_id';
        this.form.search = this.eventId;
        this.form.is_exact = true;

        this.searchEvent();
    },
    methods: {
        async searchEvent() {
            this.eventFormFromApi = null;
            this.eventFormFromApi = await this.fetchData();
        },
        attachedFormChange(selected) {
            console.log('selected form id', selected);
        },
    }
}
</script>

<template>
    <div>
        <custom-dropdown label="Attached Form" :options="formList" :withAllOption="false" @selectedChange="attachedFormChange($event)" placeholder="Select an attached form">
            <template #icon>
                <filter-icon class="h-4 w-4" />
            </template>
        </custom-dropdown>
        <h2 class="text-2xl font-semibold mb-4">List of Respondents</h2>
        <p class="mb-6">Total: {{ eventFormFromApi?.data?.length || 0 }}</p>
    </div>
    <data-table :api-response="eventFormFromApi" :model="Participants" @deleted="searchEvent" enableExport/>
</template>

<style scoped>

</style>
