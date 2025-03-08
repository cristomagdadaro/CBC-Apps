<script>
    export default {
        name: "Logo",
        props: {
            classes: {
                type: String,
                default: 'sm:h-12 h-9 w-auto drop-shadow',
            },
        }
    }
</script>
<template>
    <img src="/img/logos/pin.webp" alt="Crop Biotechnology Center Logo" :class="classes">
</template>
