<script>
import MultiSelectDropdown from "@/Components/MultiSelectDropdown.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    name: "TagifyInput",
    components: { MultiSelectDropdown },
    mixins: [ApiMixin],
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: [Array, String],
            default: () => []
        },
        placeholder: String,
        classes: String,
        name: String,
        apiLink: String,
        whitelist: {
            type: Array,
            default: () => [],
        },
        enforceWhitelist: {
            type: Boolean,
            default: false,
        },
        params: {
            type: Object,
            default: () => ({}),
        },
    },
    async mounted() {
        await this.loadOptions();
    },
    watch: {
        modelValue(newVal) {
            this.selectedValues = Array.isArray(newVal) ? [...newVal] : [];
        },
    },
    data() {
        return {
            options: [],
            selectedValues: [],
        };
    },
    methods: {
        /**
         * Normalize whitelist to proper format
         */
        normalizeWhitelist(list) {
            if (!Array.isArray(list)) return [];

            return list.map(item => {
                // If it's a string, convert to object format
                if (typeof item === 'string') {
                    return {
                        value: item,
                        label: item,
                    };
                }

                // If it's already an object, ensure it has 'value' property
                if (typeof item === 'object' && item !== null) {
                    return {
                        value: item.value ?? item.name ?? item.label ?? String(item),
                        label: item.label ?? item.name ?? item.value ?? String(item),
                    };
                }

                return null;
            }).filter(Boolean);
        },

        async loadOptions() {
            // Normalize whitelist
            this.options = this.normalizeWhitelist(this.whitelist);

            // Fetch from API if provided
            if (this.apiLink) {
                try {
                    const params = {
                        filter: 'name',
                        per_page: '*',
                        ...this.params,
                    };

                    const response = await this.fetchGetApi(this.apiLink, params);
                    const payload = response?.data ?? response;
                    const list = Array.isArray(payload) ? payload : payload?.data ?? [];
                    const apiOptions = this.normalizeWhitelist(list);

                    this.options = [...this.options, ...apiOptions];
                } catch (error) {
                    console.error('Failed to fetch Tagify options:', error);
                }
            }

            // Initialize selected values
            this.selectedValues = Array.isArray(this.modelValue) ? [...this.modelValue] : [];
        },

        /**
         * Fetch options from API endpoint
         */
        async fetchData() {
            if (!this.apiLink) return [];

            try {

                const params = {
                    filter: 'name',
                    per_page: '*',
                    ...this.params,
                }

                const response = await this.fetchGetApi(this.apiLink, params);
                const payload = response?.data ?? response;
                const list = Array.isArray(payload) ? payload : payload?.data ?? [];

                return this.normalizeWhitelist(list);
            } catch (error) {
                console.error('Failed to fetch Tagify data:', error);
                return [];
            }
        },

        handleUpdate(newValue) {
            this.$emit('update:modelValue', newValue);
        },
    },
}
</script>

<template>
    <MultiSelectDropdown
        :modelValue="selectedValues"
        :placeholder="placeholder"
        :options="options"
        :class="classes"
        @update:modelValue="handleUpdate"
    />
</template>
