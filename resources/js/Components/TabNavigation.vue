<script>
export default {
    name: 'TabNavigation',
    props: {
        tabs: {
            type: Array,
            required: true,
        },
        modelValue: {
            type: [String, Number, null],
            default: null,
        },
        size: {
            type: String,
            default: 'md',
        },
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            localActiveKey: null,
        };
    },
    watch: {
        modelValue(val) {
            this.localActiveKey = val;
        },
        tabs() {
            // ensure localActiveKey falls back to first tab when tabs change
            if (this.localActiveKey == null) this.localActiveKey = this.tabs[0]?.key ?? null;
        }
    },
    computed: {
        activeKey: {
            get() {
                return this.localActiveKey ?? this.modelValue ?? this.tabs[0]?.key ?? null;
            },
            set(val) {
                this.localActiveKey = val;
                this.$emit('update:modelValue', val);
                this.$emit('change', val);
                // Update hash in URL
                if (val !== null && val !== undefined) {
                    window.location.hash = `#tab-${val}`;
                }
            },
        },
        baseClasses() {
            return this.size === 'sm' ? 'px-3 py-1 text-xs' : 'px-4 py-2 text-sm';
        },
    },
    methods: {
        onTabClick(tab) {
            if (tab.disabled) return;
            this.activeKey = tab.key;
        },
        setTabFromHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#tab-')) {
                const key = hash.replace('#tab-', '');
                // Only set if the tab exists
                if (this.tabs.some(tab => String(tab.key) === key)) {
                    this.activeKey = typeof this.tabs[0].key === 'number' ? Number(key) : key;
                }
            }
        },
    },
    mounted() {
        this.setTabFromHash();
        window.addEventListener('hashchange', this.setTabFromHash);
    },
    beforeUnmount() {
        window.removeEventListener('hashchange', this.setTabFromHash);
    },
};
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                @click="onTabClick(tab)"
                :class="[
                    baseClasses,
                    'rounded-t-md inline-flex items-center gap-1 border-b-2 font-medium focus:outline-none transition-colors',
                    tab.disabled
                        ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed border-transparent'
                        : activeKey === tab.key
                            ? 'text-blue-600 dark:text-blue-400 border-blue-500 bg-blue-50/60 dark:bg-gray-800'
                            : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/40',
                ]"
            >
                <slot name="icon" :tab="tab" v-if="tab.icon">
                    <component :is="tab.icon" class="w-4 h-4" />
                </slot>
                <span>{{ tab.label }}</span>
            </button>
        </nav>
        <slot :activeKey="activeKey" />
    </div>
</template>

