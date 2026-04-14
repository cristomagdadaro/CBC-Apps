<script>
import { BarcodeDetector as PolyfillBarcodeDetector } from 'barcode-detector';
import {
    ScanLine,
    Camera,
    SwitchCamera,
    Volume2,
    VolumeX,
    AlertCircle,
    CheckCircle2
} from 'lucide-vue-next';
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: 'CameraScanner',
    components: {
        CustomDropdown,
        ScanLine,
        Camera,
        SwitchCamera,
        Volume2,
        VolumeX,
        AlertCircle,
        CheckCircle2
    },
    props: {
        // Core functionality
        enabled: {
            type: Boolean,
            default: true,
        },
        modelValue: {
            type: Boolean,
            default: false,
        },
        formats: {
            type: Array,
            default: () => [
                'qr_code', 'code_128', 'code_39', 'ean_13',
                'ean_8', 'upc_a', 'upc_e', 'pdf417'
            ],
        },

        // Customization
        beepUrl: {
            type: String,
            default: '/misc/audio/beep3-98810.mp3',
        },
        beepEnabled: {
            type: Boolean,
            default: true,
        },
        label: {
            type: String,
            default: null, // Uses default if not provided
        },
        placeholder: {
            type: String,
            default: 'Select camera device',
        },

        // Layout options
        variant: {
            type: String,
            default: 'default', // 'default', 'compact', 'minimal'
            validator: (v) => ['default', 'compact', 'minimal'].includes(v),
        },
        showToggle: {
            type: Boolean,
            default: true,
        },
        showDeviceSelect: {
            type: Boolean,
            default: true,
        },
        autoStart: {
            type: Boolean,
            default: false,
        },
        defaultOpenSmall: {
            type: Boolean,
            default: false,
        },

        // Styling
        scannerHeight: {
            type: String,
            default: '150px',
        },
        borderColor: {
            type: String,
            default: 'indigo',
        },
    },
    emits: ['decoded', 'error', 'update:modelValue', 'ready'],

    data() {
        return {
            error: null,
            lastDecoded: null,
            showSuccessBorder: false,
            devices: [],
            selectedDeviceId: null,
            mediaStream: null,
            barcodeDetector: null,
            scanFrameId: null,
            scanFrameBusy: false,
            beep: null,
            isReady: false,
            _scanCooldown: null,
            isClient: false,
            internalIsOpen: false,
        };
    },

    computed: {
        isOpen: {
            get() {
                return this.internalIsOpen;
            },
            set(val) {
                this.internalIsOpen = val;
                this.$emit('update:modelValue', val);
            }
        },
        selectedDevice() {
            return this.devices.find(d => d.deviceId === this.selectedDeviceId) || null;
        },
        hasMultipleDevices() {
            return this.devices.length > 1;
        },
        displayLabel() {
            return this.label || (this.variant === 'minimal' ? 'Scanner' : 'Camera Scanner');
        },
        borderColorClass() {
            const colors = {
                indigo: 'border-indigo-500 shadow-indigo-500/20',
                emerald: 'border-emerald-500 shadow-emerald-500/20',
                blue: 'border-blue-500 shadow-blue-500/20',
                purple: 'border-purple-500 shadow-purple-500/20',
            };
            return colors[this.borderColor] || colors.indigo;
        },
    },

    methods: {
        resolveBarcodeDetector() {
            if (typeof window === 'undefined') return PolyfillBarcodeDetector;
            return window.BarcodeDetector || PolyfillBarcodeDetector || null;
        },

        getVideoConstraints() {
            if (this.selectedDeviceId) {
                return { deviceId: { exact: this.selectedDeviceId } };
            }

            return { facingMode: { ideal: 'environment' } };
        },

        stopMediaTracks(stream = this.mediaStream) {
            if (!stream) return;
            stream.getTracks().forEach((track) => track.stop());
        },

        stopVideoElementTracks() {
            const video = this.$refs.scannerVideo;
            if (!video) return;
            try { video.pause(); } catch { /* ignore */ }
            video.srcObject = null;
        },

        stopDetectionLoop() {
            if (this.scanFrameId) {
                cancelAnimationFrame(this.scanFrameId);
                this.scanFrameId = null;
            }
            this.scanFrameBusy = false;
        },

        clearScannerCanvas() {
            const canvas = this.$refs.scannerOverlay;
            if (!canvas) return;
            const ctx = canvas.getContext?.('2d');
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        },

        resizeScannerCanvas() {
            const canvas = this.$refs.scannerOverlay;
            const video = this.$refs.scannerVideo;
            if (!canvas || !video) return;

            const width = video.videoWidth || video.clientWidth || canvas.clientWidth;
            const height = video.videoHeight || video.clientHeight || canvas.clientHeight;
            if (!width || !height) return;

            if (canvas.width !== width) canvas.width = width;
            if (canvas.height !== height) canvas.height = height;
            canvas.style.width = '100%';
            canvas.style.height = '100%';
        },

        drawDetections(detectedCodes) {
            const canvas = this.$refs.scannerOverlay;
            if (!canvas) return;

            this.resizeScannerCanvas();
            const ctx = canvas.getContext?.('2d');
            if (!ctx) return;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (detectedCodes?.length) {
                this.paintOutline(detectedCodes, ctx);
            }
        },

        normalizeDecodedValue(rawValue, format) {
            const value = String(rawValue ?? '').trim();
            if (!value) return '';

            const normalizedFormat = String(format ?? '').toLowerCase();
            if (normalizedFormat !== 'code_128' && normalizedFormat !== 'code128') {
                return value;
            }

            const normalized = value
                .replace(/%01/gi, '-')
                .replace(/%22/gi, '-')
                .replace(/[^A-Za-z0-9-]/g, '')
                .replace(/-+/g, '-');

            if (!normalized.toUpperCase().startsWith('CBC-')) {
                const compact = normalized.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                if (!compact.startsWith('CBC')) return value;

                const digits = compact.slice(3).replace(/\D/g, '');
                if (digits.length < 8) return value;

                return `CBC-${digits.slice(0, 2)}-${digits.slice(2, 8)}`;
            }

            return normalized.replace(/([A-Z])-(\d{2})(\d{6})$/i, '$1-$2-$3');
        },

        paintOutline(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const [firstPoint, ...otherPoints] = detectedCode.cornerPoints;
                ctx.strokeStyle = this.showSuccessBorder ? '#10b981' : '#6366f1';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.moveTo(firstPoint.x, firstPoint.y);
                for (const { x, y } of otherPoints) ctx.lineTo(x, y);
                ctx.lineTo(firstPoint.x, firstPoint.y);
                ctx.closePath();
                ctx.stroke();
            }
            this.paintCenterText(detectedCodes, ctx);
        },

        paintCenterText(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const { boundingBox, rawValue } = detectedCode;
                const centerX = boundingBox.x + boundingBox.width / 2;
                const centerY = boundingBox.y + boundingBox.height / 2;
                const fontSize = Math.max(10, (50 * boundingBox.width) / ctx.canvas.width);

                ctx.font = `bold ${fontSize}px sans-serif`;
                ctx.textAlign = 'center';
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#1f2937';
                ctx.strokeText(rawValue, centerX, centerY);
                ctx.fillStyle = '#ffffff';
                ctx.fillText(rawValue, centerX, centerY);
            }
        },

        onDecode(detectedCodes) {
            if (!detectedCodes?.length) return;
            const firstDetection = detectedCodes[0];
            const code = this.normalizeDecodedValue(firstDetection.rawValue, firstDetection.format);
            if (!code) return;

            if (this._scanCooldown) return;
            this._scanCooldown = setTimeout(() => {
                this._scanCooldown = null;
            }, 1500);

            this.lastDecoded = code;
            this.showSuccessBorder = true;
            this.drawDetections(detectedCodes);

            this.$emit('decoded', code);

            if (this.beepEnabled && this.beep) {
                this.beep.play().catch(() => { });
            }

            setTimeout(() => {
                this.showSuccessBorder = false;
                if (this.beep) {
                    this.beep.pause();
                    this.beep.currentTime = 0;
                }
            }, 1000);
        },

        onError(error) {
            this.error = error;
            this.$emit('error', error);
        },

        async initializeCameras() {
            if (!this.enabled || !this.isOpen) return;

            try {
                this.stopAllStreams();
                this.error = null;
                this.isReady = false;

                if (!navigator?.mediaDevices) {
                    this.onError('Camera API not supported');
                    return;
                }

                const Detector = this.resolveBarcodeDetector();
                if (!Detector) {
                    this.onError('Barcode detection is not supported in this browser');
                    return;
                }

                this.barcodeDetector = new Detector({ formats: this.formats });

                // Request permission
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: this.getVideoConstraints(),
                    audio: false,
                });

                this.mediaStream = stream;

                await this.$nextTick();
                const video = this.$refs.scannerVideo;
                if (!video) {
                    this.onError('Scanner video element is unavailable');
                    return;
                }

                video.srcObject = stream;
                video.muted = true;
                video.playsInline = true;
                await video.play();

                // Get devices
                const allDevices = await navigator.mediaDevices.enumerateDevices();
                this.devices = allDevices
                    .filter(d => d.kind === 'videoinput')
                    .map((d, index) => ({
                        deviceId: d.deviceId,
                        label: d.label || `Camera ${index + 1}`,
                        index
                    }));

                // Keep the actual active device in sync with the stream when possible
                const activeDeviceId = stream.getVideoTracks?.()[0]?.getSettings?.()?.deviceId || null;
                if (activeDeviceId) {
                    this.selectedDeviceId = activeDeviceId;
                } else if (!this.selectedDeviceId && this.devices.length) {
                    this.selectedDeviceId = this.devices[0].deviceId;
                }

                this.isReady = true;
                this.$emit('ready', { devices: this.devices });

                this.resizeScannerCanvas();
                this.startDetectionLoop();

            } catch (error) {
                this.stopAllStreams();
                this.isReady = false;
                this.onError(error?.message || 'Failed to access camera');
            }
        },

        startDetectionLoop() {
            this.stopDetectionLoop();

            const tick = async () => {
                if (!this.isOpen || !this.enabled || !this.isReady) {
                    this.scanFrameId = null;
                    return;
                }

                const video = this.$refs.scannerVideo;
                if (!video || !this.barcodeDetector) {
                    this.scanFrameId = requestAnimationFrame(tick);
                    return;
                }

                if (video.readyState >= 2 && !this.scanFrameBusy && !this._scanCooldown) {
                    this.scanFrameBusy = true;
                    try {
                        const detectedCodes = await this.barcodeDetector.detect(video);
                        if (detectedCodes?.length) {
                            this.onDecode(detectedCodes);
                        } else {
                            this.clearScannerCanvas();
                        }
                    } catch (error) {
                        // Ignore transient frame detection failures; camera startup and permission
                        // errors are handled in initializeCameras().
                    } finally {
                        this.scanFrameBusy = false;
                    }
                }

                this.scanFrameId = requestAnimationFrame(tick);
            };

            this.scanFrameId = requestAnimationFrame(tick);
        },

        stopAllStreams() {
            this.stopDetectionLoop();
            this.stopVideoElementTracks();
            this.stopMediaTracks();
            this.mediaStream = null;
            this.barcodeDetector = null;
            this.clearScannerCanvas();
        },

        toggleScanner() {
            this.isOpen = !this.isOpen;
        },

        switchCamera() {
            if (!this.hasMultipleDevices) return;
            const currentIndex = this.devices.findIndex(d => d.deviceId === this.selectedDeviceId);
            const nextIndex = (currentIndex + 1) % this.devices.length;
            this.selectedDeviceId = this.devices[nextIndex].deviceId;
        },
    },

    beforeUnmount() {
        this.stopAllStreams();
        if (this.beep) {
            try {
                this.beep.pause();
                this.beep.currentTime = 0;
            } catch { /* ignore */ }
        }
        if (this._scanCooldown) {
            clearTimeout(this._scanCooldown);
            this._scanCooldown = null;
        }
    },

    mounted() {
        this.isClient = true;
        this.internalIsOpen = Boolean(this.modelValue);

        if (this.beepUrl && this.beepEnabled) {
            this.beep = new Audio(this.beepUrl);
            this.beep.load();
        }

        if (this.autoStart || this.defaultOpenSmall) {
            this.isOpen = true;
        }
    },

    watch: {
        modelValue: {
            immediate: true,
            handler(val) {
                this.internalIsOpen = Boolean(val);
            },
        },
        isOpen(val) {
            if (val) {
                this.$nextTick(() => this.initializeCameras());
                return;
            }

            this.stopAllStreams();
            this.isReady = false;
        },
        enabled(val) {
            if (!val) this.isOpen = false;
        },
        selectedDeviceId(newValue, oldValue) {
            if (!this.isOpen || !this.isReady || !newValue || newValue === oldValue) return;
            this.$nextTick(() => this.initializeCameras());
        }
    },
};
</script>

<template>
    <div class="camera-scanner gap-2 flex flex-col" :class="[`variant-${variant}`]">
        <div class="flex flex-row gap-1 w-full overflow-x-autor">
            <!-- Toggle Button -->
            <div v-if="showToggle" class="scanner-controls w-full">
                <button type="button" @click="toggleScanner" :disabled="!enabled"
                    class="w-full flex items-center justify-between px-3 py-2 rounded border transition-all duration-200"
                    :class="[
                        isOpen
                            ? 'bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-800 dark:text-indigo-300 h-full'
                            : 'bg-white border-gray-700 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700',
                        !enabled && 'opacity-50 cursor-not-allowed'
                    ]">
                    <span class="flex items-center gap-3">
                        <span class="p-2 rounded-lg transition-colors"
                            :class="isOpen ? 'bg-indigo-100 dark:bg-indigo-800' : 'bg-gray-100 dark:bg-gray-700'">
                            <ScanLine class="w-5 h-5 transition-transform duration-300"
                                :class="isOpen ? 'text-indigo-600 dark:text-indigo-400 scale-110' : 'text-gray-500 dark:text-gray-400'" />
                        </span>
                        <span class="text-left" :class="!isOpen? '':'hidden md:block'">
                            <span class="block text-sm font-medium">{{ displayLabel }}</span>
                            <span class="block text-xs opacity-75">
                                {{ isOpen ? 'Scanner active' : 'Click to enable scanner' }}
                            </span>
                        </span>
                    </span>

                    <span class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors duration-300"
                        :class="isOpen ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                        <span
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform duration-300"
                            :class="isOpen ? 'translate-x-6' : 'translate-x-1'" />
                    </span>
                </button>
            </div>

            <!-- Device Selection -->
            <transition name="scanner-slide">
                <div v-show="showDeviceSelect && isOpen && devices.length" class="device-select">
                    <div
                        class="relative flex items-center gap-2 px-3 py-1 h-full bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-900/20 rounded border dark:border-gray-700">
                        <Camera class="w-4 h-4 text-gray-400" />
                        <div class="flex-1 min-w-0">
                            <CustomDropdown
                                required
                                :value="selectedDeviceId"
                                :with-all-option="false"
                                :show-clear="false"
                                :placeholder="placeholder"
                                :options="devices.map(d => ({ label: d.label, name: d.deviceId }))"
                                @selectedChange="selectedDeviceId = $event"
                            />
                        </div>
                        <button v-if="hasMultipleDevices" @click="switchCamera"
                            class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors bg-indigo-700 group"
                            title="Switch camera">
                            <SwitchCamera class="w-4 h-4 text-gray-50 group-hover:text-indigo-700 group-active:rotate-180 duration-500" />
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Scanner Viewport -->
        <transition name="scanner-slide">
            <div v-show="isOpen"
                class="scanner-viewport relative overflow-hidden rounded-2xl border-2 transition-all duration-300"
                :class="[
                    borderColorClass,
                    showSuccessBorder ? 'border-emerald-500 shadow-emerald-500/30' : ''
                ]" :style="{ height: scannerHeight }">
                <!-- Active Scanner -->
                <div v-if="enabled && isClient" class="absolute inset-0">
                    <video
                        ref="scannerVideo"
                        class="w-full h-full object-cover"
                        autoplay
                        muted
                        playsinline
                        @loadedmetadata="resizeScannerCanvas"
                    ></video>
                    <canvas
                        ref="scannerOverlay"
                        class="absolute inset-0 w-full h-full pointer-events-none"
                    ></canvas>

                    <!-- Loading State -->
                    <div v-if="!isReady"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100/90 dark:bg-gray-800/90">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-3"></div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Initializing camera...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!mediaStream"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100/90 dark:bg-gray-800/90 text-center p-4">
                        <Camera class="w-12 h-12 text-gray-400 mb-2" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">No camera available</p>
                    </div>
                </div>

                <!-- Success Overlay -->
                <transition name="fade">
                    <div v-if="showSuccessBorder"
                        class="absolute inset-0 pointer-events-none flex items-center justify-center bg-emerald-500/10">
                        <div class="bg-white dark:bg-gray-800 rounded-full p-4 shadow-xl animate-bounce">
                            <CheckCircle2 class="w-8 h-8 text-emerald-500" />
                        </div>
                    </div>
                </transition>

                <!-- Corner Markers -->
                <div class="absolute inset-4 pointer-events-none">
                    <div class="absolute top-0 left-0 w-8 h-8 border-l-4 border-t-4 border-white/50 rounded-tl-lg">
                    </div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-r-4 border-t-4 border-white/50 rounded-tr-lg">
                    </div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-l-4 border-b-4 border-white/50 rounded-bl-lg">
                    </div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-r-4 border-b-4 border-white/50 rounded-br-lg">
                    </div>
                </div>

                <!-- Last Scanned -->
                <div v-if="lastDecoded"
                    class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-sm text-white text-center py-2 px-4 rounded-lg text-sm font-mono">
                    Last: {{ lastDecoded }}
                </div>
            </div>
        </transition>

        <!-- Error Message -->
        <transition name="fade">
            <div v-if="error"
                class="mt-3 flex items-center gap-2 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <AlertCircle class="w-5 h-5 flex-shrink-0" />
                <span class="text-sm">{{ error }}</span>
                <button @click="error = null" class="ml-auto text-xs underline hover:no-underline">
                    Dismiss
                </button>
            </div>
        </transition>

        <!-- Slot for custom content -->
        <slot name="footer" :lastDecoded="lastDecoded" :devices="devices" :isReady="isReady" />
    </div>
</template>

<style scoped>
.scanner-slide-enter-active,
.scanner-slide-leave-active {
    transition: all 0.3s ease;
    max-height: v-bind(scannerHeight);
    opacity: 1;
}

.scanner-slide-enter-from,
.scanner-slide-leave-to {
    max-height: 0;
    opacity: 0;
    margin: 0;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Variant styles */
.variant-compact .scanner-controls button {
    padding: 0.5rem 0.75rem;
}

.variant-compact .scanner-controls button > div:first-child > div {
    padding: 0.375rem;
}

.variant-minimal .scanner-controls {
    margin-bottom: 0.5rem;
}

.variant-minimal .device-select {
    display: none;
}
</style>
