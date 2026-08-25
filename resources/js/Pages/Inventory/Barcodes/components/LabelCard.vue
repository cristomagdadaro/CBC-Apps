<template>
    <QrBarCode
        :mode="printMode"
        :title="label?.item?.name || ''"
        :subtitle="subtitleText"
        :barcode-value="label?.item?.barcode || ''"
        :qr-value="label?.equipmentUrl || ''"
        :qr-caption="label?.item?.barcode || ''"
        :font-size="labelFontSize"
        :qr-size="qrSize"
        :barcode-height="barcodeHeight"
        :barcode-module-width="barcodeModuleWidth"
        :card-style="cardStyle"
        :card-inner-style="cardInnerStyle"
        container-class="label-card hover:scale-[2] transition-transform duration-300 hover:z-10" />
</template>

<script setup>
import { computed } from "vue";
import QrBarCode from "@/Components/QrBarCode.vue";

const props = defineProps({
    label: { type: Object, required: true },
    printMode: { type: String, required: true },
    labelFontSize: { type: Number, required: true },
    qrSize: { type: Number, required: true },
    barcodeHeight: { type: Number, required: true },
    barcodeModuleWidth: { type: Number, required: true },
    cardStyle: { type: Object, required: true },
    cardInnerStyle: { type: Object, required: true },
});

const subtitleText = computed(() => {
    const brand = props.label?.item?.brand || "";
    const description = props.label?.item?.description || "";

    if (!brand) {
        return description;
    }

    return description ? `${brand} (${description})` : brand;
});
</script>
