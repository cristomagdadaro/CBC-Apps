<script>
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import FieldMixin from '@/Components/Forms/FieldMixin';

export default {
    name: "SingleSelectTagify",
    mixins: [FieldMixin],
    props: {
        modelValue: {
            type: [String, Array],
            default: ''
        },
        whitelist: {
            type: Array,
            default: () => []
        },
        placeholder: {
            type: String,
            default: 'Search or enter custom location...'
        },
        name: {
            type: String,
            default: 'location'
        }
    },
    emits: ['update:modelValue', 'keydown'],
    data() {
        return {
            tagify: null
        }
    },
    watch: {
        whitelist: {
            deep: true,
            handler(newWhitelist) {
                if (this.tagify) {
                    this.tagify.settings.whitelist = newWhitelist;
                }
            }
        },
        modelValue(newValue) {
            if (this.tagify) {
                const currentVal = this.tagify.value.map(item => item.value).join(',');
                if (newValue !== currentVal && newValue !== this.tagify.value[0]?.value) {
                    this.tagify.loadOriginalValues(newValue ? [newValue] : []);
                }
            }
        }
    },
    methods: {
        clearValue() {
            this.$emit('update:modelValue', '');
        }
    },
    mounted() {
        this.tagify = new Tagify(this.$refs.tagifyInput, {
            mode: "select",
            whitelist: this.whitelist,
            enforceWhitelist: false,
            keepInvalidTags: true,
            dropdown: {
                enabled: 0,
                closeOnSelect: true,
                maxItems: 100,
                searchKeys: ['value'],
                appendTarget: document.body
            }
        });

        if (this.modelValue) {
            this.tagify.loadOriginalValues([this.modelValue]);
        }

        this.tagify.on('change', (e) => {
            const value = e.detail.value;
            if (!value) {
                this.$emit('update:modelValue', '');
                return;
            }
            try {
                const parsed = JSON.parse(value);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    this.$emit('update:modelValue', parsed[0].value);
                } else {
                    this.$emit('update:modelValue', '');
                }
            } catch (err) {
                this.$emit('update:modelValue', value);
            }
        });

        // Forward the enter keydown event so the form can submit
        this.$refs.tagifyInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.$emit('keydown', e);
            }
        });
    },
    beforeUnmount() {
        if (this.tagify) {
            this.tagify.destroy();
        }
    }
}
</script>

<template>
    <Field
        :id="id"
        :label="label"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :clearable="clearable"
        :has-value="!!modelValue"
        :disabled="disabled"
        @clear="clearValue"
        class="tagify-wrapper"
    >
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <input 
                :id="inputId"
                ref="tagifyInput" 
                :name="name" 
                :disabled="disabled"
                :aria-invalid="isInvalid"
                :aria-describedby="guideId"
                class="w-full" 
                :placeholder="placeholder" 
            />
        </template>
    </Field>
</template>

<style>
.tagify-wrapper .tagify {
    --tags-border-color: #d1d5db;
    --tags-hover-border-color: #9ca3af;
    --tags-focus-border-color: #059669; /* Emerald 600 */
    border-radius: 0.5rem;
    background-color: #f9fafb;
}

/* Global Dropdown Styles (attached to body) */
.tagify__dropdown {
    z-index: 99999 !important; /* Ensure it stays above modals (z-50) */
    background-color: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    border-radius: 0.375rem !important;
    padding: 0.25rem 0 !important;
}

.tagify__dropdown__item {
    padding: 0.5rem 1rem !important;
    color: #374151 !important;
    margin: 0 !important;
    border-radius: 0 !important;
    font-size: 0.875rem !important; /* text-sm */
}

.tagify__dropdown__item--active {
    background-color: #10b981 !important; /* Emerald 500 */
    color: #ffffff !important;
}

/* Dark mode support */
:is(.dark .tagify-wrapper .tagify) {
    --tags-border-color: #374151;
    --tags-hover-border-color: #4b5563;
    --tags-focus-border-color: #059669;
    --tag-text-color: #f3f4f6;
    background-color: #1f2937;
    color: #f3f4f6;
}

:is(.dark .tagify__dropdown) {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
}

:is(.dark .tagify__dropdown__item) {
    color: #f3f4f6 !important;
}

:is(.dark .tagify__dropdown__item--active) {
    background-color: #059669 !important; /* Emerald 600 */
    color: #ffffff !important;
}
</style>
