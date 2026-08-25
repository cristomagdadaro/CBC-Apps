<script>
import ParticlesBackground from "@/Components/ParticlesBackground.vue";

export default {
    name: "MainBg",
    components: { ParticlesBackground },
};
</script>
<template>
    <div class="fixed left-0 top-0 h-full w-full overflow-hidden">
        <!-- Particles (bottom layer) -->
        <particles-background
            :id="'header-particles-js'"
            class="absolute inset-0" />

        <div
            class="absolute inset-0 -z-20 h-full w-full bg-cover bg-center"
            style="background-image: url(&quot;/imgs/philrice-cbc-compound.jpg&quot;)"></div>

        <!-- Gradient overlay -->
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-green-800/80 via-green-800/50 to-green-800/90 dark:from-slate-900/90 dark:via-slate-900/50 dark:to-slate-900/90"></div>
    </div>
</template>
