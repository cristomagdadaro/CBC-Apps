<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    eventId: {
        type: String,
        default: null,
    },
    template: {
        type: Object,
        default: null,
    },
});

const fileRef = ref(null);
const uploading = ref(false);
const generating = ref(false);
const message = ref('');
const errorMessage = ref('');
const uploadedTemplateName = ref('');

const templateName = computed(() => uploadedTemplateName.value || props.template?.template_name || 'No template uploaded');

const onFileChange = (event) => {
    errorMessage.value = '';
    message.value = '';
    fileRef.value = event.target.files?.[0] || null;
};

const uploadTemplate = async () => {
    if (!props.eventId || !fileRef.value) {
        errorMessage.value = 'Please select a template file.';
        return;
    }

    uploading.value = true;
    errorMessage.value = '';
    message.value = '';

    try {
        const formData = new FormData();
        formData.append('template', fileRef.value);

        await axios.post(route('api.event.certificates.template.upload', props.eventId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        message.value = 'Template uploaded successfully.';
        uploadedTemplateName.value = fileRef.value?.name || '';
        fileRef.value = null;
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Failed to upload template.';
    } finally {
        uploading.value = false;
    }
};

const generateCertificates = async () => {
    if (!props.eventId) {
        errorMessage.value = 'Event ID is missing.';
        return;
    }

    generating.value = true;
    errorMessage.value = '';
    message.value = '';

    try {
        const response = await axios.post(route('api.event.certificates.generate', props.eventId), {}, {
            responseType: 'blob',
        });

        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `certificates-${props.eventId}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        message.value = 'Certificates generated successfully.';
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Failed to generate certificates.';
    } finally {
        generating.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">eCertificate Template</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Upload an HTML template containing placeholders like {{ name }}, {{ event_id }}, {{ email }}, {{ date }}, {{ form_type }}.
            </p>

            <div class="mt-4 flex flex-col gap-3">
                <div class="text-sm text-gray-700 dark:text-gray-200">
                    Current template: <span class="font-semibold">{{ templateName }}</span>
                </div>

                <input type="file" accept=".html,.htm" @change="onFileChange" />

                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-fit"
                    :disabled="uploading"
                    @click="uploadTemplate"
                >
                    <span v-if="!uploading">Upload Template</span>
                    <span v-else>Uploading...</span>
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Mass Generate Certificates</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Generates a ZIP file containing certificates for all respondents of this event.
            </p>

            <button
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded"
                :disabled="generating"
                @click="generateCertificates"
            >
                <span v-if="!generating">Generate & Download</span>
                <span v-else>Generating...</span>
            </button>
        </div>

        <div v-if="message" class="text-green-600 text-sm">
            {{ message }}
        </div>
        <div v-if="errorMessage" class="text-red-600 text-sm">
            {{ errorMessage }}
        </div>
    </div>
</template>
