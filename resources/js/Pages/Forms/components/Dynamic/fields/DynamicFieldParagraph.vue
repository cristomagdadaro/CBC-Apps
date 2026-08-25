<script>
/**
 * DynamicFieldParagraph.vue - Paragraph text (decorative, non-input) component
 */
export default {
    name: "DynamicFieldParagraph",
    props: {
        field: { type: Object, required: true },
        required: { type: Boolean, default: false },
    },
};
</script>

<template>
    <div class="py-2">
        <label
            v-if="field.label"
            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ field.label }}
            <span
                v-if="required"
                class="text-red-600 dark:text-red-400">
                *
            </span>
        </label>
        <p
            v-if="field.description"
            class="mt-1 text-xs text-gray-600 dark:text-gray-400">
            {{ field.description }}
        </p>
    </div>
</template>
