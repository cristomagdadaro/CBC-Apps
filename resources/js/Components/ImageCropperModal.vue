<script setup>
import { ref, watch, onUnmounted, nextTick } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Cropper from "cropperjs";
import "cropperjs/dist/cropper.css";

const props = defineProps({
    show: Boolean,
    imageUrl: String,
});

const emit = defineEmits(["close", "cropped"]);

const imageRef = ref(null);
let cropper = null;

const initCropper = () => {
    if (cropper) {
        cropper.destroy();
    }
    if (imageRef.value && props.imageUrl) {
        cropper = new Cropper(imageRef.value, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: "move",
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }
};

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            nextTick(() => {
                setTimeout(initCropper, 200); // Wait for modal transition
            });
        } else {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }
    },
);

onUnmounted(() => {
    if (cropper) {
        cropper.destroy();
    }
});

const cropImage = () => {
    if (!cropper) return;

    // Show a loading state if needed, but this is fast
    cropper
        .getCroppedCanvas({
            width: 600,
            height: 600,
            fillColor: "#fff",
        })
        .toBlob(
            (blob) => {
                emit("cropped", blob);
                emit("close");
            },
            "image/jpeg",
            0.9,
        );
};
</script>

<template>
    <DialogModal
        :show="show"
        @close="$emit('close')"
        max-width="md">
        <template #title>Crop ID Photo</template>
        <template #content>
            <div
                class="w-full bg-slate-900 flex items-center justify-center overflow-hidden rounded-lg"
                style="height: 50vh">
                <img
                    v-if="imageUrl"
                    ref="imageRef"
                    :src="imageUrl"
                    class="block max-w-full"
                    alt="To be cropped" />
            </div>
            <p class="mt-3 text-sm text-slate-500 text-center">Drag the image to position your face in the center. Pinch or scroll to zoom.</p>
        </template>
        <template #footer>
            <SecondaryButton @click="$emit('close')">Cancel</SecondaryButton>
            <PrimaryButton
                class="ml-2"
                @click="cropImage">
                Crop & Save
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<style scoped>
/* Ensure the cropper container fits well */
.cropper-view-box,
.cropper-face {
    border-radius: 50%;
}
</style>
