<template>
    <div class="space-y-2.5">
        <custom-dropdown
            :value="normalizedValue"
            @selectedChange="$emit('update:modelValue', $event)"
            :options="dropdownOptions"
            :disabled="!normalizedOptions.length"
            :placeholder="normalizedOptions.length ? 'Select a value' : 'Add choices below first'"
            :withAllOption="false"
            class="w-full" />

        <div class="flex items-center gap-1.5 px-1">
            <AlertTriangle
                v-if="!normalizedOptions.length"
                class="h-3.5 w-3.5 shrink-0 text-amber-500" />
            <Info
                v-else
                class="h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500" />

            <p
                class="text-[0.65rem] font-semibold uppercase tracking-widest"
                :class="normalizedOptions.length ? 'text-slate-500 dark:text-slate-400' : 'text-amber-600 dark:text-amber-400'">
                {{ normalizedOptions.length ? "Pick the stored value that this option should use by default." : "Define at least one select choice below before choosing a default value." }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Info, AlertTriangle } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: "",
    },
    options: {
        type: Array,
        default: () => [],
    },
});

defineEmits(["update:modelValue"]);

const normalizedValue = computed(() => (props.modelValue === null || props.modelValue === undefined ? "" : String(props.modelValue)));

const normalizedOptions = computed(() =>
    (props.options || [])
        .map((option) => ({
            value: String(option?.value ?? option?.name ?? ""),
            label: String(option?.label ?? option?.name ?? option?.value ?? ""),
            name: String(option?.value ?? option?.name ?? ""), // CustomDropdown usually expects 'name' as the value key
        }))
        .filter((option) => option.value !== "" || option.label !== ""),
);

const showCurrentValue = computed(() => {
    return normalizedValue.value !== "" && !normalizedOptions.value.some((option) => option.value === normalizedValue.value);
});

// Combine the dynamic current value (if needed) with the available options for CustomDropdown
const dropdownOptions = computed(() => {
    const opts = [...normalizedOptions.value];

    if (showCurrentValue.value) {
        opts.unshift({
            name: normalizedValue.value,
            label: `Current: ${normalizedValue.value}`,
            value: normalizedValue.value,
        });
    }

    return opts;
});
</script>
