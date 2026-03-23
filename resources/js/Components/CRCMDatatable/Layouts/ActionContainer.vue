<template>
    <div id="dtActionContainer" class="flex-wrap flex w-full gap-1 py-0.5 sm:justify-center justify-evenly items-center">
        <slot />
    </div>
</template>
<script>
import ThreeDotIcon from "@/Components/Icons/ThreeDotIcon.vue";

export default {
    components: {ThreeDotIcon},
}
</script>
