<script>
import { QrcodeStream } from "vue-qrcode-reader";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";

export default {
    name: "Scanner",
    components: {FilterIcon, CustomDropdown, QrcodeStream },
    props: {
        selectedDevice: { type: Object, default: null },
    },
    data() {
        return {
            error: null,
            decode: null,
            showBorder: false,
            stream: null,
            devices: [],
            barcodeFormats: {
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
                matrix_codes: true
            },
            beep: new Audio('/misc/audio/beep3-98810.mp3'),
            trackFunctionSelected: { label: 'outline', name: this.paintOutline },
        };
    },
    computed: {
        selectedBarcodeFormats() {
            return Object.keys(this.barcodeFormats).filter((format) => this.barcodeFormats[format])
        }
    },
    methods: {
        stopMediaTracks(){
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
        },
        paintCenterText(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const { boundingBox, rawValue } = detectedCode

                const centerX = boundingBox.x + boundingBox.width / 2
                const centerY = boundingBox.y + boundingBox.height / 2

                const fontSize = Math.max(10, (50 * boundingBox.width) / ctx.canvas.width)

                ctx.font = `bold ${fontSize}px sans-serif`
                ctx.textAlign = 'center'

                ctx.lineWidth = 3
                ctx.strokeStyle = '#35495e'
                ctx.strokeText(detectedCode.rawValue, centerX, centerY)

                ctx.fillStyle = '#ffffff'
                ctx.fillText(rawValue, centerX, centerY)
            }
        },
        paintOutline(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const [firstPoint, ...otherPoints] = detectedCode.cornerPoints

                ctx.strokeStyle = 'red'

                ctx.beginPath()
                ctx.moveTo(firstPoint.x, firstPoint.y)
                for (const { x, y } of otherPoints) {
                    ctx.lineTo(x, y)
                }
                ctx.lineTo(firstPoint.x, firstPoint.y)
                ctx.closePath()
                ctx.stroke()
            }

            this.paintCenterText(detectedCodes, ctx);
        },
        onDecode(code) {
            if (code === this.decode) return;
                this.$emit('decoded', code[0].rawValue);

            this.beep.play();
            this.showBorder = true;
            setTimeout(() => {
                this.beep.pause();
                this.beep.currentTime = 0;
                this.showBorder = false;
            }, 1000);
        },
        onError(error) {
            this.error = error;
        },
        async setupMediaSource() {
            navigator.permissions.query({name: 'camera'})
                .then(async (permissionObj) => {
                    if (permissionObj.state === 'granted' || permissionObj.state === 'prompt') {
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: 'environment'
                                }
                            });

                            this.devices = (await navigator.mediaDevices.enumerateDevices())
                                .filter((device) => device.kind === 'videoinput')
                                .map((device) => ({
                                    deviceId: device.deviceId,
                                    label: device.label
                                }));

                            this.$emit('detectedDevices', this.devices);
                        } catch (error) {
                            this.error = error;
                        }
                    } else if (permissionObj.state === 'denied') {
                        this.error = 'Camera access denied';
                    }
                })
                .catch((error) => {
                    this.error = error;
                })
        },
    },
    mounted() {
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
        selectedDevice(newDevice) {
            if (newDevice) {
                this.stopMediaTracks();
            }
        },
    }
}
</script>

<template>
    <QrcodeStream
        :constraints="{ deviceId: selectedDevice?.deviceId }"
        :track="paintOutline"
        :formats="selectedBarcodeFormats"
        @error="onError"
        @detect="onDecode"
        v-if="!!selectedDevice"
    />
    <div v-else class="text-center">
        No Scanner is selected
    </div>
</template>

<style scoped>

</style>
