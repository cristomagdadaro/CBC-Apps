<script>
export default {
    name: "BaseBtn",
    props: {
        type: {
            type: String,
            default: "button",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        processing: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center gap-1 rounded-md border border-transparent px-4 py-2 transition duration-150 ease-in-out active:scale-90 disabled:opacity-25"
        :class="{ 'cursor-progress': processing }">
        <slot name="icon" />
        <span>
            <slot />
        </span>
    </button>
</template>

<style scoped></style>
