<script>
import BaseBtn from "@/Components/Buttons/BaseBtn.vue";

export default {
    name: "CancelBtn",
    components: {BaseBtn}
}
</script>

<template>
    <base-btn class="text-sm text-white bg-AC dark:text-gray-800 dark:bg-gray-200">
        <slot />
    </base-btn>
</template>

<style scoped>

</style>
