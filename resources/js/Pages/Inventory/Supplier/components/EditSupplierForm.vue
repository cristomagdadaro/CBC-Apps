<script>
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import {Head} from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Supplier from "@/Modules/domain/Supplier";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";
import SupplierHeaderActions from "@/Pages/Inventory/Supplier/components/SupplierHeaderActions.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";

export default {
    name: "EditSupplierForm",
    components: {
        AuditInfoCard,
        SupplierHeaderActions,
        PersonnelHeaderActions,
        Head,
        LoaderIcon
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Supplier();
        this.setFormAction('update');
    },
}
</script>

<template>
    <AppLayout title="Update Personnel Information">
        <template #header>
            <supplier-header-actions />
        </template>

        <form v-if="!!form" @submit.prevent="submitUpdate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Supplier Update Form</h2>
                    <p>Use this form to update supplier details.</p>
                </div>
                <text-input required label="Company Name" v-model="form.name" :error="form.errors.name" />
                <text-input label="Email" v-model="form.email" :error="form.errors.email" />
                <text-input label="Phone" v-model="form.phone" :error="form.errors.phone" />
                <text-input label="Address" v-model="form.address" :error="form.errors.address" />
                <text-area label="Description" v-model="form.description" :error="form.errors.description" />
                <div class="flex gap-1 justify-between">
                    <reset-btn @click="resetField($page.props.data)">
                        Reset
                    </reset-btn>
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Updating</span>
                        <span v-else>Update</span>
                    </submit-btn>
                </div>
                <audit-info-card
                    :audit-logs="$page.props.auditLogs"
                    :created-at="$page.props.data.created_at"
                    :updated-at="$page.props.data.updated_at"
                />
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>

</style>
