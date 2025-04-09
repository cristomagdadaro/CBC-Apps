<script>
export default {
    name: "DtRowBody",
    props: {
        selected: {
            type: Boolean,
            default: false,
        }
    },
}
</script>

<template>
    <tr
        class="hover:bg-gray-300 duration-100 cursor-default"
        :class="{ 'bg-blue-200': selected }"
    >
        <slot />
    </tr>
</template>
