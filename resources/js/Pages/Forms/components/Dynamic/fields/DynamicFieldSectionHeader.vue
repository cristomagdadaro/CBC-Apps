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
    <div class="border-b border-gray-200 pb-2 mt-4 first:mt-0">
        <h4 class="text-md font-semibold text-gray-800">{{ field.label }}</h4>
        <p v-if="field.description" class="text-sm text-gray-600 mt-1">{{ field.description }}</p>
    </div>
</template>
