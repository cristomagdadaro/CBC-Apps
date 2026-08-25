<template>
    <div
        :class="resolvedContainerClass"
        :style="cardStyle">
        <div
            class="label-card-inner"
            :style="cardInnerStyle">
            <div
                v-if="hasHeader"
                class="label-text"
                :style="{ fontSize: `${fontSize}px` }">
                <div
                    v-if="title"
                    class="label-item">
                    {{ title }}
                </div>
                <div
                    v-if="subtitle"
                    class="label-brand">
                    {{ subtitle }}
                </div>
                <div
                    v-if="description"
                    class="label-brand">
                    {{ description }}
                </div>
            </div>

            <svg
                v-if="hasBarcode"
                ref="barcodeSvg"></svg>
            <qrcode-vue
                v-if="hasQr"
                :value="safeQrValue"
                :size="qrSize"
                level="M"
                render-as="canvas"
                class="label-qr mx-auto" />

            <div
                v-if="hasBarcode"
                class="label-barcode mx-auto"
                :style="{ fontSize: `${fontSize}px` }">
                {{ barcodeValue }}
            </div>
            <div
                v-else-if="qrCaption"
                class="label-qr-caption"
                :style="{ fontSize: `${fontSize * 0.9}px` }">
                {{ qrCaption }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import JsBarcode from "jsbarcode";
import QrcodeVue from "qrcode.vue";

const props = defineProps({
    mode: {
        type: String,
        default: "both",
    },
    barcodeValue: {
        type: String,
        default: "",
    },
    qrValue: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        default: "",
    },
    subtitle: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
    qrCaption: {
        type: String,
        default: "",
    },
    fontSize: {
        type: Number,
        default: 10,
    },
    qrSize: {
        type: Number,
        default: 60,
    },
    barcodeHeight: {
        type: Number,
        default: 30,
    },
    barcodeModuleWidth: {
        type: Number,
        default: 2,
    },
    cardStyle: {
        type: Object,
        default: () => ({}),
    },
    cardInnerStyle: {
        type: Object,
        default: () => ({}),
    },
    containerClass: {
        type: [String, Array, Object],
        default: "label-card",
    },
});

const barcodeSvg = ref(null);

const hasBarcode = computed(() => props.mode !== "qr");
const hasQr = computed(() => props.mode !== "barcode");
const hasHeader = computed(() => !!(props.title || props.subtitle || props.description));
const safeQrValue = computed(() => String(props.qrValue || "-"));
const resolvedContainerClass = computed(() => props.containerClass || "label-card");

const renderBarcode = () => {
    if (!barcodeSvg.value) return;

    if (!hasBarcode.value || !props.barcodeValue) {
        barcodeSvg.value.innerHTML = "";
        return;
    }

    JsBarcode(barcodeSvg.value, props.barcodeValue, {
        format: "CODE128",
        displayValue: false,
        width: props.barcodeModuleWidth,
        height: props.barcodeHeight,
        margin: 0,
    });
};

onMounted(renderBarcode);

watch(() => [props.mode, props.barcodeValue, props.barcodeHeight, props.barcodeModuleWidth], renderBarcode);
</script>

<style scoped>
.label-card-inner {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    gap: 2px;
}

.label-text {
    color: #111827;
    line-height: 1.2;
}

.label-item {
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.label-brand {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.label-barcode {
    display: flex;
    justify-content: center;
    align-items: center;
    color: #374151;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.label-qr {
    display: flex;
    justify-content: center;
    align-items: center;
}

.label-qr-caption {
    display: flex;
    justify-content: center;
    align-items: center;
    color: #374151;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
</style>
