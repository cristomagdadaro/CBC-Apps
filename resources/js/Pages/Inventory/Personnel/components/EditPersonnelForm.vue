<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";

export default {
    name: "EditPersonnelForm",
    components: { AuditInfoCard, PersonnelHeaderActions, FormsHeaderActions },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Personnel();
        this.setFormAction("update");
    },
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
                        Personnel Uppdate Form
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
                        required
                        label="Email"
                        v-model="form.email"
                        :error="form.errors.email"
                    />
                </div>
                <text-input
                    label="Address"
                    v-model="form.address"
                    :error="form.errors.address"
                />
                <span class="text-sm text-gray-600">
                    For OJT, interns, thesis students, or other temporary
                    personnel who do not have a PhilRice ID, please assign a
                    temporary CBC ID using the format
                    <strong>CBC-{YY}-{NNN}</strong> (e.g.,
                    <strong>CBC-23-001</strong>).
                </span>

                <text-input
                    label="PhilRice ID"
                    required
                    v-model="form.employee_id"
                    :error="form.errors.employee_id"
                />
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

<style scoped></style>
