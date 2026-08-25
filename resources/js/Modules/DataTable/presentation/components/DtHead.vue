<script>
export default {
    name: "DtHead",
    props: {
        isFiltered: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
};
</script>

<template>
    <th
        class="select-none font-semibold tracking-wide"
        :class="isFiltered ? 'bg-blue-600 text-white' : ''">
        <slot />
    </th>
</template>
