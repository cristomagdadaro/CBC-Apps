<script>
import { router } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import User from "@/Modules/domain/User";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import { UserCog, ShieldCheck, Key, Save, X, RotateCcw, Trash2, Loader2, CheckCircle2, ShieldAlert, UserCircle } from "lucide-vue-next";

export default {
    name: "UserForm",
    components: {
        AuditInfoCard,
        UserCog,
        ShieldCheck,
        Key,
        Save,
        X,
        RotateCcw,
        Trash2,
        Loader2,
        CheckCircle2,
        ShieldAlert,
        UserCircle,
    },
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
        roleOptions: {
            type: Array,
            default: () => [],
        },
        permissionOptions: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            confirmDelete: false,
        };
    },
    computed: {
        isEdit() {
            return !!this.data;
        },
        formTitle() {
            return this.isEdit ? "User Access Update Form" : "User Access Registration Form";
        },
        formDescription() {
            return this.isEdit ? "Update account details, role assignments, and direct permissions." : "Create a new user account and define how it can access the system.";
        },
        normalizedRoleOptions() {
            return this.roleOptions.map((role) => ({
                value: role,
                label: this.formatLabel(role),
            }));
        },
        permissionGroups() {
            const grouped = this.permissionOptions.reduce((carry, permission) => {
                const [group] = String(permission).split(".");
                const key = group || "general";

                if (!carry[key]) {
                    carry[key] = [];
                }

                carry[key].push({
                    value: permission,
                    label: this.formatLabel(permission),
                });

                return carry;
            }, {});

            return Object.entries(grouped).map(([group, permissions]) => ({
                key: group,
                label: this.formatLabel(group),
                permissions,
            }));
        },
        selectedRoleCount() {
            return Array.isArray(this.form?.roles) ? this.form.roles.length : 0;
        },
        selectedPermissionCount() {
            return Array.isArray(this.form?.permissions) ? this.form.permissions.length : 0;
        },
        statusChips() {
            return [
                {
                    label: this.form?.is_admin ? "System Administrator" : "Standard Account",
                    tone: this.form?.is_admin ? "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30" : "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-700/50",
                },
                {
                    label: `${this.selectedRoleCount} role${this.selectedRoleCount === 1 ? "" : "s"}`,
                    tone: "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30",
                },
                {
                    label: `${this.selectedPermissionCount} permission${this.selectedPermissionCount === 1 ? "" : "s"}`,
                    tone: "bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30",
                },
            ];
        },
    },
    beforeMount() {
        this.model = new User(this.data ?? {});
        this.setFormAction(this.isEdit ? "update" : "create");
        this.hydrateRoleAssignments();
    },
    methods: {
        formatLabel(value) {
            if (!value) return "";

            return String(value)
                .replace(/[._]/g, " ")
                .split(" ")
                .filter(Boolean)
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(" ");
        },
        hydrateRoleAssignments() {
            if (!this.form) {
                return;
            }

            this.form.roles = this.normalizeRoleValues(this.data?.roles);
            this.form.permissions = Array.isArray(this.form.permissions) ? this.form.permissions : [];
            this.form.is_admin = Boolean(this.form.is_admin);
            this.form.is_active = this.form.is_active !== undefined ? Boolean(this.form.is_active) : true;
        },
        normalizeRoleValues(roles) {
            if (!Array.isArray(roles)) {
                return [];
            }

            return roles.map((role) => (typeof role === "string" ? role : role?.name)).filter(Boolean);
        },
        buildResetPayload() {
            if (!this.isEdit) {
                return this.model.createFields();
            }

            return this.model.updateFields({
                ...(this.data || {}),
                roles: this.normalizeRoleValues(this.data?.roles),
                permissions: Array.isArray(this.data?.permissions) ? this.data.permissions : [],
            });
        },
        resetToSource() {
            this.resetField(this.buildResetPayload());
            this.hydrateRoleAssignments();
        },
        async submitProxy() {
            const response = this.isEdit ? await this.submitUpdate() : await this.submitCreate();

            if (response instanceof DtoResponse) {
                router.visit(route("system.users.index"));
            }
        },
        openDeleteModal() {
            this.confirmDelete = true;
        },
        async handleDelete() {
            this.toDelete = { id: this.data?.id };
            const response = await this.submitDelete();

            if (response instanceof DtoResponse) {
                this.confirmDelete = false;
                router.visit(route("system.users.index"));
            }
        },
    },
};
</script>

<template>
    <div>
        <form
            v-if="form"
            @submit.prevent="submitProxy"
            class="space-y-8 rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-slate-800 dark:bg-slate-900/80">
            <!-- Header Section -->
            <div class="flex flex-col gap-5 border-b border-slate-100 pb-6 md:flex-row md:items-start md:justify-between dark:border-slate-800/60">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 rounded-xl border border-indigo-100 bg-indigo-50 p-3 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <UserCog class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Access Control</p>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ formTitle }}
                        </h2>
                        <p class="mt-1.5 max-w-2xl text-sm font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ formDescription }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2 pt-2 md:pt-0">
                    <span
                        v-for="chip in statusChips"
                        :key="chip.label"
                        class="inline-flex items-center rounded-md border px-2.5 py-1 text-[0.6rem] font-bold uppercase tracking-widest shadow-sm"
                        :class="chip.tone">
                        {{ chip.label }}
                    </span>
                </div>
            </div>

            <!-- Details Section -->
            <div class="space-y-4">
                <h3 class="flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <UserCircle class="h-3.5 w-3.5" />
                    Account Details
                </h3>
                <div class="grid gap-6 rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm md:grid-cols-2 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <text-input
                        required
                        label="Full Name"
                        v-model="form.name"
                        :error="form.errors.name"
                        guide="Use the display name shown across the system." />
                    <text-input
                        required
                        label="Email Address"
                        v-model="form.email"
                        :error="form.errors.email"
                        guide="This becomes the sign-in identifier for the account." />
                    <text-input
                        label="Employee ID"
                        v-model="form.employee_id"
                        :error="form.errors.employee_id"
                        guide="Optional, but recommended for staff accounts." />

                    <div class="grid gap-6 space-y-4 md:col-span-2 md:grid-cols-2">
                        <!-- Is Admin Checkbox -->
                        <label
                            class="group relative flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 shadow-sm transition-all duration-300"
                            :class="form.is_admin ? 'border-amber-400 bg-amber-50/50 dark:border-amber-500/50 dark:bg-amber-500/10' : 'border-slate-200/60 bg-white hover:border-amber-300 dark:border-slate-700/60 dark:bg-slate-900/50 dark:hover:border-amber-500/50'">
                            <input
                                v-model="form.is_admin"
                                type="checkbox"
                                class="mt-0.5 cursor-pointer rounded-md border-slate-300 bg-white text-amber-600 focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-800" />
                            <div>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">System Administrator</span>
                                <span class="mt-1 block text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">Grants elevated administrative context in addition to assigned roles and direct permissions.</span>
                            </div>
                        </label>

                        <!-- Is Active Checkbox -->
                        <label
                            class="group relative flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 shadow-sm transition-all duration-300"
                            :class="form.is_active ? 'border-emerald-400 bg-emerald-50/50 dark:border-emerald-500/50 dark:bg-emerald-500/10' : 'border-slate-200/60 bg-white hover:border-emerald-300 dark:border-slate-700/60 dark:bg-slate-900/50 dark:hover:border-emerald-500/50'">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="mt-0.5 cursor-pointer rounded-md border-slate-300 bg-white text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800" />
                            <div>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">Active Account</span>
                                <span class="mt-1 block text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">Allow the user to log into the system. Uncheck this for users who have resigned or left.</span>
                            </div>
                        </label>
                    </div>

                    <text-input
                        :required="!isEdit"
                        label="Password"
                        type="password"
                        v-model="form.password"
                        :error="form.errors.password"
                        :guide="isEdit ? 'Leave blank to keep the current password.' : 'Must be at least 8 characters.'" />
                    <text-input
                        :required="!isEdit"
                        label="Confirm Password"
                        type="password"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- RBAC Section -->
            <div class="grid gap-6 xl:grid-cols-2">
                <!-- Roles -->
                <div class="space-y-4">
                    <h3 class="flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <ShieldCheck class="h-3.5 w-3.5" />
                        Role Assignments
                    </h3>
                    <section class="h-full rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <p class="mb-5 text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">Roles apply the predefined permission bundles from the RBAC configuration.</p>

                        <div class="space-y-3">
                            <label
                                v-for="role in normalizedRoleOptions"
                                :key="role.value"
                                class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 shadow-sm transition-all duration-200"
                                :class="form.roles.includes(role.value) ? 'border-emerald-400 bg-emerald-50/50 dark:border-emerald-500/50 dark:bg-emerald-500/10' : 'border-slate-200/60 bg-white hover:border-emerald-300 dark:border-slate-700/60 dark:bg-slate-900/50 dark:hover:border-emerald-500/50'">
                                <input
                                    :checked="form.roles.includes(role.value)"
                                    type="checkbox"
                                    class="mt-0.5 cursor-pointer rounded-md border-slate-300 bg-white text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800"
                                    @change="toggleOption('roles', role.value, $event.target.checked)" />
                                <div>
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">
                                        {{ role.label }}
                                    </span>
                                    <span class="mt-0.5 block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                        {{ role.value }}
                                    </span>
                                </div>
                            </label>
                        </div>
                    </section>
                </div>

                <!-- Direct Permissions -->
                <div class="space-y-4">
                    <h3 class="flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        <Key class="h-3.5 w-3.5" />
                        Direct Permissions
                    </h3>
                    <section class="h-full rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <div class="mb-5 flex items-start gap-2 text-amber-600 dark:text-amber-400">
                            <ShieldAlert class="mt-0.5 h-4 w-4 shrink-0" />
                            <p class="text-xs font-medium leading-relaxed">Use direct permissions sparingly for exceptions outside standard roles. Overriding roles with direct permissions can complicate access audits.</p>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="group in permissionGroups"
                                :key="group.key"
                                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                                <div class="mb-3.5 flex items-center justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-800/60">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                        {{ group.label }}
                                    </h4>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest text-slate-400 dark:bg-slate-800 dark:text-slate-500">{{ group.permissions.length }} perm{{ group.permissions.length === 1 ? "" : "s" }}</span>
                                </div>

                                <div class="space-y-2.5">
                                    <label
                                        v-for="permission in group.permissions"
                                        :key="permission.value"
                                        class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 transition-colors hover:border-indigo-300 dark:border-slate-700/50 dark:bg-slate-800/30 dark:hover:border-indigo-500/50">
                                        <input
                                            :checked="form.permissions.includes(permission.value)"
                                            type="checkbox"
                                            class="mt-0.5 cursor-pointer rounded-md border-slate-300 bg-white text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                                            @change="toggleOption('permissions', permission.value, $event.target.checked)" />
                                        <div>
                                            <span class="block text-[0.8rem] font-bold text-slate-800 dark:text-slate-200">
                                                {{ permission.label }}
                                            </span>
                                            <span class="mt-0.5 block text-[0.6rem] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                                {{ permission.value }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-6 sm:flex-row dark:border-slate-800/60">
                <Link
                    :href="route('system.users.index')"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-100 active:scale-95 sm:w-auto dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800">
                    <X class="h-4 w-4" />
                    Cancel
                </Link>

                <button
                    v-if="isEdit"
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-100 active:scale-95 sm:w-auto dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800"
                    @click="resetToSource">
                    <RotateCcw class="h-4 w-4" />
                    Reset
                </button>

                <button
                    v-if="isEdit"
                    type="button"
                    :disabled="processing"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-5 py-2.5 text-sm font-bold text-rose-700 shadow-sm transition-all hover:bg-rose-100 active:scale-95 disabled:pointer-events-none disabled:opacity-60 sm:w-auto dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20"
                    @click="openDeleteModal">
                    <Trash2 class="h-4 w-4" />
                    Delete
                </button>

                <button
                    :disabled="processing"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95 disabled:pointer-events-none disabled:opacity-70 sm:w-auto">
                    <Loader2
                        v-if="processing"
                        class="h-4 w-4 animate-spin" />
                    <Save
                        v-else
                        class="h-4 w-4" />
                    {{ processing ? (isEdit ? "Saving..." : "Creating...") : isEdit ? "Save Changes" : "Create User" }}
                </button>
            </div>
        </form>

        <delete-confirmation-modal
            :show="confirmDelete"
            :is-processing="processing"
            title="Delete User"
            message="This action cannot be undone. The user account and its direct assignments will be removed from the management list."
            :item-name="form?.name || data?.name || 'Selected user'"
            @confirm="handleDelete"
            @close="confirmDelete = false" />
    </div>
</template>
