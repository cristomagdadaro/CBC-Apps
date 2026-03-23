<template>
    <div v-if="count">
        <span>Selected {{ count }} row<span v-if="count > 1">s</span></span>
    </div>
</template>
<script>
export default {
    props: {
        count: {
            type: Number,
            required: true,
            default: 0,
        },
    },
}
</script>
