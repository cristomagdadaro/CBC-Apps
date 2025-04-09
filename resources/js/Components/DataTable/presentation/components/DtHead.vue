<script>
export default {
    name: "DtHead",
    props: {
        isFiltered: {
            type: Boolean,
            required: false,
            default: false
        },
    },
}
</script>

<template>
    <th :class="isFiltered?'bg-blue-600 border border-blue-800 hover:bg-blue-700':''">
        <slot />
    </th>
</template>
