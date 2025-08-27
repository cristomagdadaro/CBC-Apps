<script>
import SearchComp from "@/Components/Search/SearchComp.vue";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
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
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";

export default {
    name: "ListOfParticipants",
    components: {
        DataTable,
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

        this.eventFormFromApi =  await this.fetchData();
    },
    methods: {
        async searchEvent() {
            this.eventFormFromApi = null;

            this.eventFormFromApi = await this.fetchData();
        },
    }
}
</script>

<template>
    <div class="mx-auto sm:px-6 lg:px-8">
        <data-table :api-response="eventFormFromApi" :model="Participants" />
    </div>
</template>

<style scoped>

</style>
