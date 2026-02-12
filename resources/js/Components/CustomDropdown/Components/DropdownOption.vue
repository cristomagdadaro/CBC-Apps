<template>
    <div
        :class="selected ? 'bg-blue-400' : 'hover:bg-gray-200 active:bg-gray-400'"
        class="duration-200 text-gray-700 whitespace-normal break-words overflow-hidden w-full px-4 py-1"
    >
        <slot />
    </div>

</template>
<script>
export default {
    props: {
        selected: {
            type: Boolean,
            required: false,
            default: false,
        }
    },
}
</script>
