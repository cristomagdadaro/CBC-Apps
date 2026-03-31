<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";

export default {
    name: "CreatePersonnelForm",
    components: { PersonnelHeaderActions, FormsHeaderActions },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Personnel();
        this.setFormAction("create");
        this.form.is_philrice_employee = true;
    },
    computed: {
        isPhilRiceEmployee() {
            return this.form?.is_philrice_employee !== false;
        },
        externalEmployeeIdPreview() {
            return this.$page.props.nextExternalEmployeeId || "CBC-YY-0001";
        },
    },
};
</script>

<template>
    <AppLayout title="Register a New Personnel">
        <template #header>
            <personnel-header-actions />
        </template>

        <form
            v-if="!!form"
            @submit.prevent="submitCreate"
            class="py-12 max-w-3xl mx-auto"
        >
            <div
                class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
            >
                <div class="flex flex-col">
                    <h2
                        class="font-bold uppercase leading-none py-2 mb-1 border-b"
                    >
                        Personnel Registration Form
                    </h2>
                    <p>Use this form to register new personnel.</p>
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
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">
                            Personnel Type
                        </label>
                        <select
                            v-model="form.is_philrice_employee"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB"
                        >
                            <option :value="true">PhilRice Employee</option>
                            <option :value="false">OJT / Thesis / Outsider</option>
                        </select>
                    </div>
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
                <div v-if="isPhilRiceEmployee" class="space-y-1">
                    <span class="text-sm text-gray-600">
                        Enter the employee's PhilRice ID.
                    </span>
                    <text-input
                        label="PhilRice ID"
                        required
                        v-model="form.employee_id"
                        :error="form.errors.employee_id"
                    />
                </div>
                <div v-else class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                    <p class="font-semibold">Auto-generated CBC ID</p>
                    <p class="mt-1">
                        The next outsider/OJT/thesis identifier will be assigned automatically on save.
                    </p>
                    <p class="mt-2 font-mono text-base">{{ externalEmployeeIdPreview }}</p>
                </div>
                <div class="flex gap-1 justify-end">
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Saving</span>
                        <span v-else>Save</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped></style>
