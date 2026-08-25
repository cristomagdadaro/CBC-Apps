<script>
export default {
    name: "SubmitBtn",
    props: {
        disabled: Boolean,
    },
};
</script>

<template>
    <base-btn
        :disabled="disabled"
        :processing="!disabled"
        type="submit"
        class="bg-AB text-sm text-white disabled:opacity-50 dark:bg-gray-200 dark:text-gray-800">
        <slot />
    </base-btn>
</template>

<style scoped></style>
