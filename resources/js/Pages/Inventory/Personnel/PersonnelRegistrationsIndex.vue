<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import PersonnelRegistration from "@/Modules/domain/PersonnelRegistration";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";

export default {
    name: "PersonnelRegistrationsIndex",
    components: {
        CreatePersonnelLink,
        IncommingTransactionLink,
        OutgoingTransactionLink,
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
        statusClass(status) {
            return {
                approved: "border-emerald-200 bg-emerald-50 text-emerald-700",
                rejected: "border-rose-200 bg-rose-50 text-rose-700",
                pending: "border-amber-200 bg-amber-50 text-amber-700",
            }[status] ?? "border-slate-200 bg-slate-50 text-slate-700";
        },
        verifiedClass(registration) {
            return registration.is_email_verified
                ? "border-emerald-200 bg-emerald-50 text-emerald-700"
                : "border-slate-200 bg-slate-50 text-slate-600";
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
        async approveRegistration(registration) {
            if (!registration.is_email_verified || this.processing) {
                return;
            }

            await this.fetchPutApi(
                PersonnelRegistration.endpoints.status,
                registration.id,
                { status: "approved" },
            );

            await this.searchRegistrations();
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
                <IncommingTransactionLink />
                <OutgoingTransactionLink />
            </ActionHeaderLayout>
        </template>

        <div class="default-container pt-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2">
                    <p class="text-xs font-bold uppercase tracking-widest text-AB">Approval queue</p>
                    <h2 class="text-xl font-black text-gray-900">{{ pendingCount }} pending visible registration{{ pendingCount === 1 ? "" : "s" }}</h2>
                </div>
            </div>

            <form v-if="!!form" class="mt-4 flex gap-2 items-end" @submit.prevent="searchRegistrations">
                <div class="grid grid-rows-2 w-full">
                    <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                        <div class="flex flex-col gap-0.5">
                            <div class="text-xs text-gray-500 flex items-center justify-between">
                                <span class="flex gap-0.5 whitespace-nowrap">Filter by Status</span>
                            </div>
                            <custom-dropdown
                                :with-all-option="false"
                                :show-clear="true"
                                :value="statusFilterValue"
                                @selectedChange="fetchDataFilterStatus($event)"
                                placeholder="Select a Status"
                                :options="statusOptions"
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
            </form>

            <div class="mt-3 bg-white overflow-hidden sm:rounded-lg">
                <div v-if="registrationRows.length && !processing" class="grid gap-4 p-1 lg:grid-cols-2">
                    <article
                        v-for="registration in registrationRows"
                        :key="registration.id"
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-black text-gray-900">{{ registration.full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ registration.position || "No position supplied" }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ registration.email }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="rounded-full border px-3 py-1 text-xs font-bold uppercase" :class="statusClass(registration.status)">
                                    {{ registration.status }}
                                </span>
                                <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="verifiedClass(registration)">
                                    {{ registration.is_email_verified ? "Email verified" : "Awaiting email" }}
                                </span>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500">Personnel Type</dt>
                                <dd class="font-semibold text-gray-900">{{ registration.is_philrice_employee ? "PhilRice Employee" : "OJT / Thesis / Outsider" }}</dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500">Employee ID</dt>
                                <dd class="font-semibold text-gray-900">{{ registration.employee_id || "Assigned on approval" }}</dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500">Phone</dt>
                                <dd class="font-semibold text-gray-900">{{ registration.phone || "Not supplied" }}</dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500">Submitted</dt>
                                <dd class="font-semibold text-gray-900">{{ registration.created_at ? new Date(registration.created_at).toLocaleDateString() : "N/A" }}</dd>
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
                                    type="button"
                                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="!registration.is_email_verified || processing"
                                    :title="registration.is_email_verified ? 'Approve registration' : 'Email must be verified first'"
                                    @click="approveRegistration(registration)"
                                >
                                    Approve
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else-if="processing" class="text-center py-3 border border-AB rounded-lg">
                    Searching...
                </div>

                <div v-else-if="registrationsFromApi && registrationsFromApi.total === 0 && hasSearchTerm" class="text-center py-3 border border-AB rounded-lg">
                    Registration does not exist. Try using other filters.
                </div>

                <div v-else class="text-center py-3 border border-AB rounded-lg">
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
    </AppLayout>
</template>
