<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";

export default {
    name: "PersonnelLookup",
    mixins: [ApiMixin],
    props: {
        modelValue: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue', 'found', 'error'],
    data() {
        return {
            searchLoading: false,
            clientErrors: {}
        };
    },
    methods: {
        onInput(value) {
            delete this.clientErrors.employee_id;
            this.$emit('update:modelValue', value);
        },

        async searchPersonnel() {
            this.clientErrors = { ...this.clientErrors, employee_id: null };

            if (!this.modelValue) {
                this.clientErrors.employee_id = 'PhilRice ID is required';
                this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });
                return;
            }

            this.searchLoading = true;

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

                const payload = response?.data ?? response ?? [];

                const record = Array.isArray(payload?.data ?? payload)
                    ? (payload.data ?? payload)[0]
                    : (payload.data ?? payload);

                if (!record) {
                    this.clientErrors.employee_id = 'No personnel found for this ID';
                    this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });
                    return;
                }

                delete this.clientErrors.employee_id;

                this.$emit('found', {
                    fullName: record.fullName,
                    position: record.position,
                    phone: record.phone,
                    email: record.email,
                    affiliation: "Philippine Rice Research Institute"
                });

            } catch (error) {
                console.error(error);
                this.clientErrors.employee_id = 'Lookup failed. Please try again.';
                this.$emit('error', { field: 'employee_id', message: this.clientErrors.employee_id });

            } finally {
                this.searchLoading = false;
            }
        }
    }
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <p class="text-sm text-gray-600">Auto-fill by PhilRice ID</p>
        <div class="flex items-end gap-2">
            <TextInput
                id="employee_id"
                :modelValue="modelValue"
                type="text"
                :error="clientErrors.employee_id"
                label="PhilRice ID (optional)"
                placeholder="**-****"
                name="employee_id"
                autocomplete="employee_id"
                @update:modelValue="onInput"
                @keydown.enter.prevent="searchPersonnel"
                @input="delete clientErrors.employee_id"
            />
            <button id="personnel-lookip-btn" type="button" class="px-3 py-[0.66rem] rounded bg-AB text-white text-sm hover:bg-AB-dark disabled:opacity-50" :disabled="searchLoading" @click="searchPersonnel">
                <search-icon v-if="!searchLoading" class="w-5 h-5" />
                <loader-icon v-else class="w-5 h-5 animate-spin" />
            </button>
        </div>
    </div>
</template>
