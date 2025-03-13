<script>
import BaseBtn from "@/Components/Buttons/BaseBtn.vue";

export default {
    name: "DeleteBtn",
    components: {BaseBtn}
}
</script>

<template>
    <base-btn type="submit" class="text-sm text-white bg-red-400 dark:text-gray-800 dark:bg-gray-200">
        <slot />
    </base-btn>
</template>

<style scoped>

</style>
