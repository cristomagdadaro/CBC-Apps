export default {
    data() {
        return {
            selectedScanner: "webcam",
            selectedDevice: null,
            devices: [],
            error: null,
        }
    },
    methods: {
        cameraChange(deviceInfo) {
            this.selectedDevice = this.devices.find(option => option.deviceId === deviceInfo.deviceId);
        },
    }
}
