<script>
/**
 * DynamicFieldParagraph.vue - Paragraph text (decorative, non-input) component
 */
export default {
    name: "DynamicFieldParagraph",
    props: {
        field: { type: Object, required: true },
    },
};
</script>

<template>
    <div class="py-2">
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ field.label }}</p>
        <p v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</p>
    </div>
</template>
