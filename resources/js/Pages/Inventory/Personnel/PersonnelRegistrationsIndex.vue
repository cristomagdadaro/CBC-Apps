<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import PersonnelRegistration from "@/Modules/domain/PersonnelRegistration";

const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "approved", label: "Approved" },
    { value: "rejected", label: "Rejected" },
    { value: "all", label: "All" },
];

export default {
    name: "PersonnelRegistrationsIndex",
    mixins: [ApiMixin],
    data() {
        return {
            registrations: [],
            selectedStatus: "pending",
            search: "",
            rejectingId: null,
            rejectionRemarks: "",
            statusOptions,
        };
    },
    beforeMount() {
        this.model = new PersonnelRegistration();
        this.setFormAction("get");
    },
    mounted() {
        this.fetchRegistrations();
    },
    computed: {
        filteredRegistrations() {
            return this.registrations;
        },
        pendingCount() {
            return this.registrations.filter((registration) => registration.status === "pending").length;
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
        requestPayload() {
            const hasStatusFilter = this.selectedStatus !== "all";
            const hasSearch = this.search.trim() !== "";

            return {
                ...this.form.data(),
                search: hasSearch ? this.search.trim() : hasStatusFilter ? this.selectedStatus : null,
                filter: hasSearch ? null : hasStatusFilter ? "status" : null,
                is_exact: !hasSearch && hasStatusFilter,
                per_page: 50,
                sort: "created_at",
                order: "desc",
            };
        },
        extractRows(payload) {
            const root = payload?.data ?? payload;
            const rows = root?.data ?? root;

            if (Array.isArray(rows)) {
                return rows;
            }

            if (Array.isArray(rows?.data)) {
                return rows.data;
            }

            return [];
        },
        async fetchRegistrations() {
            this.processing = true;

            try {
                const payload = await this.model.api.getIndex(this.requestPayload(), PersonnelRegistration);
                this.registrations = this.extractRows(payload);
            } finally {
                this.processing = false;
            }
        },
        async approveRegistration(registration) {
            if (!registration.is_email_verified || this.processing) {
                return;
            }

            const response = await this.fetchPutApi(
                PersonnelRegistration.endpoints.status,
                registration.id,
                { status: "approved" },
            );

            this.replaceRegistration(response?.data?.data);
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

            const response = await this.fetchPutApi(
                PersonnelRegistration.endpoints.status,
                registration.id,
                {
                    status: "rejected",
                    rejection_remarks: this.rejectionRemarks.trim(),
                },
            );

            this.replaceRegistration(response?.data?.data);
            this.cancelReject();
        },
        replaceRegistration(registration) {
            if (!registration) {
                this.fetchRegistrations();
                return;
            }

            this.registrations = this.registrations.map((row) => (
                row.id === registration.id ? new PersonnelRegistration(registration) : row
            ));
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
                <Link :href="route('personnels.index')" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm">
                    Registered Personnel
                </Link>
            </ActionHeaderLayout>
        </template>

        <section class="mx-auto flex max-w-7xl flex-col gap-4 p-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-AB">Approval queue</p>
                        <h2 class="text-xl font-black text-gray-900">{{ pendingCount }} pending visible registration{{ pendingCount === 1 ? "" : "s" }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select v-model="selectedStatus" @change="fetchRegistrations" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <input
                            v-model="search"
                            @keyup.enter="fetchRegistrations"
                            type="search"
                            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB"
                            placeholder="Search name, email, ID"
                            autocomplete="off"
                        />
                        <button type="button" class="rounded-lg bg-AB px-4 py-2 text-sm font-semibold text-white shadow" @click="fetchRegistrations">
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="processing" class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-500 shadow-sm">
                Loading registrations...
            </div>

            <div v-else-if="filteredRegistrations.length === 0" class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                No personnel registrations found.
            </div>

            <div v-else class="grid gap-4 lg:grid-cols-2">
                <article
                    v-for="registration in filteredRegistrations"
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
        </section>
    </AppLayout>
</template>
