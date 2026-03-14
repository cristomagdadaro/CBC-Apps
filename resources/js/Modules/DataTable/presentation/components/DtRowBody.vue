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
        class="transition-colors duration-150 cursor-pointer"
        :class="{ 
            'bg-blue-50 dark:bg-blue-900/20': selected,
            'hover:bg-gray-50 dark:hover:bg-gray-700/50': !selected 
        }"
    >
        <slot />
    </tr>
</template>