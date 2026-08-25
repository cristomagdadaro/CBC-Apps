<script>
export default {
    name: "TabNavigation",
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
            default: "md",
        },
    },
    emits: ["update:modelValue", "change"],
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
        },
    },
    computed: {
        activeKey: {
            get() {
                return this.localActiveKey ?? this.modelValue ?? this.tabs[0]?.key ?? null;
            },
            set(val) {
                this.localActiveKey = val;
                this.$emit("update:modelValue", val);
                this.$emit("change", val);
                // Update hash in URL
                if (val !== null && val !== undefined) {
                    window.location.hash = `#tab-${val}`;
                }
            },
        },
        baseClasses() {
            return this.size === "sm" ? "px-3 py-1 text-xs" : "px-4 py-2 text-sm";
        },
    },
    methods: {
        onTabClick(tab) {
            if (tab.disabled) return;
            this.activeKey = tab.key;
        },
        setTabFromHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith("#tab-")) {
                const key = hash.replace("#tab-", "");
                // Only set if the tab exists
                if (this.tabs.some((tab) => String(tab.key) === key)) {
                    this.activeKey = typeof this.tabs[0].key === "number" ? Number(key) : key;
                }
            }
        },
    },
    mounted() {
        this.setTabFromHash();
        window.addEventListener("hashchange", this.setTabFromHash);
    },
    beforeUnmount() {
        window.removeEventListener("hashchange", this.setTabFromHash);
    },
};
</script>

<template>
    <div class="rounded-xl border border-gray-400 bg-white p-3 transition-colors dark:border-gray-700 dark:bg-gray-700">
        <nav
            class="flex space-x-4"
            aria-label="Tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                @click="onTabClick(tab)"
                :class="[baseClasses, 'inline-flex items-center gap-1 rounded-t-md border-b-2 font-medium transition-colors focus:outline-none', tab.disabled ? 'cursor-not-allowed border-transparent text-gray-400 dark:text-gray-500' : activeKey === tab.key ? 'border-blue-500 bg-blue-50/60 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-800/50 dark:hover:text-gray-100']">
                <slot
                    name="icon"
                    :tab="tab"
                    v-if="tab.icon">
                    <component
                        :is="tab.icon"
                        class="h-4 w-4" />
                </slot>
                <span>{{ tab.label }}</span>
            </button>
        </nav>
        <slot :activeKey="activeKey" />
    </div>
</template>
