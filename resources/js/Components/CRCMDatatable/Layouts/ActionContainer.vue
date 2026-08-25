<template>
    <div
        id="dtActionContainer"
        class="flex w-full flex-wrap items-center justify-evenly gap-1 py-0.5 sm:justify-center">
        <slot />
    </div>
</template>
<script>
import ThreeDotIcon from "@/Components/Icons/ThreeDotIcon.vue";

export default {
    components: { ThreeDotIcon },
};
</script>
