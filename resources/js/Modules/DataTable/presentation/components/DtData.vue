<script>
export default {
    name: "DtData",
    props: {
        truncate: {
            type: Boolean,
            default: true
        }
    }
}
</script>

<template>
    <td class="whitespace-nowrap">
        <div :class="truncate ? 'truncate max-w-xs' : ''">
            <slot />
        </div>
    </td>
</template>