<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";

export default {
    name: 'CameraScanner',
    components: {FilterIcon, CustomDropdown, QrcodeStream },
    props: {
        enabled: {
            type: Boolean,
            default: true,
        },
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
        beepUrl: {
            type: String,
            default: '/misc/audio/beep3-98810.mp3',
        },
        label: {
            type: String,
            default: 'Available Camera Devices',
        },
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
            _scanCooldown: null,
        };
    },
    computed: {
        selectedBarcodeFormats() { return Object.keys(this.formats).filter(f => this.formats[f]); },
        hasDevice() { return !!this.selectedDeviceId; },
        selectedDevice() { return this.devices.find(d => d.deviceId === this.selectedDeviceId) || null; },
        showScannerArea() { return this.isOpen; }
    },
    methods: {
        stopMediaTracks() { if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; } },
        stopVideoElementTracks() {
            const video = this.$el?.querySelector('video');
            const stream = video?.srcObject;
            if (stream && typeof stream.getTracks === 'function') {
                stream.getTracks().forEach(t => t.stop());
                video.srcObject = null;
            }
        },
        stopAllStreams() {
            this.stopMediaTracks();
            this.stopVideoElementTracks();
        },
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
            if (!code) return;
            // Add a cooldown to prevent rapid-fire duplicate events, but allow consecutive scans
            if (this._scanCooldown) return;
            this._scanCooldown = setTimeout(() => { this._scanCooldown = null; }, 1000); // 1 second cooldown
            this.lastDecoded = code;
            this.$emit('decoded', code);
            if (this.beep) this.beep.play().catch(() => {});
            this.showBorder = true;
            setTimeout(() => { if (this.beep) { this.beep.pause(); this.beep.currentTime = 0; } this.showBorder = false; }, 1000);
        },
        onError(error) { this.error = error; },
        async setupMediaSource() {
            if (!this.enabled || !this.isOpen) return; // only open when visible
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
        },
        toggleOpen() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && !this.stream) this.setupMediaSource();
            else if (!this.isOpen) this.stopAllStreams();
        },
    },
    mounted() {
        this.beep = new Audio(this.beepUrl); this.beep.load();
        if (typeof window !== 'undefined') { this.handleResize(); window.addEventListener('resize', this.handleResize); }
        this.isOpen = this.defaultOpenSmall;
        this.setupMediaSource();
    },
    unmounted() {
        if (typeof window !== 'undefined') window.removeEventListener('resize', this.handleResize);
        this.stopMediaTracks();
    },
    watch: {
        error(val) { if (val) this.$emit('error', val); },
        enabled(val) { if (val && this.isOpen) this.setupMediaSource(); else if (!val) this.stopAllStreams(); },
        isOpen(val) { if (val && !this.stream) this.setupMediaSource(); else if (!val) this.stopAllStreams(); },
    },
};
</script>

<template>
    <div class="flex flex-col gap-2 w-full max-w-xl">
        <div class="grid gap-1 w-full" :class="showScannerArea? 'md:grid-rows-2 grid-cols-2 md:grid-cols-1':'grid-cols-1'">
            <div class="flex flex-col gap-0.5 w-full">
                <div class="text-xs text-gray-600 flex items-center justify-between">
                    <span class="flex gap-0.5 whitespace-nowrap"> Search by Scanner </span>
                </div>
                <button type="button" @click="toggleOpen" :aria-checked="isOpen.toString()" role="switch" class="w-full h-full flex items-center justify-between px-3 py-2 border border-gray-700 rounded-md bg-white hover:bg-gray-50 active:scale-[.98] duration-75">
                    <span class="text-sm whitespace-nowrap">
                        {{ isOpen ? 'Scanner On' : 'Scanner Off' }}
                    </span>
                    <span class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="isOpen ? 'bg-green-600' : 'bg-gray-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="isOpen ? 'translate-x-6' : 'translate-x-1'"></span>
                    </span>
                </button>
            </div>
            <div v-if="showScannerArea">
                <div v-if="devices.length" class="flex flex-col gap-1">
                    <div class="text-xs text-gray-600 flex items-center justify-between">
                        <span class="flex gap-0.5 whitespace-nowrap"> {{ label }} </span>
                    </div>
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
            </div>
        </div>


        <transition name="fade" mode="out-in">
            <div v-show="showScannerArea" class="flex flex-col gap-2 w-full">
                <div class="relative w-full h-full mt-2">
                    <QrcodeStream
                        v-if="enabled && hasDevice && showScannerArea"
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
