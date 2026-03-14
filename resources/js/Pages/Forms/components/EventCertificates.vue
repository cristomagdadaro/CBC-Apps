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
            dragOver: { template: false, data: false },
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
        isReadyToProcess() {
            if (this.useEventData) {
                return this.selectedNameColumn && this.selectedEmailColumn;
            }
            return !!this.dataFile;
        },
        statusConfig() {
            const configs = {
                idle: { color: 'gray', icon: 'LuCircle', text: 'Ready' },
                queued: { color: 'blue', icon: 'LuLoader2', text: 'Queued', spin: true },
                processing: { color: 'amber', icon: 'LuLoader2', text: 'Processing', spin: true },
                completed: { color: 'green', icon: 'LuCheckCircle', text: 'Completed' },
                failed: { color: 'red', icon: 'LuXCircle', text: 'Failed' },
            };
            return configs[this.serverStatus] || configs.idle;
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
            if (value) this.templateFile = null;
        },
        useEventData(value) {
            if (value) this.dataFile = null;
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
            if (!file) return 'File is required.';
            if (file.size > this.maxFileSizeBytes) return 'File size must be 10MB or lower.';
            
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
            if (type === 'template') this.templateFile = file;
            else this.dataFile = file;
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
            window.open(route('api.event.certificates.template.view', [this.eventId]), '_blank');
        },
        viewSelectedTemplate() {
            if (!this.templateFile) {
                this.errorMessage = 'Select a template file first.';
                return;
            }
            const objectUrl = URL.createObjectURL(this.templateFile);
            window.open(objectUrl, '_blank');
            setTimeout(() => URL.revokeObjectURL(objectUrl), 60_000);
        },
        async fetchResponseColumns() {
            if (!this.eventId) return;
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
                    const emailMatch = this.responseColumns.find((col) => /email/i.test(String(col)));
                    this.selectedEmailColumn = emailMatch || '';
                }
                if (!this.selectedNameColumn) {
                    const nameMatch = this.responseColumns.find((col) => /full\s*name|fullname|name/i.test(String(col)));
                    this.selectedNameColumn = nameMatch || '';
                }
                if (!this.hasSavedTemplate) this.useSavedTemplate = false;
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to fetch response columns.');
            } finally {
                this.loadingColumns = false;
            }
        },
        onDrop(type, event) {
            event.preventDefault();
            this.dragOver[type] = false;
            this.resetMessages();
            const file = event.dataTransfer?.files?.[0] || null;
            this.setFile(type, file);
        },
        onDragOver(type, event) {
            event.preventDefault();
            this.dragOver[type] = true;
        },
        onDragLeave(type, event) {
            event.preventDefault();
            this.dragOver[type] = false;
        },
        async resolveErrorMessage(error, fallback = 'Certificate request failed.') {
            if (!error?.response) return error?.message || fallback;
            if (error?.response?.status === 413) {
                return 'Request is too large. Use Saved Template/Event Data to avoid uploading files.';
            }
            if (error?.response?.status === 422) {
                return error?.response?.data?.message || 'Validation failed.';
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
                this.errorMessage = 'Select both name and email columns.';
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
                    if (shouldUploadTemplate) formData.append('template', this.templateFile);
                    if (shouldUploadData) formData.append('data', this.dataFile);
                    formData.append('format', this.outputFormat);
                    formData.append('use_saved_template', this.useSavedTemplate ? '1' : '0');
                    formData.append('use_event_data', this.useEventData ? '1' : '0');
                    if (this.useEventData) {
                        formData.append('name_column', this.selectedNameColumn);
                        formData.append('email_column', this.selectedEmailColumn);
                        if (this.selectedSubformType) formData.append('subform_type', this.selectedSubformType);
                    }
                    if (this.namingTemplate?.trim()) formData.append('name_template', this.namingTemplate.trim());

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
                        ...(this.useEventData ? {
                            name_column: this.selectedNameColumn,
                            email_column: this.selectedEmailColumn,
                            ...(this.selectedSubformType ? { subform_type: this.selectedSubformType } : {}),
                        } : {}),
                        ...(this.namingTemplate?.trim() ? { name_template: this.namingTemplate.trim() } : {}),
                    };
                    requestConfig = { routeParams: this.eventId };
                }

                const response = await this.fetchPostApi('api.event.certificates.generate', payload, requestConfig);
                this.batchId = response?.data?.data?.batch_id || null;
                this.serverStatus = 'queued';
                this.processingOnServer = true;
                this.message = hasFileUpload ? 'Upload complete. Processing...' : 'Request submitted. Processing...';
                this.startPolling();
            } catch (error) {
                this.processingOnServer = false;
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to submit request.');
            } finally {
                this.uploading = false;
            }
        },
        startPolling() {
            this.stopPolling();
            if (!this.batchId) return;
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
            if (!this.batchId || !this.eventId) return;
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
                    this.message = `Completed! Success: ${summary.success ?? 0}, Failed: ${summary.fail ?? 0}`;
                }
            } catch (error) {
                this.processingOnServer = false;
                this.stopPolling();
                this.errorMessage = await this.resolveErrorMessage(error, 'Failed to fetch status.');
            }
        },
        downloadZip() {
            if (!this.eventId || !this.batchId || this.serverStatus !== 'completed') return;
            window.location.href = route('api.event.certificates.download', [this.eventId, this.batchId]);
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Main Generator Card -->
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-indigo-600">
                <div class="flex items-center gap-3">
                    <LuFileText class="w-6 h-6 text-white" />
                    <div>
                        <h3 class="text-lg font-bold text-white">Bulk Certificate Generator</h3>
                        <p class="text-sm text-white/80">Generate and email certificates automatically</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Template Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <LuFileType class="w-4 h-4 text-blue-600" />
                            Template Source
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input 
                                v-model="useSavedTemplate" 
                                type="checkbox" 
                                :disabled="!hasSavedTemplate"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" 
                            />
                            <span class="text-sm text-gray-600 dark:text-gray-400">Use saved template</span>
                        </label>
                    </div>

                    <!-- Saved Template Info -->
                    <div v-if="useSavedTemplate && hasSavedTemplate" class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                                <LuFileCheck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ savedTemplateName }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Event-specific template</p>
                            </div>
                        </div>
                        <button @click="viewSavedTemplate" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-800/50 rounded-lg transition-colors">
                            <LuEye class="w-4 h-4" />
                            Preview
                        </button>
                    </div>

                    <!-- Upload Template -->
                    <div 
                        v-else
                        class="relative border-2 border-dashed rounded-md p-6 text-center transition-all duration-200"
                        :class="dragOver.template ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                        @dragover.prevent="onDragOver('template', $event)"
                        @dragleave.prevent="onDragLeave('template', $event)"
                        @drop.prevent="onDrop('template', $event)"
                    >
                        <input 
                            type="file" 
                            accept=".pptx" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            @change="onFileInput('template', $event)" 
                        />
                        <div class="space-y-2">
                            <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <LuUpload class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Drop your .pptx template here or click to browse
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Maximum file size: 10MB</p>
                        </div>
                        
                        <!-- Selected File -->
                        <div v-if="templateFile" class="mt-4 flex items-center justify-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <LuFile class="w-5 h-5 text-green-600" />
                            <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ templateFile.name }}</span>
                            <button @click.stop="templateFile = null" class="p-1 hover:bg-green-100 dark:hover:bg-green-800 rounded">
                                <LuX class="w-4 h-4 text-green-600" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Data Source Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <LuDatabase class="w-4 h-4 text-purple-600" />
                            Data Source
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input 
                                v-model="useEventData" 
                                type="checkbox"
                                class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500" 
                            />
                            <span class="text-sm text-gray-600 dark:text-gray-400">Use event responses</span>
                        </label>
                    </div>

                    <!-- Event Data Columns -->
                    <div v-if="useEventData" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name Column</label>
                            <div class="relative">
                                <select 
                                    v-model="selectedNameColumn" 
                                    class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option value="" disabled>Select column</option>
                                    <option v-for="column in responseColumns" :key="`name-${column}`" :value="column">{{ column }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email Column</label>
                            <div class="relative">
                                <select 
                                    v-model="selectedEmailColumn" 
                                    class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option value="" disabled>Select column</option>
                                    <option v-for="column in responseColumns" :key="`email-${column}`" :value="column">{{ column }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Subform Filter (Optional)</label>
                            <div class="relative">
                                <select 
                                    v-model="selectedSubformType" 
                                    class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">All types</option>
                                    <option v-for="type in subformTypes" :key="type" :value="type">{{ type }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Data File -->
                    <div 
                        v-else
                        class="relative border-2 border-dashed rounded-md p-6 text-center transition-all duration-200"
                        :class="dragOver.data ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                        @dragover.prevent="onDragOver('data', $event)"
                        @dragleave.prevent="onDragLeave('data', $event)"
                        @drop.prevent="onDrop('data', $event)"
                    >
                        <input 
                            type="file" 
                            accept=".xlsx,.csv" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            @change="onFileInput('data', $event)" 
                        />
                        <div class="space-y-2">
                            <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <LuSheet class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Drop your Excel/CSV file here or click to browse
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Supports .xlsx and .csv formats</p>
                        </div>

                        <!-- Selected File -->
                        <div v-if="dataFile" class="mt-4 flex items-center justify-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <LuSheet class="w-5 h-5 text-green-600" />
                            <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ dataFile.name }}</span>
                            <button @click.stop="dataFile = null" class="p-1 hover:bg-green-100 dark:hover:bg-green-800 rounded">
                                <LuX class="w-4 h-4 text-green-600" />
                            </button>
                        </div>
                    </div>

                    <p v-if="useEventData && !loadingColumns && responseColumns.length === 0" class="flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400">
                        <LuAlertTriangle class="w-4 h-4" />
                        No response data found for this event
                    </p>
                </div>

                <!-- Output Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-md">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Output Format</label>
                        <select 
                            v-model="outputFormat" 
                            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="pdf">PDF Document</option>
                            <option value="png">PNG Image</option>
                            <option value="jpg">JPG Image</option>
                            <option value="pptx">PowerPoint</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">File Naming Pattern</label>
                        <div class="relative">
                            <input
                                v-model="namingTemplate"
                                type="text"
                                class="block w-full pl-3 pr-3 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="{Fullname}_{date}"
                            />
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Use placeholders like {Fullname}, {date}, {event_id}</p>
                    </div>
                </div>

                <!-- Progress & Status -->
                <div v-if="uploading" class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Uploading files...</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ uploadProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div 
                            class="bg-blue-600 h-full rounded-full transition-all duration-300 ease-out"
                            :style="{ width: `${uploadProgress}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Server Status -->
                <div v-if="processingOnServer || serverStatus !== 'idle'" class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-md border border-gray-200 dark:border-gray-600">
                    <component 
                        :is="statusConfig.icon" 
                        class="w-5 h-5"
                        :class="{
                            'text-blue-600': statusConfig.color === 'blue',
                            'text-amber-600': statusConfig.color === 'amber',
                            'text-green-600': statusConfig.color === 'green',
                            'text-red-600': statusConfig.color === 'red',
                            'text-gray-600': statusConfig.color === 'gray',
                        }"
                    />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ statusConfig.text }}</p>
                        <p v-if="message" class="text-xs text-gray-500 dark:text-gray-400">{{ message }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        class="inline-flex items-center justify-center gap-2 flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-md shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                        :disabled="uploading || processingOnServer || !isReadyToProcess"
                        @click="submitForProcessing"
                    >
                        <LuLoader2 v-if="uploading || processingOnServer" class="w-5 h-5 animate-spin" />
                        <LuPlay v-else class="w-5 h-5" />
                        {{ uploading ? 'Uploading...' : processingOnServer ? 'Processing...' : 'Generate Certificates' }}
                    </button>

                    <button
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-300 dark:hover:border-gray-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="serverStatus !== 'completed'"
                        @click="downloadZip"
                    >
                        <LuDownload class="w-5 h-5" />
                        Download ZIP
                    </button>
                </div>

                <!-- Alerts -->
                <TransitionGroup name="alert" tag="div" class="space-y-3">
                    <div v-if="errorMessage" key="error" class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                        <LuAlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">Error</p>
                            <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ errorMessage }}</p>
                        </div>
                        <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>

                    <div v-if="message && !processingOnServer" key="success" class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                        <LuCheckCircle class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">Success</p>
                            <p class="text-sm text-green-600 dark:text-green-300 mt-1">{{ message }}</p>
                        </div>
                        <button @click="message = ''" class="text-green-400 hover:text-green-600">
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>
                </TransitionGroup>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center gap-2">
                    <LuInfo class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    <h3 class="font-semibold text-gray-900 dark:text-white">Template Formatting Guide</h3>
                </div>
            </div>
            <div class="p-6">
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Use placeholder format <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-blue-600 dark:text-blue-400 font-mono text-xs">&lt;&lt;COLUMN_NAME&gt;&gt;</code> in text boxes</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Match column names exactly from your data source (e.g., <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-blue-600 dark:text-blue-400 font-mono text-xs">fullname_8647</code>)</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Keep each placeholder in a single text element—don't split across multiple boxes</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Save your template as <strong>.pptx</strong> format only</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.alert-enter-active,
.alert-leave-active {
    transition: all 0.3s ease;
}
.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>