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
            class="py-6 sm:py-10 max-w-3xl mx-auto px-4"
        >
            <div
                class="flex flex-col gap-4 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs rounded-2xl p-4 sm:p-6 text-slate-900 dark:text-slate-100"
            >
                <div class="flex flex-col pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h2
                        class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-900 dark:text-slate-100"
                    >
                        Personnel Update Form
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Use this form to update personnel information.</p>
                </div>
                <div class="flex sm:flex-row flex-col gap-2">
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
                        label="Suffix"
                        v-model="form.suffix"
                        :error="form.errors.suffix"
                    />
                </div>
                <div class="flex flex-col gap-3">
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
                <div class="flex flex-col sm:flex-row gap-3">
                    <text-input
                        label="Employee ID / CBC ID"
                        required
                        class="flex-1"
                        v-model="form.employee_id"
                        :error="form.errors.employee_id"
                    />
                    <div class="flex flex-col gap-1 w-full sm:w-1/3">
                        <label class="block font-semibold text-xs sm:text-sm text-slate-700 dark:text-slate-300">Status</label>
                        <select
                            v-model="form.status"
                            class="border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-lime-500 focus:ring-lime-500 rounded-xl shadow-xs w-full text-xs sm:text-sm py-2.5"
                        >
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-rose-500 mt-1 font-semibold">{{ form.errors.status }}</p>
                    </div>
                </div>
                <text-input
                    label="Affiliation / School / Agency"
                    v-model="form.affiliation"
                    :error="form.errors.affiliation"
                />
                <div class="space-y-1">
                    <text-input
                        label="Expires At (optional)"
                        type="date"
                        v-model="form.expires_at"
                        :error="form.errors.expires_at"
                    />
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Set an expiry date for temporary personnel (OJT, Student, Thesis). Status will auto-set to Suspended after this date.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-2">
                    <div class="flex gap-2 w-full sm:w-auto">
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
