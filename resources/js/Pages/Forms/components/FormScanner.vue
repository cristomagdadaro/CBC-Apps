<script setup>
import { ref } from "vue";
import { QrcodeStream } from "vue-qrcode-reader";

const result = ref("");

const onDecode = (decodedText) => {
    result.value = decodedText;
};
</script>

<template>
    <QrcodeStream @decode="onDecode" />
    <p>Scanned QR Code: {{ result }}</p>
</template>
