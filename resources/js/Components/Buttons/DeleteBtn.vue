<script>
export default {
    name: "DeleteBtn",
};
</script>

<template>
    <base-btn
        type="submit"
        class="bg-red-400 text-sm text-white dark:bg-gray-200 dark:text-gray-800">
        <slot />
    </base-btn>
</template>

<style scoped></style>
