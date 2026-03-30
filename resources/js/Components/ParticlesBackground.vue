<script>
import particlesConfig from '@/assets/particlesjs-config.json';

export default {
    name: 'ParticlesBackground',
    props: {
        id: { type: String, default: "particles-js" }
    },
    mounted() {
        this.initParticles();
        window.addEventListener('resize', this.handleResize);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize);
        if (window.pJSDom) {
            window.pJSDom.forEach(p => {
                if (p?.pJS?.canvas?.el?.id === this.id) {
                    p.pJS.fn.vendors.destroypJS();
                }
            });

            window.pJSDom = window.pJSDom.filter(
                p => p?.pJS?.canvas?.el?.id !== this.id
            );
        }
    },
    methods: {
        initParticles() {
            if (!window.particlesJS) {
                console.warn('particlesJS not loaded');
                return;
            }

            window.particlesJS(this.id, particlesConfig);
        },
        handleResize() {
            this.initParticles();
        }
    }
};
</script>
<template>
    <div :id="id" class="particles-container"></div>
</template>

<style scoped>
.particles-container {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 0;
}
</style>
