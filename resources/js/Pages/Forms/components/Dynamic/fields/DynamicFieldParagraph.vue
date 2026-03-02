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
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <p v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</p>
    </div>
</template>
