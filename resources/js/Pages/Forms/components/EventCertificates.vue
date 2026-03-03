<script>
import ApiMixin from '@/Modules/mixins/ApiMixin';

export default {
    name: 'EventCertificates',
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
            templateFile: null,
            dataFile: null,
            useSavedTemplate: true,
            useEventData: true,
            responseColumns: [],
            subformTypes: [],
            selectedNameColumn: '',
            selectedEmailColumn: '',
            selectedSubformType: '',
            loadingColumns: false,
            savedTemplate: this.template,
            uploadProgress: 0,
            uploading: false,
            processingOnServer: false,
            message: '',
            errorMessage: '',
            outputFormat: 'pdf',
            namingTemplate: '{Fullname}_{date}',
            batchId: null,
            poller: null,
            serverStatus: 'idle',
            maxFileSizeBytes: 10 * 1024 * 1024,
        };
    },
    computed: {
        hasSavedTemplate() {
            return !!this.savedTemplate?.template_path;
        },
        savedTemplateName() {
            return this.savedTemplate?.template_name || 'template.pptx';
        },
        hasSelectedTemplateFile() {
            return !!this.templateFile;
        },
    },
    watch: {
        eventId: {
            immediate: true,
            handler() {
                this.fetchResponseColumns();
            },
        },
        template: {
            immediate: true,
            handler(value) {
                this.savedTemplate = value;
                if (!value?.template_path) {
                    this.useSavedTemplate = false;
                }
            },
        },
        useSavedTemplate(value) {
            if (value) {
                this.templateFile = null;
            }
        },
        useEventData(value) {
            if (value) {
                this.dataFile = null;
            }
        },
    },
    beforeUnmount() {
        this.stopPolling();
    },
    methods: {
        resetMessages() {
            this.message = '';
            this.errorMessage = '';
        },
        validateFile(file, expectedType) {
            if (!file) {
                return 'File is required.';
            }

            if (file.size > this.maxFileSizeBytes) {
                return 'File size must be 10MB or lower.';
            }

            const name = file.name.toLowerCase();
            if (expectedType === 'template' && !name.endsWith('.pptx')) {
                return 'Template file must be a .pptx file.';
            }

            if (expectedType === 'data' && !(name.endsWith('.xlsx') || name.endsWith('.csv'))) {
                return 'Data file must be a .xlsx or .csv file.';
            }

            return null;
        },
        setFile(type, file) {
            const error = this.validateFile(file, type);
            if (error) {
                this.errorMessage = error;
                return;
            }

            if (type === 'template') {
                this.templateFile = file;
            } else {
                this.dataFile = file;
            }

            this.errorMessage = '';
        },
        onFileInput(type, event) {
            this.resetMessages();
            const file = event.target.files?.[0] || null;
            this.setFile(type, file);
        },
        viewSavedTemplate() {
            if (!this.eventId) {
                this.errorMessage = 'Event ID is missing.';
                return;
            }

            const url = route('api.event.certificates.template.view', [this.eventId]);
            window.open(url, '_blank');
        },
        viewSelectedTemplate() {
            if (!this.templateFile) {
                this.errorMessage = 'Select a template file first.';
                return;
            }

            const objectUrl = URL.createObjectURL(this.templateFile);
            window.open(objectUrl, '_blank');
            window.setTimeout(() => URL.revokeObjectURL(objectUrl), 60_000);
        },
        async fetchResponseColumns() {
            if (!this.eventId) {
                return;
            }

            this.loadingColumns = true;

            try {
                const response = await this.fetchGetApi('api.event.certificates.columns', {
                    routeParams: this.eventId,
                });

                const payload = response?.data || {};
                this.responseColumns = Array.isArray(payload?.columns) ? payload.columns : [];
                this.subformTypes = Array.isArray(payload?.subform_types) ? payload.subform_types : [];

                this.savedTemplate = payload?.template || null;

                if (!this.selectedEmailColumn) {
                    const emailMatch = this.responseColumns.find((column) => /email/i.test(String(column)));
                    this.selectedEmailColumn = emailMatch || '';
                }

                if (!this.selectedNameColumn) {
                    const nameMatch = this.responseColumns.find((column) => /full\s*name|fullname|name/i.test(String(column)));
                    this.selectedNameColumn = nameMatch || '';
                }

                if (!this.hasSavedTemplate) {
                    this.useSavedTemplate = false;
                }
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to fetch response_data columns.');
            } finally {
                this.loadingColumns = false;
            }
        },
        onDrop(type, event) {
            this.resetMessages();
            const file = event.dataTransfer?.files?.[0] || null;
            this.setFile(type, file);
        },
        async resolveErrorMessage(error, fallback = 'Certificate request failed.') {
            if (!error?.response) {
                return error?.message || fallback;
            }

            if (error?.response?.status === 413) {
                return 'Request is too large. Use Saved Template/Event Data to avoid uploading files, or reduce file size and server upload limits.';
            }

            if (error?.response?.status === 422) {
                return error?.response?.data?.message || 'Validation failed. Please review your files and fields.';
            }

            const responseData = error.response.data;
            if (responseData instanceof Blob) {
                try {
                    const text = await responseData.text();
                    const parsed = JSON.parse(text);
                    return parsed?.message || parsed?.error || fallback;
                } catch {
                    return fallback;
                }
            }

            return responseData?.message || responseData?.error || fallback;
        },
        async submitForProcessing() {
            if (!this.eventId) {
                this.errorMessage = 'Event ID is missing.';
                return;
            }

            const templateError = this.validateFile(this.templateFile, 'template');

            if (!this.useSavedTemplate && templateError) {
                this.errorMessage = templateError;
                return;
            }

            if (!this.useEventData) {
                const dataError = this.validateFile(this.dataFile, 'data');
                if (dataError) {
                    this.errorMessage = dataError;
                    return;
                }
            }

            if (this.useEventData && (!this.selectedEmailColumn || !this.selectedNameColumn)) {
                this.errorMessage = 'Select both name and email columns from response_data.';
                return;
            }

            this.resetMessages();
            this.uploadProgress = 0;
            const shouldUploadTemplate = !this.useSavedTemplate && !!this.templateFile;
            const shouldUploadData = !this.useEventData && !!this.dataFile;
            const hasFileUpload = shouldUploadTemplate || shouldUploadData;
            this.uploading = hasFileUpload;

            if (!hasFileUpload) {
                this.processingOnServer = true;
                this.message = 'Submitting request...';
            }

            try {
                let payload;
                let requestConfig;

                if (hasFileUpload) {
                    const formData = new FormData();

                    if (shouldUploadTemplate) {
                        formData.append('template', this.templateFile);
                    }

                    if (shouldUploadData) {
                        formData.append('data', this.dataFile);
                    }

                    formData.append('format', this.outputFormat);
                    formData.append('use_saved_template', this.useSavedTemplate ? '1' : '0');
                    formData.append('use_event_data', this.useEventData ? '1' : '0');

                    if (this.useEventData) {
                        formData.append('name_column', this.selectedNameColumn);
                        formData.append('email_column', this.selectedEmailColumn);
                        if (this.selectedSubformType) {
                            formData.append('subform_type', this.selectedSubformType);
                        }
                    }

                    if (this.namingTemplate?.trim()) {
                        formData.append('name_template', this.namingTemplate.trim());
                    }

                    payload = formData;
                    requestConfig = {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        routeParams: this.eventId,
                        onUploadProgress: (progressEvent) => {
                            const total = progressEvent.total || 0;
                            if (total > 0) {
                                this.uploadProgress = Math.round((progressEvent.loaded / total) * 100);
                            }
                        },
                    };
                } else {
                    payload = {
                        format: this.outputFormat,
                        use_saved_template: this.useSavedTemplate,
                        use_event_data: this.useEventData,
                        ...(this.useEventData
                            ? {
                                  name_column: this.selectedNameColumn,
                                  email_column: this.selectedEmailColumn,
                                  ...(this.selectedSubformType ? { subform_type: this.selectedSubformType } : {}),
                              }
                            : {}),
                        ...(this.namingTemplate?.trim() ? { name_template: this.namingTemplate.trim() } : {}),
                    };

                    requestConfig = {
                        routeParams: this.eventId,
                    };
                }

                const response = await this.fetchPostApi('api.event.certificates.generate', payload, requestConfig);

                this.batchId = response?.data?.data?.batch_id || null;
                this.serverStatus = 'queued';
                this.processingOnServer = true;
                this.message = hasFileUpload
                    ? 'Upload complete. Processing on Server...'
                    : 'Request submitted. Processing on Server...';
                this.startPolling();
            } catch (error) {
                this.processingOnServer = false;
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to submit certificate generation request.');
            } finally {
                this.uploading = false;
            }
        },
        startPolling() {
            this.stopPolling();

            if (!this.batchId) {
                return;
            }

            this.poller = window.setInterval(async () => {
                await this.fetchStatus();
            }, 3000);

            this.fetchStatus();
        },
        stopPolling() {
            if (this.poller) {
                window.clearInterval(this.poller);
                this.poller = null;
            }
        },
        async fetchStatus() {
            if (!this.batchId || !this.eventId) {
                return;
            }

            try {
                const response = await this.fetchGetApi('api.event.certificates.status', {
                    routeParams: [this.eventId, this.batchId],
                });

                const data = response?.data || {};
                this.serverStatus = data?.status || 'processing';

                if (data?.status === 'failed') {
                    this.processingOnServer = false;
                    this.stopPolling();
                    this.errorMessage = data?.error || data?.message || 'Server processing failed.';
                }

                if (data?.status === 'completed') {
                    this.processingOnServer = false;
                    this.stopPolling();
                    const summary = data?.summary || {};
                    this.message = `Processing complete. Success: ${summary.success ?? 0}, Failed: ${summary.fail ?? 0}.`;
                }
            } catch (error) {
                this.processingOnServer = false;
                this.stopPolling();
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to fetch processing status.');
            }
        },
        downloadZip() {
            if (!this.eventId || !this.batchId || this.serverStatus !== 'completed') {
                return;
            }

            window.location.href = route('api.event.certificates.download', [this.eventId, this.batchId]);
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <h3 class="text-normal font-bold text-gray-500 dark:text-gray-400">Bulk Certificate Generator</h3>
            
            <div class="mt-4 p-3 border border-gray-200 rounded bg-gray-50 dark:bg-gray-900/20">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input v-model="useSavedTemplate" type="checkbox" :disabled="!hasSavedTemplate" />
                    Use saved template for this event
                </label>
                <p v-if="hasSavedTemplate" class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    Saved template: {{ savedTemplateName }}
                </p>
                <div v-if="hasSavedTemplate" class="mt-2">
                    <button
                        class="text-xs px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white"
                        @click="viewSavedTemplate"
                    >
                        View Saved Template
                    </button>
                </div>
                <p v-else class="mt-1 text-xs text-amber-600">No event-specific template found. The system default template will be used unless you upload a new one.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div
                    v-if="!useSavedTemplate"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                    @dragover.prevent
                    @drop.prevent="onDrop('template', $event)"
                >
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Template (.pptx)</p>
                    <p class="text-xs text-gray-500 mt-1">Upload a new template to replace the saved event template (optional).</p>
                    <input type="file" accept=".pptx" class="mt-3 w-full" @change="onFileInput('template', $event)" />
                    <p v-if="templateFile" class="mt-2 text-xs text-gray-600">{{ templateFile.name }}</p>
                    <div v-if="hasSelectedTemplateFile" class="mt-2">
                        <button
                            class="text-xs px-3 py-1 rounded bg-slate-600 hover:bg-slate-700 text-white"
                            @click="viewSelectedTemplate"
                        >
                            View Selected Template
                        </button>
                    </div>
                </div>

                <div
                    v-if="!useEventData"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                    @dragover.prevent
                    @drop.prevent="onDrop('data', $event)"
                >
                    <div class="flex items-center justify-center gap-2">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Data Upload (.xlsx/.csv)</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Optional when using event response_data columns.</p>
                    <input type="file" accept=".xlsx,.csv" class="mt-3 w-full" :disabled="useEventData" @change="onFileInput('data', $event)" />
                    <p v-if="dataFile" class="mt-2 text-xs text-gray-600">{{ dataFile.name }}</p>
                </div>
            </div>

            <div class="mt-4 p-3 border border-gray-200 rounded bg-gray-50 dark:bg-gray-900/20 space-y-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input v-model="useEventData" type="checkbox" />
                    Use Event Responses (no Excel upload)
                </label>

                <div v-if="useEventData" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Name column</label>
                        <select v-model="selectedNameColumn" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="" disabled>Select column</option>
                            <option v-for="column in responseColumns" :key="`name-${column}`" :value="column">{{ column }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Email column</label>
                        <select v-model="selectedEmailColumn" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="" disabled>Select column</option>
                            <option v-for="column in responseColumns" :key="`email-${column}`" :value="column">{{ column }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Subform type (optional)</label>
                        <select v-model="selectedSubformType" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="">All types</option>
                            <option v-for="type in subformTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                </div>

                <p v-if="useEventData && !loadingColumns && responseColumns.length === 0" class="text-xs text-amber-600">
                    No response_data columns were found for this event.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="text-xs text-gray-500 dark:text-gray-400">Output format</label>
                    <select v-model="outputFormat" class="border border-gray-300 rounded px-3 py-2 w-full md:w-64">
                        <option value="pdf">PDF</option>
                        <option value="png">PNG</option>
                        <option value="jpg">JPG</option>
                        <option value="pptx">PPTX</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs text-gray-500 dark:text-gray-400">Naming convention</label>
                    <input
                        v-model="namingTemplate"
                        type="text"
                        class="border border-gray-300 rounded px-3 py-2 w-full"
                        placeholder="{Fullname}_{date}"
                    />
                </div>
            </div>

            <div v-if="uploading" class="mt-4">
                <div class="text-xs text-gray-600 mb-1">Uploading files... {{ uploadProgress }}%</div>
                <div class="w-full bg-gray-200 rounded h-2">
                    <div class="bg-blue-600 h-2 rounded" :style="{ width: `${uploadProgress}%` }"></div>
                </div>
            </div>

            <div v-if="processingOnServer" class="mt-4 flex items-center gap-3 p-3 bg-gray-100 rounded border border-gray-300">
                <div class="h-4 w-4 border-2 border-gray-400 border-t-blue-600 rounded-full animate-spin"></div>
                <span class="text-sm text-gray-700">Processing on Server...</span>
            </div>
            <p class="font-medium mt-4">Note: This will automatically send emails to the recipients</p>
            <div class="mt-2 flex flex-wrap gap-2 w-full justify-between">
                <button
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded"
                    :disabled="uploading || processingOnServer"
                    @click="submitForProcessing"
                >
                    <span v-if="!uploading && !processingOnServer">Upload & Process</span>
                    <span v-else-if="uploading">Uploading...</span>
                    <span v-else>Processing...</span>
                </button>

                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                    :disabled="serverStatus !== 'completed'"
                    @click="downloadZip"
                >
                    Download ZIP
                </button>
            </div>
        </div>

        <div v-if="message" class="text-green-600 text-sm">
            {{ message }}
        </div>
        <div v-if="errorMessage" class="text-red-600 text-sm">
            {{ errorMessage }}
        </div>
        <section>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                Brief: Template Formatting Rules
            </h3>
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                    <li>Generate certificates using either uploaded Excel data or event response_data fields, then process in the background queue.</li>
                    <li>Use placeholder format <strong>&lt;&lt;COLUMN_NAME&gt;&gt;</strong> inside text boxes or table cells.</li>
                    <li>Use the exact selected column key from the UI whenever possible (example: <code>fullname_8647</code>).</li>
                    <li><strong>&lt;&lt;NAME&gt;&gt;</strong> can resolve to fullname-style fields, but exact keys are most reliable.</li>
                    <li>Avoid putting placeholder brackets in separate text boxes; keep each placeholder in one text element.</li>
                    <li>Save template as <strong>.pptx</strong> only.</li>
                </ul>
            </div>
        </section>
    </div>
</template>
