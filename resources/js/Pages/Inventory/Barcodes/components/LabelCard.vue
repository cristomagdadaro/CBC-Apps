<template>
    <div class="label-card" :style="cardStyle">
        <div class="label-card-inner" :style="cardInnerStyle">
            <div class="label-text" :style="{ fontSize: `${labelFontSize}px` }">
                <div class="label-item">{{ label?.item?.name }}</div>
                <div class="label-brand">
                    {{ label?.item?.brand }}
                    <span v-if="label?.item?.description">({{ label.item.description }})</span>
                </div>
            </div>
            <svg v-if="hasBarcode" ref="barcodeSvg"></svg>
            <qrcode-vue v-if="hasQr" :value="label?.equipmentUrl" :size="qrSize" level="M" render-as="canvas" class="label-qr mx-auto" />
            <div v-if="hasBarcode" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize}px` }">
                {{ label?.item?.barcode }}
            </div>
            <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 0.9}px` }">
                {{ label?.item?.barcode }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import JsBarcode from "jsbarcode";
import QrcodeVue from "qrcode.vue";

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

const barcodeSvg = ref(null);
const hasBarcode = computed(() => props.printMode !== "qr");
const hasQr = computed(() => props.printMode !== "barcode");

const renderBarcode = () => {
    if (!barcodeSvg.value) return;
    if (!hasBarcode.value || !props.label?.item?.barcode) {
        barcodeSvg.value.innerHTML = "";
        return;
    }

    JsBarcode(barcodeSvg.value, props.label.item.barcode, {
        format: "CODE128",
        displayValue: false,
        width: props.barcodeModuleWidth,
        height: props.barcodeHeight,
        margin: 0,
    });
};

onMounted(renderBarcode);

watch(
    () => [props.printMode, props.barcodeHeight, props.barcodeModuleWidth, props.label?.item?.barcode ?? ""],
    renderBarcode,
);
</script>
