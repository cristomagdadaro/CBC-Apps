<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";

export default {
    name: 'CameraScanner',
    components: {FilterIcon, CustomDropdown, QrcodeStream },
    props: {
        /** Enable or disable all scanning */
        enabled: {
            type: Boolean,
            default: true,
        },
        /** Override which barcode formats are enabled */
        formats: {
            type: Object,
            default: () => ({
                aztec: true, code_128: true, code_39: true, code_93: true,
                codabar: true, databar: true, databar_expanded: true, data_matrix: true,
                dx_film_edge: true, ean_13: true, ean_8: true, itf: true, maxi_code: true,
                micro_qr_code: true, pdf417: true, qr_code: true, rm_qr_code: true,
                upc_a: true, upc_e: true, linear_codes: true, matrix_codes: true,
            }),
        },
        /** Optional custom audio URL for beep sound when a code is detected */
        beepUrl: {
            type: String,
            default: '/misc/audio/beep3-98810.mp3',
        },
        /** Optional label to show above the device selector */
        label: {
            type: String,
            default: 'Available Camera Devices',
        },
        /** Whether the scanner section should default open on small screens */
        defaultOpenSmall: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['decoded', 'error'],
    data() {
        return {
            error: null,
            lastDecoded: null,
            showBorder: false,
            stream: null,
            devices: [],
            selectedDeviceId: null,
            beep: null,
            isMdUp: false,
            isOpen: false,
        };
    },
    computed: {
        selectedBarcodeFormats() { return Object.keys(this.formats).filter(f => this.formats[f]); },
        hasDevice() { return !!this.selectedDeviceId; },
        selectedDevice() { return this.devices.find(d => d.deviceId === this.selectedDeviceId) || null; },
        showScannerArea() { return this.isMdUp || this.isOpen; }
    },
    methods: {
        stopMediaTracks() { if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; } },
        paintCenterText(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const { boundingBox, rawValue } = detectedCode;
                const centerX = boundingBox.x + boundingBox.width / 2;
                const centerY = boundingBox.y + boundingBox.height / 2;
                const fontSize = Math.max(10, (50 * boundingBox.width) / ctx.canvas.width);
                ctx.font = `bold ${fontSize}px sans-serif`; ctx.textAlign = 'center';
                ctx.lineWidth = 3; ctx.strokeStyle = '#35495e'; ctx.strokeText(detectedCode.rawValue, centerX, centerY);
                ctx.fillStyle = '#ffffff'; ctx.fillText(rawValue, centerX, centerY);
            }
        },
        paintOutline(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const [firstPoint, ...otherPoints] = detectedCode.cornerPoints;
                ctx.strokeStyle = 'red'; ctx.beginPath(); ctx.moveTo(firstPoint.x, firstPoint.y);
                for (const { x, y } of otherPoints) ctx.lineTo(x, y);
                ctx.lineTo(firstPoint.x, firstPoint.y); ctx.closePath(); ctx.stroke();
            }
            this.paintCenterText(detectedCodes, ctx);
        },
        onDecode(detectedCodes) {
            if (!detectedCodes?.length) return;
            const code = detectedCodes[0].rawValue;
            if (!code || code === this.lastDecoded) return;
            this.lastDecoded = code; this.$emit('decoded', code);
            if (this.beep) this.beep.play().catch(() => {});
            this.showBorder = true;
            setTimeout(() => { if (this.beep) { this.beep.pause(); this.beep.currentTime = 0; } this.showBorder = false; }, 1000);
        },
        onError(error) { this.error = error; },
        async setupMediaSource() {
            if (!this.enabled || (!this.isMdUp && !this.isOpen)) return; // only open when visible
            try {
                if (!navigator?.mediaDevices) { this.error = 'Media devices unavailable'; return; }
                let permissionObj; // permissions API may not exist in all browsers
                if (navigator.permissions?.query) {
                    try { permissionObj = await navigator.permissions.query({ name: 'camera' }); } catch (_) {}
                }
                if (!permissionObj || ['granted','prompt'].includes(permissionObj.state)) {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    this.devices = (await navigator.mediaDevices.enumerateDevices())
                        .filter(d => d.kind === 'videoinput')
                        .map(d => ({ deviceId: d.deviceId, label: d.label }));
                    if (!this.selectedDeviceId && this.devices.length) this.selectedDeviceId = this.devices[0].deviceId;
                } else if (permissionObj.state === 'denied') { this.error = 'Camera access denied'; }
            } catch (error) { this.error = error; }
        },
        onDeviceChange(event) { const id = event; if (id && id !== this.selectedDeviceId) this.selectedDeviceId = id; },
        handleResize() {
            const wasMdUp = this.isMdUp;
            this.isMdUp = typeof window !== 'undefined' ? window.innerWidth >= 768 : false;
            if (this.isMdUp) { this.isOpen = true; if (!this.stream) this.setupMediaSource(); }
            else if (wasMdUp && !this.defaultOpenSmall) { this.isOpen = false; this.stopMediaTracks(); }
        },
        toggleOpen() { if (!this.isMdUp) { this.isOpen = !this.isOpen; if (this.isOpen && !this.stream) this.setupMediaSource(); else if (!this.isOpen) this.stopMediaTracks(); } },
    },
    mounted() {
        this.beep = new Audio(this.beepUrl); this.beep.load();
        if (typeof window !== 'undefined') { this.handleResize(); window.addEventListener('resize', this.handleResize); }
        if (!this.isMdUp) this.isOpen = this.defaultOpenSmall;
        this.setupMediaSource();
    },
    unmounted() {
        if (typeof window !== 'undefined') window.removeEventListener('resize', this.handleResize);
        this.stopMediaTracks();
    },
    watch: {
        error(val) { if (val) this.$emit('error', val); },
        enabled(val) { if (val) this.setupMediaSource(); else this.stopMediaTracks(); },
        isOpen(val) { if (!this.isMdUp) { if (val && !this.stream) this.setupMediaSource(); else if (!val) this.stopMediaTracks(); } },
    },
};
</script>

<template>
    <div class="flex flex-col gap-2 w-full max-w-xl">
        <!-- Small screen toggle button -->
        <div class="flex md:hidden">
            <button
                type="button"
                @click="toggleOpen"
                :aria-expanded="isOpen.toString()"
                class="px-3 py-2 text-sm rounded-md text-left border border-gray-300 bg-white shadow hover:bg-gray-50 active:scale-[.98] duration-75 flex items-center gap-2 w-full"
            >
                <span v-if="!isOpen" class="w-full">Show Scanner</span>
                <span v-else  class="w-full">Hide Scanner</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="isOpen ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                </svg>
            </button>
        </div>

        <!-- Scanner content -->
        <transition name="fade" mode="out-in">
            <div v-show="showScannerArea" class="flex flex-col gap-2 w-full">
                <div v-if="devices.length" class="flex flex-col gap-1">
                    <label class="text-gray-600 text-center text-sm whitespace-nowrap">{{ label }}</label>
                    <custom-dropdown
                        required
                        searchable
                        :with-all-option="false"
                        :value="selectedDeviceId"
                        :options="devices.map((device) => ({ name: device.deviceId, label: device.label || 'Camera ' + (devices.indexOf(device) + 1) }))"
                        placeholder="Select a Camera Device"
                        @selectedChange="onDeviceChange"
                        class="w-full"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                </div>
                <div v-else class="text-center py-2 text-gray-600 text-sm">
                    Initializing cameras...
                </div>

                <div class="relative w-full h-full mt-2">
                    <QrcodeStream
                        v-if="enabled && hasDevice"
                        :constraints="{ deviceId: selectedDevice?.deviceId }"
                        :track="paintOutline"
                        :formats="selectedBarcodeFormats"
                        @error="onError"
                        @detect="onDecode"
                        class="w-full h-full"
                    />
                    <div v-else class="text-center py-4 text-gray-600">
                        <span v-if="!hasDevice">No scanner is selected.</span>
                        <span v-else-if="!enabled">Scanner disabled.</span>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
