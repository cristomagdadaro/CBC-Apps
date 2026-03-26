<script>
import { router } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import User from "@/Modules/domain/User";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";

export default {
  name: "UserForm",
  components: { AuditInfoCard },
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
      return this.isEdit
        ? "Update account details, role assignments, and direct permissions."
        : "Create a new user account and define how it can access the system.";
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
          tone: this.form?.is_admin
            ? "bg-amber-100 text-amber-800 border-amber-200"
            : "bg-slate-100 text-slate-700 border-slate-200",
        },
        {
          label: `${this.selectedRoleCount} role${
            this.selectedRoleCount === 1 ? "" : "s"
          } selected`,
          tone: "bg-emerald-100 text-emerald-800 border-emerald-200",
        },
        {
          label: `${this.selectedPermissionCount} permission${
            this.selectedPermissionCount === 1 ? "" : "s"
          } selected`,
          tone: "bg-blue-100 text-blue-800 border-blue-200",
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
      this.form.permissions = Array.isArray(this.form.permissions)
        ? this.form.permissions
        : [];
      this.form.is_admin = Boolean(this.form.is_admin);
    },
    normalizeRoleValues(roles) {
      if (!Array.isArray(roles)) {
        return [];
      }

      return roles
        .map((role) => (typeof role === "string" ? role : role?.name))
        .filter(Boolean);
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
      const response = this.isEdit
        ? await this.submitUpdate()
        : await this.submitCreate();

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
    <form v-if="form" class="]" @submit.prevent="submitProxy">
      <section
        class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
      >
        <div
          class="flex flex-col gap-4 border-b border-slate-200 pb-5 md:flex-row md:items-start md:justify-between"
        >
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
              Access Control
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ formTitle }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
              {{ formDescription }}
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <span
              v-for="chip in statusChips"
              :key="chip.label"
              class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
              :class="chip.tone"
            >
              {{ chip.label }}
            </span>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <text-input
            required
            label="Full Name"
            v-model="form.name"
            :error="form.errors.name"
            guide="Use the display name shown across the system."
          />
          <text-input
            required
            label="Email Address"
            v-model="form.email"
            :error="form.errors.email"
            guide="This becomes the sign-in identifier for the account."
          />
          <text-input
            label="Employee ID"
            v-model="form.employee_id"
            :error="form.errors.employee_id"
            guide="Optional, but recommended for staff accounts."
          />
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="flex items-start gap-3">
              <input
                v-model="form.is_admin"
                type="checkbox"
                class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
              />
              <span>
                <span class="block text-sm font-semibold text-slate-900"
                  >System Administrator</span
                >
                <span class="mt-1 block text-sm leading-5 text-slate-600">
                  Grants elevated administrative context in addition to assigned roles and
                  direct permissions.
                </span>
              </span>
            </label>
          </div>
          <text-input
            :required="!isEdit"
            label="Password"
            type="password"
            v-model="form.password"
            :error="form.errors.password"
            :guide="
              isEdit
                ? 'Leave blank to keep the current password.'
                : 'Must be at least 8 characters.'
            "
          />
          <text-input
            :required="!isEdit"
            label="Confirm Password"
            type="password"
            v-model="form.password_confirmation"
            :error="form.errors.password_confirmation"
          />
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
              <div>
                <h3 class="text-lg font-semibold text-slate-900">Role Assignments</h3>
                <p class="mt-1 text-sm text-slate-600">
                  Roles apply the permission bundles defined in the RBAC configuration.
                </p>
              </div>
              <span
                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800"
              >
                {{ selectedRoleCount }} selected
              </span>
            </div>

            <div class="space-y-3">
              <label
                v-for="role in normalizedRoleOptions"
                :key="role.value"
                class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-emerald-300"
              >
                <input
                  :checked="form.roles.includes(role.value)"
                  type="checkbox"
                  class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                  @change="toggleOption('roles', role.value, $event.target.checked)"
                />
                <span>
                  <span class="block text-sm font-semibold text-slate-900">{{
                    role.label
                  }}</span>
                  <span
                    class="mt-1 block text-xs uppercase tracking-wide text-slate-500"
                    >{{ role.value }}</span
                  >
                </span>
              </label>
            </div>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
              <div>
                <h3 class="text-lg font-semibold text-slate-900">Direct Permissions</h3>
                <p class="mt-1 text-sm text-slate-600">
                  Use direct permissions sparingly for exceptions outside standard roles.
                </p>
              </div>
              <span
                class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"
              >
                {{ selectedPermissionCount }} selected
              </span>
            </div>

            <div class="space-y-4">
              <div
                v-for="group in permissionGroups"
                :key="group.key"
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
              >
                <div class="mb-3 flex items-center justify-between gap-3">
                  <div>
                    <h4 class="text-sm font-semibold text-slate-900">
                      {{ group.label }}
                    </h4>
                    <p class="text-xs uppercase tracking-wide text-slate-500">
                      {{ group.permissions.length }} permission{{
                        group.permissions.length === 1 ? "" : "s"
                      }}
                    </p>
                  </div>
                </div>

                <div class="space-y-2">
                  <label
                    v-for="permission in group.permissions"
                    :key="permission.value"
                    class="flex items-start gap-3 rounded-xl border border-slate-100 px-3 py-2 transition hover:border-blue-200"
                  >
                    <input
                      :checked="form.permissions.includes(permission.value)"
                      type="checkbox"
                      class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                      @change="
                        toggleOption(
                          'permissions',
                          permission.value,
                          $event.target.checked
                        )
                      "
                    />
                    <span>
                      <span class="block text-sm font-medium text-slate-900">{{
                        permission.label
                      }}</span>
                      <span
                        class="mt-1 block text-xs uppercase tracking-wide text-slate-500"
                        >{{ permission.value }}</span
                      >
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
          <Link
            :href="route('system.users.index')"
            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          >
            Cancel
          </Link>
          <button
            v-if="isEdit"
            type="button"
            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            @click="resetToSource"
          >
            Reset
          </button>
          <button
            v-if="isEdit"
            type="button"
            :disabled="processing"
            class="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:opacity-50"
            @click="openDeleteModal"
          >
            Delete
          </button>
          <button
            :disabled="processing"
            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:opacity-50"
          >
            {{
              processing
                ? isEdit
                  ? "Saving Changes..."
                  : "Creating User..."
                : isEdit
                ? "Save Changes"
                : "Create User"
            }}
          </button>
        </div>
      </section>
    </form>

    <delete-confirmation-modal
      :show="confirmDelete"
      :is-processing="processing"
      title="Delete User"
      message="This action cannot be undone. The user account and its direct assignments will be removed from the management list."
      :item-name="form?.name || data?.name || 'Selected user'"
      @confirm="handleDelete"
      @close="confirmDelete = false"
    />
  </div>
</template>
