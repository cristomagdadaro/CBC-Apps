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
            default: ''
        },
        required: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue', 'found', 'error'],
    data() {
        return {
            clientErrors: {}
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
            this.$emit('update:modelValue', value);
        },

        buildFoundPayload(record) {
            return {
                employee_id: this.modelValue,
                fullName: record.fullName,
                fname: record.fname,
                mname: record.mname,
                lname: record.lname,
                suffix: record.suffix,
                position: record.position,
                phone: record.phone ?? null,
                address: record.address ?? null,
                email: record.email ?? null,
                has_email: !!record.has_email,
                profile_requires_update: !!record.profile_requires_update,
                affiliation: "Philippine Rice Research Institute",
            };
        },

        async searchPersonnel() {
            this.clientErrors = { ...this.clientErrors, employee_id: null };

            if (!this.modelValue) {
                this.clientErrors.employee_id = 'PhilRice ID is required';
                this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });
                return;
            }

            try {
                const response = await this.fetchGetApi(
                    'api.inventory.personnels.index.guest',
                    {
                        filter: 'employee_id',
                        search: this.modelValue,
                        is_exact: true,
                    },
                    Personnel
                );
                
                let payload = response?.data ?? response ?? [];

                const record = Array.isArray(payload?.data ?? payload)
                    ? (payload.data ?? payload)[0]
                    : (payload.data ?? payload);

                if (!record) {
                    this.clientErrors.employee_id = 'No personnel found for this ID';
                    this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });
                    return null;
                }

                delete this.clientErrors.employee_id;

                payload = this.buildFoundPayload(record);
                this.$emit('found', payload);
                return payload;

            } catch (error) {
                console.error(error);
                this.clientErrors.employee_id = 'Lookup failed. Please try again.';
                this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });
                return null;

            }
        }
    }
};
</script>

<template>
    <div v-if="currentLaboratoryPersonnel" key="saved" class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-200">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-emerald-100">
                <LuUser class="w-4 h-4 text-emerald-600" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">{{
                    currentLaboratoryPersonnel.fullName }}</p>
                <p class="text-xs text-gray-500">{{ currentLaboratoryPersonnel.employee_id }}
                </p>
            </div>
        </div>
        <button type="button" @click="handlePersonnelSwitch"
            class="p-2 text-gray-500 transition-colors rounded-lg hover:bg-gray-200"
            :class="{ 'animate-spin': processing }">
            <LuRefreshCw class="w-4 h-4" />
        </button>
    </div>
    <div v-else class="flex flex-col gap-2 w-full">
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
                autocomplete="employee_id"
                @update:modelValue="onInput"
                @keydown.enter.prevent="searchPersonnel"
                @input="delete clientErrors.employee_id"
            />
            <button id="personnel-lookip-btn" type="button" class="px-3 py-[0.66rem] rounded bg-AB text-white text-sm hover:bg-AB-dark disabled:opacity-50" :disabled="processing" @click="searchPersonnel">
                <search-icon v-if="!processing" class="w-5 h-5" />
                <loader-icon v-else class="w-5 h-5 animate-spin" />
            </button>
        </div>
    </div>
</template>
