<script>
export default {
    name: "DtData",
    props: {
        truncate: {
            type: Boolean,
            default: true,
        },
    },
};
</script>

<template>
    <td class="whitespace-nowrap">
        <div :class="truncate ? 'max-w-xs truncate' : ''">
            <slot />
        </div>
    </td>
</template>
