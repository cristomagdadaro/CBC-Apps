<script>
/**
 * DynamicFieldSectionHeader.vue - Section header (decorative, non-input) component
 */
export default {
    name: "DynamicFieldSectionHeader",
    props: {
        field: { type: Object, required: true },
    },
};
</script>

<template>
    <div class="mt-4 border-b border-gray-200 pb-2 first:mt-0 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100">{{ field.label }}</h4>
        <p
            v-if="field.description"
            class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ field.description }}
        </p>
    </div>
</template>
