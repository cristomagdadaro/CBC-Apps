export default {
    methods: {
        createFallingLogos() {
            const container = document.getElementById('falling-logos');
            if (!container) return;

            const logoCount = 30; // Adjust the number of logos

            for (let i = 0; i < logoCount; i++) {
                const logo = document.createElement('img');
                logo.src = '/imgs/logo-black.png'; // Replace with actual logo path
                logo.classList.add('falling-logo');

                // Set random positions
                logo.style.left = `${Math.random() * 100}vw`;
                logo.style.animationDuration = `${Math.random() * 5 + 5}s`; // 5 to 10 seconds fall
                const width = Math.random() * 40 + 20;
                logo.style.width = `${width}px`; // Random size
                logo.style.opacity = `${width % 100}%`;

                container.appendChild(logo);
            }
        }
    },
    mounted() {
        this.createFallingLogos();
    },
};

/*copy these code below to the component*/
/*
<template>
    <div id="falling-logos"></div>
</template>
*/


/*<style>
    #falling-logos {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none; /!* Prevent interaction *!/
}

.falling-logo {
    position: absolute;
    top: -50px; /!* Start slightly above viewport *!/
    animation: fall linear infinite;
}

@keyframes fall {
    to {
        transform: translateY(100vh); /!* Move to bottom *!/
        opacity: 0;
    }
}

</style>*/
