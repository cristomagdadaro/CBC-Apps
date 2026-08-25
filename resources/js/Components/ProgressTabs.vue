<script>
export default {
    name: "ProgressTabs",
    props: {
        steps: {
            type: Array,
            required: true,
        },
        current: {
            type: Number,
            default: 0,
        },
        clickable: {
            type: Boolean,
            default: true,
        },
        showProgress: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["update:current"],
    data() {
        return {
            maxVisited: 0,
        };
    },
    computed: {
        percent() {
            if (!this.steps.length) return 0;
            return Math.round(((this.current + 1) / this.steps.length) * 100);
        },
    },
    watch: {
        current: {
            immediate: true,
            handler(val) {
                if (typeof val === "number") {
                    this.maxVisited = Math.max(this.maxVisited, val);
                }
            },
        },
    },
    methods: {
        stepStatus(index) {
            if (index < this.current) return "done";
            if (index === this.current) return "active";
            if (index <= this.maxVisited) return "visited";
            return "todo";
        },
        go(index) {
            if (!this.clickable) return;
            if (index < 0 || index >= this.steps.length) return;
            this.$emit("update:current", index);
        },
    },
};
</script>

<template>
    <div class="w-full">
        <div class="flex items-center justify-between overflow-x-hidden">
            <div
                v-for="(label, idx) in steps"
                :key="idx"
                class="flex flex-1 items-center">
                <div
                    class="group flex cursor-pointer items-center"
                    :class="{ 'pointer-events-none': !clickable }"
                    @click="go(idx)"
                    :title="label"
                    :aria-label="label">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full border text-xs font-semibold"
                        :class="{
                            'border-blue-600 bg-blue-600 text-white': stepStatus(idx) === 'active',
                            'border-green-500 bg-green-500 text-white': stepStatus(idx) === 'done',
                            'border-purple-500 bg-purple-500 text-white': stepStatus(idx) === 'visited',
                            'border-gray-300 bg-gray-200 text-gray-600': stepStatus(idx) === 'todo',
                        }">
                        {{ idx + 1 }}
                    </div>
                    <div
                        v-if="idx === current"
                        class="ml-2 whitespace-nowrap text-xs leading-none"
                        :class="{
                            'font-medium text-blue-700 dark:text-blue-100': stepStatus(idx) === 'active',
                            'text-gray-700': stepStatus(idx) === 'done',
                        }">
                        {{ label }}
                    </div>
                </div>
                <div
                    v-if="idx < steps.length - 1"
                    class="mx-2 h-0.5 flex-1"
                    :class="{
                        'bg-green-400': stepStatus(idx) === 'done',
                        'bg-blue-300': stepStatus(idx) === 'active',
                        'bg-purple-300': stepStatus(idx) === 'visited',
                        'bg-gray-200': stepStatus(idx) === 'todo',
                    }" />
            </div>
        </div>

        <div
            v-if="showProgress"
            class="mt-2">
            <div class="h-2 w-full rounded bg-gray-200">
                <div
                    class="h-2 rounded bg-blue-600 transition-all"
                    :style="{ width: percent + '%' }" />
            </div>
            <div class="mt-1 text-right text-xs text-gray-500 dark:text-gray-300">{{ percent }}% complete</div>
        </div>
    </div>
</template>

<style scoped></style>
