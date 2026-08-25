<script>
export default {
    name: "ResetBtn",
    props: {
        disabled: Boolean,
    },
};
</script>

<template>
    <base-btn
        :disabled="disabled"
        :processing="!disabled"
        class="bg-yellow-300 text-sm text-yellow-800 disabled:opacity-50 dark:bg-gray-200 dark:text-gray-800">
        <slot />
    </base-btn>
</template>

<style scoped></style>
