<script>
import ApiMixin from '@/Modules/mixins/ApiMixin';

export default {
    mixins: [ApiMixin],
    props: {
        eventId: {
            type: String,
            default: null,
        },
        template: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            fileRef: null,
            uploading: false,
            generating: false,
            message: '',
            errorMessage: '',
            uploadedTemplateName: '',
            outputFormat: 'pdf',
        };
    },
    computed: {
        templateName() {
            return this.uploadedTemplateName || this.template?.template_name || 'No template uploaded';
        },
    },
    methods: {
        async resolveErrorMessage(error) {
            const fallback = 'Failed to generate certificates.';

            if (!error?.response) {
                return error?.message || fallback;
            }

            const data = error.response.data;
            if (data instanceof Blob) {
                try {
                    const text = await data.text();
                    const parsed = JSON.parse(text);
                    return parsed?.message || fallback;
                } catch {
                    return fallback;
                }
            }

            return data?.message || fallback;
        },
        onFileChange(event) {
            this.errorMessage = '';
            this.message = '';
            this.fileRef = event.target.files?.[0] || null;
        },
        async uploadTemplate() {
            if (!this.eventId || !this.fileRef) {
                this.errorMessage = 'Please select a template file.';
                return;
            }

            this.uploading = true;
            this.errorMessage = '';
            this.message = '';

            try {
                const formData = new FormData();
                formData.append('template', this.fileRef);

                await this.fetchPostApi('api.event.certificates.template.upload', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    routeParams: this.eventId,
                });

                this.message = 'Template uploaded successfully.';
                this.uploadedTemplateName = this.fileRef?.name || '';
                this.fileRef = null;
            } catch (error) {
                this.errorMessage = error?.response?.data?.message || 'Failed to upload template.';
            } finally {
                this.uploading = false;
            }
        },
        async generateCertificates() {
            if (!this.eventId) {
                this.errorMessage = 'Event ID is missing.';
                return;
            }

            this.generating = true;
            this.errorMessage = '';
            this.message = '';

            try {
                const response = await this.fetchPostApi('api.event.certificates.generate', {
                    format: this.outputFormat,
                }, {
                    responseType: 'blob',
                    routeParams: this.eventId,
                });

                const blob = new Blob([response.data], { type: 'application/zip' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `certificates-${this.eventId}.zip`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);

                this.message = 'Certificates generated successfully.';
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error);
            } finally {
                this.generating = false;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">eCertificate Template</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Upload a PPTX template containing placeholders like &lt;&lt;name&gt;&gt;, &lt;&lt;event_id&gt;&gt;, &lt;&lt;email&gt;&gt;, &lt;&lt;date&gt;&gt;, &lt;&lt;form_type&gt;&gt;.
            </p>

            <div class="mt-4 flex flex-col gap-3">
                <div class="text-sm text-gray-700 dark:text-gray-200">
                    Current template: <span class="font-semibold">{{ templateName }}</span>
                </div>

                <input type="file" accept=".pptx" @change="onFileChange" />

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

            <div class="flex flex-col gap-2">
                <label class="text-xs text-gray-500 dark:text-gray-400">Output format</label>
                <select v-model="outputFormat" class="border border-gray-300 rounded px-3 py-2 w-fit">
                    <option value="pdf">PDF</option>
                    <option value="png">PNG</option>
                    <option value="jpg">JPG</option>
                    <option value="pptx">PPTX</option>
                </select>
            </div>

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
