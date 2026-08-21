<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import PersonnelRegistration from "@/Modules/domain/PersonnelRegistration";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import PrintApprovedIdsLink from "@/Pages/Inventory/Transactions/components/presentation/PrintApprovedIdsLink.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

export default {
    name: "PersonnelRegistrationsIndex",
    components: {
        CreatePersonnelLink,
        IncommingTransactionLink,
        OutgoingTransactionLink,
        PrintApprovedIdsLink,
        ConfirmationModal,
    },
    mixins: [ApiMixin],
    data() {
        return {
            registrationsFromApi: null,
            rejectingId: null,
            rejectionRemarks: "",
            statusFilterValue: "pending",
            statusOptions: [
                { name: "approved", label: "Approved" },
                { name: "pending", label: "Pending" },
                { name: "rejected", label: "Rejected" },
            ],
            showBypassModal: false,
            bypassingRegistration: null,
        };
    },
    beforeMount() {
        this.model = new PersonnelRegistration();
        this.setFormAction("get");
        this.form.per_page = 15;
        this.applyStatusFilter(this.statusFilterValue);
    },
    mounted() {
        this.searchRegistrations();
    },
    computed: {
        registrationRows() {
            return this.registrationsFromApi?.data ?? [];
        },
        pendingCount() {
            return this.registrationRows.filter((registration) => registration.status === "pending").length;
        },
        hasSearchTerm() {
            return Boolean(this.form?.search);
        },
    },
    methods: {
        registrationTypeLabel(registration) {
            return {
                philrice_employee: "PhilRice Employee",
                student: "Student",
                ojt: "OJT",
                thesis: "Thesis",
            }[registration.registration_type] ?? (registration.is_philrice_employee ? "PhilRice Employee" : "External Personnel");
        },
        statusClass(status) {
            return {
                approved: "border-emerald-200 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300",
                rejected: "border-rose-200 dark:border-rose-800/80 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300",
                pending: "border-amber-200 dark:border-amber-800/80 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300",
            }[status] ?? "border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-700 dark:text-slate-300";
        },
        verifiedClass(registration) {
            return registration.is_email_verified
                ? "border-emerald-200 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300"
                : "border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-600 dark:text-slate-400";
        },
        applyStatusFilter(status) {
            if (status) {
                this.form.search = status;
                this.form.filter = "status";
                this.form.is_exact = true;
                return;
            }

            if (this.form.filter === "status") {
                this.form.search = null;
                this.form.filter = null;
                this.form.is_exact = false;
            }
        },
        async searchRegistrations() {
            this.registrationsFromApi = await this.fetchData();
        },
        async fetchDataFilterStatus(filterVal) {
            this.statusFilterValue = filterVal;
            this.form.page = 1;
            this.applyStatusFilter(filterVal);
            await this.searchRegistrations();
        },
        async approveRegistration(registration, force = false) {
            if (this.processing) {
                return;
            }

            if (!registration.is_email_verified) {
                if (!force) {
                    this.bypassingRegistration = registration;
                    this.showBypassModal = true;
                    return;
                }
            }

            try {
                await this.fetchPutApi(
                    PersonnelRegistration.endpoints.status,
                    registration.id,
                    {
                        status: "approved",
                        bypass_email_verification: force,
                    },
                );

                await this.searchRegistrations();
            } catch (error) {
                if (error.response?.status === 422) {
                    const errors = error.response.data?.errors;
                    const message = errors ? Object.values(errors).flat().join('\n') : error.response.data?.message || 'Validation failed';
                    this.$notify({
                        title: "Approval Failed",
                        text: message,
                        type: "error",
                    });
                } else {
                    throw error;
                }
            }
        },
        executeBypass() {
            if (!this.bypassingRegistration) return;
            const registration = this.bypassingRegistration;
            this.showBypassModal = false;
            this.bypassingRegistration = null;
            this.approveRegistration(registration, true);
        },
        cancelBypass() {
            this.showBypassModal = false;
            this.bypassingRegistration = null;
        },
        beginReject(registration) {
            this.rejectingId = registration.id;
            this.rejectionRemarks = registration.rejection_remarks ?? "";
        },
        cancelReject() {
            this.rejectingId = null;
            this.rejectionRemarks = "";
        },
        async rejectRegistration(registration) {
            if (!this.rejectionRemarks.trim() || this.processing) {
                return;
            }

            await this.fetchPutApi(
                PersonnelRegistration.endpoints.status,
                registration.id,
                {
                    status: "rejected",
                    rejection_remarks: this.rejectionRemarks.trim(),
                },
            );

            this.cancelReject();
            await this.searchRegistrations();
        },
    },
    watch: {
        "form.search": {
            handler(newVal) {
                if (!newVal) {
                    this.form.filter = null;
                    this.form.is_exact = false;
                    this.statusFilterValue = null;
                }
            },
        },
        "form.filter": {
            handler(newVal) {
                if (newVal !== "status") {
                    this.statusFilterValue = null;
                }
            },
        },
    },
};
</script>

<template>
    <Head title="Personnel Registration Approvals" />

    <AppLayout>
        <template #header>
            <ActionHeaderLayout
                title="Personnel Registration Approvals"
                subtitle="Review guest-submitted personnel records after email verification."
                :route-link="route('personnels.index')"
            >
                <CreatePersonnelLink />
                <PrintApprovedIdsLink />
                <IncommingTransactionLink />
                <OutgoingTransactionLink />
            </ActionHeaderLayout>
        </template>

        <div class="default-container pt-5">
            <form v-if="!!form" class="mt-4 flex gap-2 items-end" @submit.prevent="searchRegistrations">
                <div class="grid grid-rows-2 w-full">
                    <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                        <div class="flex flex-col gap-0.5">
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between font-medium">
                                <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                            </div>
                            <custom-dropdown
                                :with-all-option="false"
                                :show-clear="true"
                                :value="statusFilterValue"
                                @selectedChange="fetchDataFilterStatus($event)"
                                placeholder="Select a Status"
                                :options="statusOptions"
                                :show-valid-indicator="false"
                            >
                                <template #icon>
                                    <filter-icon class="h-4 w-4" />
                                </template>
                            </custom-dropdown>
                        </div>
                        <search-by
                            :value="form.filter"
                            :is-exact="form.is_exact"
                            :options="model.constructor.getFilterColumns()"
                            @isExact="form.is_exact = $event"
                            @searchBy="form.filter = $event"
                        />
                        <text-input placeholder="Search..." v-model="form.search" />
                        <search-btn type="submit" :disabled="processing" class="w-[10rem] text-center">
                            <span v-if="!processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                    <div v-if="registrationsFromApi" class="flex w-full gap-2 items-center">
                        <div class="flex gap-1 items-center w-full justify-center">
                            <paginate-btn @click="form.page = 1; searchRegistrations();" :disabled="form.page === 1">First</paginate-btn>
                            <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchRegistrations();" :disabled="form.page === 1">
                                <template #icon>
                                    <arrow-left class="h-auto w-6" />
                                </template>
                                Prev
                            </paginate-btn>
                            <div class="text-xs flex flex-col whitespace-nowrap text-center text-slate-600 dark:text-slate-400 font-semibold">
                                <span class="font-medium mx-1" title="current page and total pages">
                                    <span>{{ registrationsFromApi?.current_page }}</span> / <span>{{ registrationsFromApi?.last_page }}</span>
                                </span>
                            </div>
                            <paginate-btn
                                @click="form.page = Math.min(registrationsFromApi?.last_page, form.page + 1); searchRegistrations();"
                                :disabled="form.page === registrationsFromApi?.last_page"
                            >
                                Next
                                <template #icon>
                                    <arrow-right class="h-auto w-6" />
                                </template>
                            </paginate-btn>
                            <paginate-btn
                                @click="form.page = registrationsFromApi?.last_page; searchRegistrations();"
                                :disabled="form.page === registrationsFromApi?.last_page"
                            >
                                Last
                            </paginate-btn>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-3 overflow-hidden rounded-2xl">
               <div v-if="registrationRows.length && !processing" class="grid gap-4 p-1 lg:grid-cols-2">
                    <article v-for="registration in registrationRows" :key="registration.id" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">{{ registration.full_name }}</h3>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">{{ registration.position || "No position supplied" }}</p>
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 font-mono">{{ registration.email }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="rounded-full border px-3 py-1 text-[11px] font-bold uppercase" :class="statusClass(registration.status)">
                                    {{ registration.status }}
                                </span>
                                <span class="rounded-full border px-3 py-1 text-[11px] font-semibold" :class="verifiedClass(registration)">
                                    {{ registration.is_email_verified ? "Email verified" : "Awaiting email" }}
                                </span>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-100 dark:border-slate-800/80">
                                <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Personnel Type</dt>
                                <dd class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">{{ registrationTypeLabel(registration) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-100 dark:border-slate-800/80">
                                <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Employee ID</dt>
                                <dd class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5 font-mono">{{ registration.employee_id || "Assigned on approval" }}</dd>
                            </div>
                            <div v-if="registration.requires_cbc_id_card" class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-100 dark:border-slate-800/80">
                                <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Course / Program</dt>
                                <dd class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">{{ registration.course_program || "Not supplied" }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-100 dark:border-slate-800/80">
                                <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Phone</dt>
                                <dd class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">{{ registration.phone || "Not supplied" }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-100 dark:border-slate-800/80">
                                <dt class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Submitted</dt>
                                <dd class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">{{ registration.created_at ? registration.created_at : "N/A" }}</dd>
                            </div>
                        </dl>

                        <p v-if="registration.rejection_remarks" class="mt-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">
                            {{ registration.rejection_remarks }}
                        </p>

                        <div v-if="registration.status === 'pending'" class="mt-5 flex flex-col gap-2">
                            <div v-if="rejectingId === registration.id" class="space-y-2">
                                <textarea
                                    v-model="rejectionRemarks"
                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB"
                                    rows="3"
                                    placeholder="Reason for rejection"
                                ></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="rounded-lg border px-3 py-2 text-sm font-semibold text-gray-700" @click="cancelReject">
                                        Cancel
                                    </button>
                                    <button type="button" class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white disabled:opacity-50" :disabled="!rejectionRemarks.trim() || processing" @click="rejectRegistration(registration)">
                                        Confirm Reject
                                    </button>
                                </div>
                            </div>
                            <div v-else class="flex justify-end gap-2">
                                <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700" @click="beginReject(registration)">
                                    Reject
                                </button>
                                <button
                                    v-if="registration.is_email_verified"
                                    type="button"
                                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="processing"
                                    title="Approve registration"
                                    @click="approveRegistration(registration)"
                                >
                                    Approve
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="processing"
                                    title="Bypass email verification and approve"
                                    @click="approveRegistration(registration)"
                                >
                                    Force Approve
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else-if="processing" class="text-center py-8 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl text-slate-500 dark:text-slate-400 text-xs sm:text-sm font-semibold shadow-xs">
                    Searching...
                </div>

                <div v-else-if="registrationsFromApi && registrationsFromApi.total === 0 && hasSearchTerm" class="text-center py-8 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl text-slate-500 dark:text-slate-400 text-xs sm:text-sm font-semibold shadow-xs">
                    Registration does not exist. Try using other filters.
                </div>

                <div v-else class="text-center py-8 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-2xl text-slate-500 dark:text-slate-400 text-xs sm:text-sm font-semibold shadow-xs">
                    No personnel registrations available.
                </div>
            </div>

            <div v-if="registrationsFromApi && registrationsFromApi.data?.length" class="flex w-full gap-2 py-5 items-center">
                <div class="flex gap-1 items-center w-full justify-center">
                    <paginate-btn @click="form.page = 1; searchRegistrations();" :disabled="form.page === 1">First</paginate-btn>
                    <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchRegistrations();" :disabled="form.page === 1">
                        <template #icon>
                            <arrow-left class="h-auto w-6" />
                        </template>
                        Prev
                    </paginate-btn>
                    <div class="text-xs flex flex-col whitespace-nowrap text-center">
                        <span class="font-medium mx-1" title="current page and total pages">
                            <span>{{ registrationsFromApi?.current_page }}</span> / <span>{{ registrationsFromApi?.last_page }}</span>
                        </span>
                    </div>
                    <paginate-btn
                        @click="form.page = Math.min(registrationsFromApi?.last_page, form.page + 1); searchRegistrations();"
                        :disabled="form.page === registrationsFromApi?.last_page"
                    >
                        Next
                        <template #icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>
                    <paginate-btn
                        @click="form.page = registrationsFromApi?.last_page; searchRegistrations();"
                        :disabled="form.page === registrationsFromApi?.last_page"
                    >
                        Last
                    </paginate-btn>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showBypassModal" @close="cancelBypass">
            <template #title>
                Bypass Email Verification?
            </template>
            <template #content>
                The email (<strong>{{ bypassingRegistration?.email }}</strong>) has not been verified yet.
                <br /><br />
                Are you sure you want to bypass verification and approve this registration?
            </template>
            <template #footer>
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 mr-2"
                    @click="cancelBypass"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="processing"
                    @click="executeBypass"
                >
                    Yes, Bypass and Approve
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
