<script>
import GuestFormPage from '@/Pages/Shared/GuestFormPage.vue';
import SuppEquipReportForm from "@/Pages/Inventory/SuppEquipReports/components/SuppEquipReportForm.vue";
import SuccessModal from "@/Components/SuccessModal.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    name: "SuppEquipReportsCreateGuest",
    components: {
        GuestFormPage,
        SuppEquipReportForm,
        SuccessModal,
    },
    mixins: [ApiMixin],
    props: {
        reportTemplates: {
            type: Object,
            required: true,
        },
        barcode: {
            type: String,
            required: false,
        }
    },
    data() {
        return {
            delayReady: false,
            showSuccessModal: false,
            successMessage: '',
        };
    },
    async mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    },
    methods: {
        async closeForm() {
            this.showSuccessModal = true;
            this.showModel = false;
            this.resetForm();
        },
    }
};
</script>

<template>
    <Head title="Attach Supplies and Equipment Report" />
    <SuccessModal
        :show="showSuccessModal"
        title="Report Submitted"
        :message="successMessage"
        @close="showSuccessModal = false"
    />
    
    <GuestFormPage
        title="Supplies and Equipment Report Form"
        subtitle="Link a supplies or equipment incident to a transaction for faster auditing and compliance reviews."
        :delay-ready="delayReady"
    >
        <div class="py-10">
            <div class="max-w-4xl mx-auto space-y-6 px-4">
                <supp-equip-report-form :report-templates="reportTemplates" :barcode="barcode" @saved="closeForm"/>
            </div>
        </div>
    </GuestFormPage>
</template>

<style scoped>

</style>
