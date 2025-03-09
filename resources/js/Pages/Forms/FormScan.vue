<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { BrowserMultiFormatReader } from "@zxing/browser";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const selectedCamera = ref("");
const availableCameras = ref([]);
const result = ref("");
const videoElement = ref(null);
const codeReader = new BrowserMultiFormatReader();

const getCameras = async () => {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        availableCameras.value = devices
            .filter(device => device.kind === "videoinput")
            .map(device => ({
                id: device.deviceId,
                label: device.label || `Camera ${availableCameras.value.length + 1}`
            }));

        if (availableCameras.value.length > 0) {
            selectedCamera.value = availableCameras.value[0].id; // Select the first camera by default
        }
    } catch (error) {
        console.error("Error getting cameras:", error);
    }
};

const startScanning = async () => {
    if (!selectedCamera.value) return;

    try {
        result.value = ""; // Clear previous results
        await codeReader.decodeFromVideoDevice(
            selectedCamera.value,
            videoElement.value,
            (res) => {
                if (res) {
                    result.value = res.text;
                    codeReader.reset(); // Stop scanning after success
                }
            }
        );
    } catch (error) {
        console.error("Scanning error:", error);
    }
};

// Watch for camera selection change
watch(selectedCamera, () => {
    startScanning();
});

onMounted(async () => {
    await getCameras();
    await startScanning();
});
</script>

<template>
    <AppLayout title="Scan Attendance Forms">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="scanner-container">
                    <label for="camera-select">Select Camera:</label>
                    <select v-model="selectedCamera">
                        <option v-for="camera in availableCameras" :key="camera.id" :value="camera.id">
                            {{ camera.label }}
                        </option>
                    </select>

                    <video ref="videoElement" class="video-preview" autoplay></video>

                    <p v-if="result">Scanned QR Code: <strong>{{ result }}</strong></p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scanner-container {
    text-align: center;
}
.video-preview {
    width: 100%;
    max-width: 500px;
    border: 2px solid #ddd;
    border-radius: 8px;
    margin-top: 10px;
}
</style>
