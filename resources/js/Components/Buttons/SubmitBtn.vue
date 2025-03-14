<script>
import BaseBtn from "@/Components/Buttons/BaseBtn.vue";

export default {
    name: "SubmitBtn",
    components: {BaseBtn},
    props: {
        disabled: Boolean,
    }
}
</script>

<template>
    <base-btn :disabled="disabled" :processing="!disabled" type="submit" class="text-sm text-white bg-AB dark:text-gray-800 dark:bg-gray-200 disabled:opacity-50">
        <slot />
    </base-btn>
</template>

<style scoped>

</style>
