<script>
import BaseBtn from "@/Components/Buttons/BaseBtn.vue";

export default {
    name: "ResetBtn",
    components: {BaseBtn},
    props: {
        disabled: Boolean,
    }
}
</script>

<template>
    <base-btn :disabled="disabled" :processing="!disabled" class="text-sm text-yellow-800 bg-yellow-300 dark:text-gray-800 dark:bg-gray-200 disabled:opacity-50">
        <slot />
    </base-btn>
</template>

<style scoped>

</style>
