<template>
    <div :class="selected?'bg-blue-400':'hover:bg-gray-200 active:bg-gray-400'" class="duration-200 text-gray-700 whitespace-nowrap px-4 py-0.5">
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
