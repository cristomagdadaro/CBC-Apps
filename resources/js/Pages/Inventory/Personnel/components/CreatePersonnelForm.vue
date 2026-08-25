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
            class="py-6 sm:py-10 max-w-3xl mx-auto px-4">
            <div class="flex flex-col gap-4 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs rounded-2xl p-4 sm:p-6 text-slate-900 dark:text-slate-100">
                <div class="flex flex-col pb-3 border-b border-slate-200 dark:border-slate-800">
                    <h2 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-900 dark:text-slate-100">Personnel Registration Form</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Use this form to register new personnel into the system.</p>
                </div>
                <div class="flex sm:flex-row flex-col gap-2">
                    <text-input
                        required
                        label="First Name"
                        v-model="form.fname"
                        :error="form.errors.fname" />
                    <text-input
                        label="Middle Name"
                        v-model="form.mname"
                        :error="form.errors.mname" />
                    <text-input
                        required
                        label="Last Name"
                        v-model="form.lname"
                        :error="form.errors.lname" />
                    <text-input
                        label="Suffix"
                        v-model="form.suffix"
                        :error="form.errors.suffix" />
                </div>
                <div class="flex flex-col gap-3">
                    <div class="space-y-1">
                        <label class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Personnel Type</label>
                        <select
                            v-model="form.is_philrice_employee"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm shadow-xs focus:border-lime-500 focus:ring-lime-500">
                            <option :value="true">PhilRice Employee</option>
                            <option :value="false">OJT / Thesis / Outsider</option>
                        </select>
                    </div>
                    <text-input
                        required
                        label="Position"
                        v-model="form.position"
                        :error="form.errors.position" />
                    <text-input
                        label="Phone"
                        v-model="form.phone"
                        :error="form.errors.phone" />
                    <text-input
                        label="Email (optional)"
                        v-model="form.email"
                        :error="form.errors.email" />
                </div>
                <text-input
                    label="Address"
                    v-model="form.address"
                    :error="form.errors.address" />
                <div
                    v-if="isPhilRiceEmployee"
                    class="space-y-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Enter the employee's PhilRice ID.</span>
                    <text-input
                        label="PhilRice ID"
                        required
                        v-model="form.employee_id"
                        :error="form.errors.employee_id" />
                </div>
                <div
                    v-else
                    class="space-y-3">
                    <div class="rounded-xl border border-lime-200 dark:border-lime-900/60 bg-lime-50/70 dark:bg-lime-950/40 p-4 text-xs sm:text-sm text-lime-900 dark:text-lime-200">
                        <p class="font-bold">Auto-generated CBC ID</p>
                        <p class="mt-1 text-slate-600 dark:text-slate-300 text-xs">The next outsider/OJT/thesis identifier will be assigned automatically on save.</p>
                        <p class="mt-2 font-mono text-sm font-bold text-lime-700 dark:text-lime-300">
                            {{ externalEmployeeIdPreview }}
                        </p>
                    </div>
                    <text-input
                        required
                        label="Affiliation / School / Agency"
                        v-model="form.affiliation"
                        :error="form.errors.affiliation" />
                </div>
                <div class="space-y-1">
                    <text-input
                        label="Expires At (optional)"
                        type="date"
                        v-model="form.expires_at"
                        :error="form.errors.expires_at" />
                    <p class="text-xs text-slate-500 dark:text-slate-400">Set an expiry date for temporary personnel (OJT, Student, Thesis). Status will auto-set to Suspended after this date.</p>
                </div>
                <div class="flex gap-2 justify-end pt-2">
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
