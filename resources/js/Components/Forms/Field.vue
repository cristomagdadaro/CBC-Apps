<script setup>
import { computed } from "vue";

const props = defineProps({
    id: String,
    label: String,
    required: Boolean,
    error: String,
    hint: String,
    guide: String,
    disabled: Boolean,
    clearable: Boolean,
    hasValue: Boolean,
    datalistId: String,
    datalistOptions: Array,
    classes: { type: [String, Array, Object], default: "" },
    showValidIndicator: { type: Boolean, default: true },
});

const emit = defineEmits(["clear"]);

const inputId = computed(() => {
    const val = String(props.id || "").trim();
    return val === "" ? `field-${Math.random().toString(36).substr(2, 9)}` : val;
});

const isInvalid = computed(() => !!props.error);
const isValid = computed(() => props.hasValue && !props.error);
</script>

<template>
    <div
        class="relative w-full"
        :class="[classes, { 'opacity-60': disabled }]">
        <!-- HEADER ROW: Label, Hint, Error -->
        <div
            v-if="label"
            class="mb-1.5 flex items-end justify-between">
            <label
                :for="inputId"
                class="flex cursor-pointer items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <!-- Slot for prefix icon (e.g., AlignLeft, Clock) -->
                <slot name="label-icon"></slot>

                <span class="flex items-center gap-0.5">
                    {{ label }}
                    <span
                        v-if="required"
                        class="ml-0.5 text-rose-500"
                        aria-label="required">
                        *
                    </span>
                </span>

                <LuHelpCircle
                    v-if="hint"
                    :title="hint"
                    class="ml-1 h-3.5 w-3.5 cursor-help text-slate-400 transition-colors hover:text-indigo-500" />
            </label>

            <!-- Slot for character counters or top-right actions -->
            <div class="flex items-center gap-2.5">
                <slot name="header-actions"></slot>

                <transition name="fade">
                    <div
                        v-if="error"
                        class="flex items-center gap-1 text-[0.65rem] font-semibold uppercase tracking-wider text-rose-500">
                        <LuAlertCircle class="h-3.5 w-3.5" />
                        <span class="max-w-[150px] truncate">{{ error }}</span>
                    </div>
                </transition>
            </div>
        </div>

        <!-- INPUT WRAPPER -->
        <div
            class="group relative flex items-center"
            :class="{ 'cursor-not-allowed': disabled }">
            <!-- 
                SCOPED DEFAULT SLOT: 
                Passes down the generated ID and validation states so the child 
                input can apply specific borders/focus rings automatically.
            -->
            <slot
                :input-id="inputId"
                :is-invalid="isInvalid"
                :is-valid="isValid"
                :guide-id="guide ? `${inputId}-guide` : undefined"></slot>

            <!-- RIGHT-SIDE ACTIONS & VALIDATION ICONS -->
            <div class="absolute right-2 flex items-center gap-1">
                <slot name="input-actions"></slot>

                <button
                    v-if="clearable && hasValue && !disabled"
                    type="button"
                    @click="emit('clear')"
                    class="rounded-lg bg-white/80 p-1.5 text-slate-400 backdrop-blur-sm transition-colors hover:bg-slate-100 hover:text-slate-600 dark:bg-slate-800/80 dark:hover:bg-slate-700">
                    <LuX class="h-4 w-4" />
                </button>

                <div
                    v-else-if="isValid && showValidIndicator"
                    class="p-1.5">
                    <LuCheckCircle2 class="h-4 w-4 text-emerald-500" />
                </div>

                <div
                    v-else-if="isInvalid"
                    class="p-1.5">
                    <LuXCircle class="h-4 w-4 text-rose-500" />
                </div>
            </div>

            <!-- SHARED DATALIST -->
            <datalist
                v-if="datalistId && datalistOptions?.length"
                :id="datalistId">
                <option
                    v-for="opt in datalistOptions"
                    :key="opt"
                    :value="opt" />
            </datalist>
        </div>

        <!-- FOOTER ROW: Guide Text -->
        <p
            v-if="guide"
            :id="`${inputId}-guide`"
            class="mt-2 flex items-start gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
            <LuHelpCircle class="mt-0.5 h-3.5 w-3.5 shrink-0 text-indigo-400 dark:text-indigo-500" />
            <span class="leading-relaxed">{{ guide }}</span>
        </p>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
