<script>
export default {
    name: 'ProgressTabs',
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
        }
    },
    emits: ['update:current'],
    data() {
        return {
            maxVisited: 0,
        };
    },
    computed: {
        percent() {
            if (!this.steps.length) return 0;
            return Math.round(((this.current + 1) / this.steps.length) * 100);
        }
    },
    watch: {
        current: {
            immediate: true,
            handler(val) {
                if (typeof val === 'number') {
                    this.maxVisited = Math.max(this.maxVisited, val);
                }
            }
        }
    },
    methods: {
        stepStatus(index) {
            if (index < this.current) return 'done';
            if (index === this.current) return 'active';
            if (index <= this.maxVisited) return 'visited';
            return 'todo';
        },
        go(index) {
            if (!this.clickable) return;
            if (index < 0 || index >= this.steps.length) return;
            this.$emit('update:current', index);
        }
    }
}
</script>

<template>
    <div class="w-full">
        <div class="flex items-center justify-between ">
            <div v-for="(label, idx) in steps" :key="idx" class="flex-1 flex items-center">
                <div
                    class="flex items-center cursor-pointer group"
                    :class="{ 'pointer-events-none': !clickable }"
                    @click="go(idx)"
                    :title="label"
                    :aria-label="label"
                >
                    <div
                        class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-semibold border"
                        :class="{
              'bg-blue-600 text-white border-blue-600': stepStatus(idx) === 'active',
              'bg-green-500 text-white border-green-500': stepStatus(idx) === 'done',
              'bg-purple-500 text-white border-purple-500': stepStatus(idx) === 'visited',
              'bg-gray-200 text-gray-600 border-gray-300': stepStatus(idx) === 'todo',
            }"
                    >
                        {{ idx + 1 }}
                    </div>
                    <div v-if="idx === current"
                         class="ml-2 text-xs leading-none whitespace-nowrap"
                         :class="{
                 'text-blue-700 font-medium': stepStatus(idx) === 'active',
                 'text-gray-700': stepStatus(idx) === 'done',
               }"
                    >
                        {{ label }}
                    </div>
                </div>
                <div v-if="idx < steps.length - 1" class="flex-1 h-0.5 mx-2"
                     :class="{
               'bg-green-400': stepStatus(idx) === 'done',
               'bg-blue-300': stepStatus(idx) === 'active',
               'bg-purple-300': stepStatus(idx) === 'visited',
               'bg-gray-200': stepStatus(idx) === 'todo',
             }"
                />
            </div>
        </div>

        <div v-if="showProgress" class="mt-2">
            <div class="w-full bg-gray-200 h-2 rounded">
                <div class="bg-blue-600 h-2 rounded transition-all" :style="{ width: percent + '%' }"/>
            </div>
            <div class="text-right text-xs text-gray-500 mt-1">{{ percent }}% complete</div>
        </div>
    </div>
</template>

<style scoped>
</style>
