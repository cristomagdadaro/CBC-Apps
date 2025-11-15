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
                aztec: true,
                code_128: true,
                code_39: true,
                code_93: true,
                codabar: true,
                databar: true,
                databar_expanded: true,
                data_matrix: true,
                dx_film_edge: true,
                ean_13: true,
                ean_8: true,
                itf: true,
                maxi_code: true,
                micro_qr_code: true,
                pdf417: true,
                qr_code: true,
                rm_qr_code: true,
                upc_a: true,
                upc_e: true,
                linear_codes: true,
                matrix_codes: true,
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
        };
    },
    computed: {
        selectedBarcodeFormats() {
            return Object.keys(this.formats).filter((format) => this.formats[format]);
        },
        hasDevice() {
            return !!this.selectedDeviceId;
        },
        selectedDevice() {
            return this.devices.find(d => d.deviceId === this.selectedDeviceId) || null;
        },
    },
    methods: {
        stopMediaTracks() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }
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
                ctx.strokeStyle = '#35495e';
                ctx.strokeText(detectedCode.rawValue, centerX, centerY);

                ctx.fillStyle = '#ffffff';
                ctx.fillText(rawValue, centerX, centerY);
            }
        },
        paintOutline(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const [firstPoint, ...otherPoints] = detectedCode.cornerPoints;

                ctx.strokeStyle = 'red';

                ctx.beginPath();
                ctx.moveTo(firstPoint.x, firstPoint.y);
                for (const { x, y } of otherPoints) {
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(firstPoint.x, firstPoint.y);
                ctx.closePath();
                ctx.stroke();
            }

            this.paintCenterText(detectedCodes, ctx);
        },
        onDecode(detectedCodes) {
            if (!detectedCodes || !detectedCodes.length) return;

            const code = detectedCodes[0].rawValue;
            if (!code || code === this.lastDecoded) return;

            this.lastDecoded = code;
            this.$emit('decoded', code);

            if (this.beep) {
                this.beep.play().catch(() => {});
            }
            this.showBorder = true;
            setTimeout(() => {
                if (this.beep) {
                    this.beep.pause();
                    this.beep.currentTime = 0;
                }
                this.showBorder = false;
            }, 1000);
        },
        onError(error) {
            this.error = error;
        },
        async setupMediaSource() {
            if (!this.enabled) return;

            try {
                const permissionObj = await navigator.permissions.query({ name: 'camera' });
                if (permissionObj.state === 'granted' || permissionObj.state === 'prompt') {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                    });

                    this.devices = (await navigator.mediaDevices.enumerateDevices())
                        .filter((device) => device.kind === 'videoinput')
                        .map((device) => ({
                            deviceId: device.deviceId,
                            label: device.label,
                        }));
                    if (!this.selectedDeviceId && this.devices.length) {
                        this.selectedDeviceId = this.devices[0].deviceId;
                    }
                } else if (permissionObj.state === 'denied') {
                    this.error = 'Camera access denied';
                }
            } catch (error) {
                this.error = error;
            }
        },
        onDeviceChange(event) {
            const id = event;
            if (id && id !== this.selectedDeviceId) {
                this.selectedDeviceId = id;
            }
        },
    },
    mounted() {
        this.beep = new Audio(this.beepUrl);
        this.beep.load();
        this.setupMediaSource();
    },
    unmounted() {
        this.stopMediaTracks();
    },
    watch: {
        error(newError) {
            if (newError) {
                this.$emit('error', newError);
            }
        },
        enabled(newVal) {
            if (newVal) {
                this.setupMediaSource();
            } else {
                this.stopMediaTracks();
            }
        },
    },
};
</script>

<template>
    <div class="flex flex-col gap-2 w-full max-w-xl">
        <div v-if="devices.length" class="flex flex-col gap-1">
            <label class="text-gray-600 text-center text-sm whitespace-nowrap">{{ label }}</label>
            <custom-dropdown
                required
                searchable
                :with-all-option="false"
                :value="selectedDeviceId"
                :options="devices.map((device) => ({ name: device.deviceId, label: device.label || 'Camera ' + (devices.indexOf(device) + 1) }))"
                placeholder="Select Camera a Device"
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
</template>

<style scoped>
</style>
