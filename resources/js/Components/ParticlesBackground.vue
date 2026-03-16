<script>
export default {
    name: 'ParticlesBackground',
    props: {
        id: { type: String, default: "particles-js" },
        configPath: { type: String, default: "/particlesjs-config.json" }
    },
    data() {
        return { particlesInstance: null };
    },
    async mounted() {
        await this.initParticles();
    },
    beforeUnmount() {
        if (window.pJSDom && window.pJSDom.length) {
            window.pJSDom = window.pJSDom.filter(p => {
                if (p.pJS.canvas.el.id === this.id) {
                    p.pJS.fn.vendors.destroypJS();
                    return false;
                }
                return true;
            });
        }
    },
    methods: {
        async initParticles() {
            try {
                const response = await fetch(this.configPath);
                const config = await response.json();
                
                if (window.particlesJS) {
                    window.particlesJS(this.id, config);
                }
            } catch (error) {
                console.warn('Particles config failed to load:', error);
            }
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
    z-index: -1;
}
</style>
