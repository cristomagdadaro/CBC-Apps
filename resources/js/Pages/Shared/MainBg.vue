<script>
import ParticlesBackground from '@/Components/ParticlesBackground.vue';

export default {
  name: 'MainBg',
  components: { ParticlesBackground },
};
</script>
<template>
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden">
    <!-- Particles (bottom layer) -->
    <particles-background :id="'header-particles-js'" class="absolute inset-0" />

    <div class="absolute inset-0 w-full h-full bg-cover bg-center -z-20" style="background-image: url('/imgs/philrice-cbc-compound.jpg');">
    </div>

    <!-- Gradient overlay -->
    <div class="absolute inset-0 -z-10 bg-gradient-to-b pointer-events-none from-green-800/80 via-green-800/50 to-green-800/90 dark:from-slate-900/90 dark:via-slate-900/50 dark:to-slate-900/90"></div>
  </div>
</template>