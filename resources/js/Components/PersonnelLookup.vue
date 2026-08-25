<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import LaboratoryPersonnelMixin from "@/Modules/mixins/LaboratoryPersonnelMixin";

export default {
    name: "PersonnelLookup",
    mixins: [ApiMixin, LaboratoryPersonnelMixin],
    props: {
        modelValue: {
            type: String,
            default: "",
        },
        required: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["update:modelValue", "found", "error"],
    data() {
        return {
            clientErrors: {},
        };
    },
    computed: {
        authenticatedPersonnel() {
            return this.$currentUser;
        },
        currentLaboratoryPersonnel() {
            return this.savedLaboratoryPersonnel || this.authenticatedPersonnel;
        },
    },
    methods: {
        onInput(value) {
            delete this.clientErrors.employee_id;
            this.$emit("update:modelValue", value);
        },

        buildFoundPayload(record) {
            return {
                employee_id: this.modelValue || record.employee_id,
                fullName: record.fullName || record.name,
                fname: record.fname,
                mname: record.mname,
                lname: record.lname,
                suffix: record.suffix,
                position: record.position,
                phone: record.phone ?? null,
                address: record.address ?? null,
                email: record.email ?? null,
                affiliation: record.affiliation ?? null,
                has_email: !!record.has_email,
                profile_requires_update: !!record.profile_requires_update,
            };
        },

        async searchPersonnel() {
            this.clientErrors = { ...this.clientErrors, employee_id: null };

            if (!this.modelValue) {
                this.clientErrors.employee_id = "PhilRice ID is required";
                this.$emit("error", {
                    field: "employee_id",
                    message: this.clientErrors.employee_id,
                });
                return;
            }

            try {
                const response = await this.fetchGetApi(
                    "api.inventory.personnels.index.guest",
                    {
                        filter: "employee_id",
                        search: this.modelValue,
                        is_exact: true,
                    },
                    Personnel,
                );

                let payload = response?.data ?? response ?? [];

                const record = Array.isArray(payload?.data ?? payload) ? (payload.data ?? payload)[0] : (payload.data ?? payload);

                if (!record) {
                    this.clientErrors.employee_id = "No personnel found for this ID";
                    this.$emit("error", {
                        field: "employee_id",
                        message: this.clientErrors.employee_id,
                    });
                    return null;
                }

                delete this.clientErrors.employee_id;

                payload = this.buildFoundPayload(record);
                this.$emit("found", payload);
                return payload;
            } catch (error) {
                console.error(error);
                this.clientErrors.employee_id = error.response?.data?.message || "Lookup failed. Please try again.";
                this.$emit("error", {
                    field: "employee_id",
                    message: this.clientErrors.employee_id,
                });
                return null;
            }
        },
    },
    mounted() {
        if (this.currentLaboratoryPersonnel) {
            this.$emit("found", this.buildFoundPayload(this.currentLaboratoryPersonnel));
            if (this.currentLaboratoryPersonnel.employee_id && !this.modelValue) {
                this.$emit("update:modelValue", this.currentLaboratoryPersonnel.employee_id);
            }
        }
    },
    watch: {
        currentLaboratoryPersonnel(newVal) {
            if (newVal) {
                this.$emit("found", this.buildFoundPayload(newVal));
                if (newVal.employee_id && !this.modelValue) {
                    this.$emit("update:modelValue", newVal.employee_id);
                }
            }
        },
    },
};
</script>

<template>
    <div
        v-if="currentLaboratoryPersonnel"
        key="saved"
        class="flex items-center justify-between rounded-xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/80">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-900/30">
                <LuUser class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ currentLaboratoryPersonnel.fullName || currentLaboratoryPersonnel.name }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ currentLaboratoryPersonnel.employee_id }}
                </p>
            </div>
        </div>
        <button
            type="button"
            @click="handlePersonnelSwitch"
            class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            :class="{ 'animate-spin': processing }">
            <LuRefreshCw class="h-4 w-4" />
        </button>
    </div>
    <div
        v-else
        class="flex w-full flex-col gap-2">
        <div class="flex items-end gap-2">
            <TextInput
                id="employee_id"
                :modelValue="modelValue"
                type="text"
                :error="clientErrors.employee_id"
                :label="required ? 'PhilRice ID or CBC ID' : 'PhilRice ID (optional)'"
                :required="required"
                placeholder="**-****"
                name="employee_id"
                autocomplete="username"
                @update:modelValue="onInput"
                @keydown.enter.prevent="searchPersonnel"
                @input="delete clientErrors.employee_id" />
            <button
                id="personnel-lookip-btn"
                type="button"
                class="hover:bg-AB-dark rounded bg-AB px-3 py-[0.66rem] text-sm text-white transition-colors disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-700"
                :disabled="processing"
                @click="searchPersonnel">
                <search-icon
                    v-if="!processing"
                    class="h-5 w-5" />
                <loader-icon
                    v-else
                    class="h-5 w-5 animate-spin" />
            </button>
        </div>
    </div>
</template>
