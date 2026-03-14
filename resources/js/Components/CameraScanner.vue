<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import { 
  ScanLine, 
  Camera, 
  SwitchCamera, 
  Volume2, 
  VolumeX,
  AlertCircle,
  CheckCircle2
} from 'lucide-vue-next';

export default {
    name: 'CameraScanner',
    components: { 
        QrcodeStream,
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
        
        // Styling
        scannerHeight: {
            type: String,
            default: '300px',
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
            beep: null,
            isReady: false,
            _scanCooldown: null,
            isClient: false,
        };
    },
    
    computed: {
        isOpen: {
            get() {
                return this.modelValue;
            },
            set(val) {
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
        // ... keep existing methods: stopMediaTracks, stopVideoElementTracks, stopAllStreams ...
        
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
            const code = detectedCodes[0].rawValue;
            if (!code) return;
            
            if (this._scanCooldown) return;
            this._scanCooldown = setTimeout(() => { 
                this._scanCooldown = null; 
            }, 1500);
            
            this.lastDecoded = code;
            this.showSuccessBorder = true;
            
            this.$emit('decoded', code);
            
            if (this.beepEnabled && this.beep) {
                this.beep.play().catch(() => {});
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
                if (!navigator?.mediaDevices) {
                    throw new Error('Camera API not supported');
                }
                
                // Request permission
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                
                // Get devices
                const allDevices = await navigator.mediaDevices.enumerateDevices();
                this.devices = allDevices
                    .filter(d => d.kind === 'videoinput')
                    .map((d, index) => ({
                        deviceId: d.deviceId,
                        label: d.label || `Camera ${index + 1}`,
                        index
                    }));
                
                // Select first device if none selected
                if (!this.selectedDeviceId && this.devices.length) {
                    this.selectedDeviceId = this.devices[0].deviceId;
                }
                
                this.isReady = true;
                this.$emit('ready', { devices: this.devices });
                
                // Stop the initial stream
                stream.getTracks().forEach(t => t.stop());
                
            } catch (error) {
                this.onError(error.message || 'Failed to access camera');
            }
        },
        
        toggleScanner() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => this.initializeCameras());
            }
        },
        
        switchCamera() {
            if (!this.hasMultipleDevices) return;
            const currentIndex = this.devices.findIndex(d => d.deviceId === this.selectedDeviceId);
            const nextIndex = (currentIndex + 1) % this.devices.length;
            this.selectedDeviceId = this.devices[nextIndex].deviceId;
        },
    },
    
    mounted() {
        this.isClient = true;
        if (this.beepUrl && this.beepEnabled) {
            this.beep = new Audio(this.beepUrl);
            this.beep.load();
        }
        if (this.autoStart) {
            this.isOpen = true;
            this.initializeCameras();
        }
    },
    
    watch: {
        isOpen(val) {
            if (val) this.initializeCameras();
        },
        enabled(val) {
            if (!val) this.isOpen = false;
        }
    },
};
</script>

<template>
    <div class="camera-scanner" :class="[`variant-${variant}`]">
        <!-- Toggle Button -->
        <div v-if="showToggle" class="scanner-controls mb-3">
            <button 
                type="button"
                @click="toggleScanner"
                :disabled="!enabled"
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl border transition-all duration-200"
                :class="[
                    isOpen 
                        ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-800 dark:text-indigo-300' 
                        : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700',
                    !enabled && 'opacity-50 cursor-not-allowed'
                ]"
            >
                <div class="flex items-center gap-3">
                    <div 
                        class="p-2 rounded-lg transition-colors"
                        :class="isOpen ? 'bg-indigo-100 dark:bg-indigo-800' : 'bg-gray-100 dark:bg-gray-700'"
                    >
                        <ScanLine 
                            class="w-5 h-5 transition-transform duration-300"
                            :class="isOpen ? 'text-indigo-600 dark:text-indigo-400 scale-110' : 'text-gray-500 dark:text-gray-400'"
                        />
                    </div>
                    <div class="text-left">
                        <span class="block text-sm font-medium">{{ displayLabel }}</span>
                        <span class="block text-xs opacity-75">
                            {{ isOpen ? 'Scanner active' : 'Click to enable scanner' }}
                        </span>
                    </div>
                </div>
                
                <div 
                    class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors duration-300"
                    :class="isOpen ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                >
                    <span 
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform duration-300"
                        :class="isOpen ? 'translate-x-6' : 'translate-x-1'"
                    />
                </div>
            </button>
        </div>

        <!-- Device Selection -->
        <div v-if="showDeviceSelect && isOpen && devices.length" class="device-select mb-3">
            <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
                <Camera class="w-4 h-4 text-gray-400" />
                <select 
                    v-model="selectedDeviceId"
                    class="flex-1 bg-transparent text-sm text-gray-700 dark:text-gray-300 outline-none cursor-pointer"
                >
                    <option 
                        v-for="device in devices" 
                        :key="device.deviceId" 
                        :value="device.deviceId"
                    >
                        {{ device.label }}
                    </option>
                </select>
                <button 
                    v-if="hasMultipleDevices"
                    @click="switchCamera"
                    class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Switch camera"
                >
                    <SwitchCamera class="w-4 h-4 text-gray-500" />
                </button>
            </div>
        </div>

        <!-- Scanner Viewport -->
        <transition name="scanner-slide">
            <div 
                v-show="isOpen" 
                class="scanner-viewport relative overflow-hidden rounded-2xl border-2 transition-all duration-300"
                :class="[
                    borderColorClass,
                    showSuccessBorder ? 'border-emerald-500 shadow-emerald-500/30' : ''
                ]"
                :style="{ height: scannerHeight }"
            >
                <!-- Active Scanner -->
                <QrcodeStream
                    v-if="enabled && selectedDevice && isClient"
                    :constraints="{ deviceId: selectedDevice.deviceId }"
                    :track="paintOutline"
                    :formats="formats"
                    @error="onError"
                    @detect="onDecode"
                    class="w-full h-full object-cover"
                />
                
                <!-- Loading State -->
                <div v-else-if="!isReady" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-3"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Initializing camera...</p>
                </div>
                
                <!-- Empty State -->
                <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 text-center p-4">
                    <Camera class="w-12 h-12 text-gray-400 mb-2" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">No camera available</p>
                </div>

                <!-- Success Overlay -->
                <transition name="fade">
                    <div 
                        v-if="showSuccessBorder" 
                        class="absolute inset-0 pointer-events-none flex items-center justify-center bg-emerald-500/10"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-full p-4 shadow-xl animate-bounce">
                            <CheckCircle2 class="w-8 h-8 text-emerald-500" />
                        </div>
                    </div>
                </transition>

                <!-- Corner Markers -->
                <div class="absolute inset-4 pointer-events-none">
                    <div class="absolute top-0 left-0 w-8 h-8 border-l-4 border-t-4 border-white/50 rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-r-4 border-t-4 border-white/50 rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-l-4 border-b-4 border-white/50 rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-r-4 border-b-4 border-white/50 rounded-br-lg"></div>
                </div>

                <!-- Last Scanned -->
                <div 
                    v-if="lastDecoded" 
                    class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-sm text-white text-center py-2 px-4 rounded-lg text-sm font-mono"
                >
                    Last: {{ lastDecoded }}
                </div>
            </div>
        </transition>

        <!-- Error Message -->
        <transition name="fade">
            <div 
                v-if="error" 
                class="mt-3 flex items-center gap-2 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300"
            >
                <AlertCircle class="w-5 h-5 flex-shrink-0" />
                <span class="text-sm">{{ error }}</span>
                <button 
                    @click="error = null"
                    class="ml-auto text-xs underline hover:no-underline"
                >
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