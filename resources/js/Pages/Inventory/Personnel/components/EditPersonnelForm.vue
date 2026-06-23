<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import DangerButton from "@/Components/DangerButton.vue";

export default {
    name: "EditPersonnelForm",
    components: { DangerButton, AuditInfoCard, PersonnelHeaderActions, FormsHeaderActions },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Personnel();
        this.setFormAction("update");
    },
    methods: {
        async returnToApproval() {
            if (confirm('Are you sure you want to return this personnel to the Registration Approval stage? This will suspend their current active personnel record.')) {
                try {
                    await this.fetchPostApi(route('api.inventory.personnels.revert', this.$page.props.data.id));
                    this.$notify({ title: 'Success', text: 'Personnel returned to approval stage.', type: 'success' });
                    this.$inertia.reload();
                } catch (error) {
                    this.$notify({ title: 'Error', text: error.response?.data?.message || 'Failed to revert personnel.', type: 'error' });
                }
            }
        }
    }
};
</script>

<template>
    <AppLayout title="Update Personnel Information">
        <template #header>
            <personnel-header-actions />
        </template>

        <form
            v-if="!!form"
            @submit.prevent="submitUpdate"
            class="py-12 max-w-3xl mx-auto"
        >
            <div
                class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
            >
                <div class="flex flex-col">
                    <h2
                        class="font-bold uppercase leading-none py-2 mb-1 border-b"
                    >
                        Personnel Update Form
                    </h2>
                    <p>Use this form to update personnel information.</p>
                </div>
                <div class="flex sm:flex-row flex-col gap-1">
                    <text-input
                        required
                        label="First Name"
                        v-model="form.fname"
                        :error="form.errors.fname"
                    />
                    <text-input
                        label="Middle Name"
                        v-model="form.mname"
                        :error="form.errors.mname"
                    />
                    <text-input
                        required
                        label="Last Name"
                        v-model="form.lname"
                        :error="form.errors.lname"
                    />
                    <text-input
                        label="suffix"
                        v-model="form.suffix"
                        :error="form.errors.suffix"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <text-input
                        required
                        label="Position"
                        v-model="form.position"
                        :error="form.errors.position"
                    />
                    <text-input
                        label="Phone"
                        v-model="form.phone"
                        :error="form.errors.phone"
                    />
                    <text-input
                        label="Email (optional)"
                        v-model="form.email"
                        :error="form.errors.email"
                    />
                </div>
                <text-input
                    label="Address"
                    v-model="form.address"
                    :error="form.errors.address"
                />
                <div class="flex gap-2">
                    <text-input
                        label="Employee ID / CBC ID"
                        required
                        class="flex-1"
                        v-model="form.employee_id"
                        :error="form.errors.employee_id"
                    />
                    <div class="flex flex-col gap-1 w-1/3">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status</label>
                        <select
                            v-model="form.status"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full"
                        >
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                        <p v-if="form.errors.status" class="text-sm text-red-600 mt-2">{{ form.errors.status }}</p>
                    </div>
                </div>
                <div class="flex gap-1 justify-between items-center">
                    <div class="flex gap-2">
                        <reset-btn @click="resetField($page.props.data)">
                            Reset
                        </reset-btn>
                        <danger-button v-if="$isAdminUser" @click="returnToApproval" type="button">
                            Return to Registration Approval
                        </danger-button>
                    </div>
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

<style scoped></style>
