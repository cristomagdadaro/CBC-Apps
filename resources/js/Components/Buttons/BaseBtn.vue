<script>
export default {
    name: "BaseBtn",
    props: {
        type: {
            type: String,
            default: 'button',
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        processing: {
            type: Boolean,
            default: false,
        }
    }
}
</script>

<template>
    <button :type="type" :disabled="disabled" class="inline-flex gap-1 items-center justify-center disabled:opacity-25 px-4 py-2 border border-transparent rounded-md transition ease-in-out duration-150 active:scale-90" :class="{'cursor-progress' : processing}">
        <slot name="icon" />
        <span>
            <slot />
        </span>
    </button>
</template>

<style scoped>

</style>
