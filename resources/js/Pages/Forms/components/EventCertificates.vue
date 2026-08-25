<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";

export default {
    name: "EventCertificates",
    components: {
        DataTable,
    },
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
            selectedNameColumn: "",
            selectedEmailColumn: "",
            selectedSubformType: "",
            recipients: [],
            selectedRecipientIds: [],
            loadingColumns: false,
            savedTemplate: this.template,
            uploadProgress: 0,
            uploading: false,
            processingOnServer: false,
            message: "",
            errorMessage: "",
            outputFormat: "pdf",
            namingTemplate: "{event}_{Fullname}_{date}",
            batchId: null,
            poller: null,
            realtimeCleanup: null,
            serverStatus: "idle",
            maxFileSizeBytes: 10 * 1024 * 1024,
            dragOver: { template: false, data: false },
        };
    },
    computed: {
        hasSavedTemplate() {
            return !!this.savedTemplate?.template_path;
        },
        savedTemplateName() {
            return this.savedTemplate?.template_name || "template.pptx";
        },
        hasSelectedTemplateFile() {
            return !!this.templateFile;
        },
        isReadyToProcess() {
            if (this.useEventData) {
                return this.selectedNameColumn && this.selectedEmailColumn && this.selectedRecipientIds.length > 0;
            }
            return !!this.dataFile;
        },
        filteredRecipients() {
            if (!this.selectedSubformType) {
                return this.recipients;
            }
            return this.recipients.filter((recipient) => recipient?.subform_type === this.selectedSubformType);
        },
        tableRows() {
            return this.filteredRecipients.map((recipient) => ({
                ...recipient,
                name: this.recipientName(recipient),
                email: this.recipientEmail(recipient),
                status: this.recipientCertificateStatusConfig(recipient).label,
                subform: recipient.subform_type || "—",
            }));
        },
        allFilteredRecipientsSelected() {
            if (!this.filteredRecipients.length) {
                return false;
            }
            return this.filteredRecipients.every((recipient) => this.selectedRecipientIds.includes(recipient.id));
        },
        selectedRecipientCount() {
            return this.selectedRecipientIds.length;
        },
        statusConfig() {
            const configs = {
                idle: { color: "slate", icon: "LuCircle", text: "Ready" },
                queued: { color: "blue", icon: "LuLoader2", text: "Queued", spin: true },
                processing: { color: "amber", icon: "LuLoader2", text: "Processing", spin: true },
                completed: { color: "emerald", icon: "LuCheckCircle2", text: "Completed" },
                failed: { color: "red", icon: "LuXCircle", text: "Failed" },
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
        selectedSubformType() {
            if (this.useEventData) {
                this.syncRecipientSelection();
            }
        },
    },
    beforeUnmount() {
        this.stopPolling();
        this.cleanupRealtime();
    },
    methods: {
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }
            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            if (!this.batchId) {
                return;
            }

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: `certificates.batch.${this.batchId}`,
                    event: "certificates.batch.updated",
                    handler: (payload) => this.applyBatchStatus(payload),
                },
            ]);
        },
        applyBatchStatus(data = {}) {
            const status = data?.status || "processing";
            this.serverStatus = status;

            if (data?.message) {
                this.message = data.message;
            }

            if (status === "failed") {
                this.processingOnServer = false;
                this.stopPolling();
                this.errorMessage = data?.error || data?.message || "Server processing failed.";
                this.fetchResponseColumns();
                return;
            }

            if (status === "completed") {
                this.processingOnServer = false;
                this.stopPolling();
                const summary = data?.summary || {};
                this.message = `Completed! Success: ${summary.success ?? 0}, Failed: ${summary.fail ?? 0}`;
                this.fetchResponseColumns();
                return;
            }

            this.processingOnServer = true;
        },
        resetMessages() {
            this.message = "";
            this.errorMessage = "";
        },
        validateFile(file, expectedType) {
            if (!file) return "File is required.";
            if (file.size > this.maxFileSizeBytes) return "File size must be 10MB or lower.";

            const name = file.name.toLowerCase();
            if (expectedType === "template" && !name.endsWith(".pptx")) {
                return "Template file must be a .pptx file.";
            }
            if (expectedType === "data" && !(name.endsWith(".xlsx") || name.endsWith(".csv"))) {
                return "Data file must be a .xlsx or .csv file.";
            }
            return null;
        },
        setFile(type, file) {
            const error = this.validateFile(file, type);
            if (error) {
                this.errorMessage = error;
                return;
            }
            if (type === "template") this.templateFile = file;
            else this.dataFile = file;
            this.errorMessage = "";
        },
        onFileInput(type, event) {
            this.resetMessages();
            const file = event.target.files?.[0] || null;
            this.setFile(type, file);
        },
        viewSavedTemplate() {
            if (!this.eventId) {
                this.errorMessage = "Event ID is missing.";
                return;
            }
            window.open(route("api.event.certificates.template.view", [this.eventId]), "_blank");
        },
        viewSelectedTemplate() {
            if (!this.templateFile) {
                this.errorMessage = "Select a template file first.";
                return;
            }
            const objectUrl = URL.createObjectURL(this.templateFile);
            window.open(objectUrl, "_blank");
            setTimeout(() => URL.revokeObjectURL(objectUrl), 60_000);
        },
        async fetchResponseColumns() {
            if (!this.eventId) return;
            this.loadingColumns = true;
            try {
                const response = await this.fetchGetApi("api.event.certificates.columns", {
                    routeParams: this.eventId,
                });
                const payload = response?.data || {};
                this.responseColumns = Array.isArray(payload?.columns) ? payload.columns : [];
                this.subformTypes = Array.isArray(payload?.subform_types) ? payload.subform_types : [];
                this.recipients = Array.isArray(payload?.recipients) ? payload.recipients : [];
                this.savedTemplate = payload?.template || null;

                const existingSelection = new Set(this.selectedRecipientIds);
                const availableRecipientIds = this.recipients.map((recipient) => recipient?.id).filter((id) => !!id);

                if (existingSelection.size > 0) {
                    this.selectedRecipientIds = availableRecipientIds.filter((id) => existingSelection.has(id));
                } else {
                    this.selectedRecipientIds = [...availableRecipientIds];
                }

                this.syncRecipientSelection();

                if (!this.selectedEmailColumn) {
                    const emailMatch = this.responseColumns.find((col) => /email/i.test(String(col)));
                    this.selectedEmailColumn = emailMatch || "";
                }
                if (!this.selectedNameColumn) {
                    const nameMatch = this.responseColumns.find((col) => /full\s*name|fullname|name/i.test(String(col)));
                    this.selectedNameColumn = nameMatch || "";
                }
                if (!this.hasSavedTemplate) this.useSavedTemplate = false;
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error, "Failed to fetch response columns.");
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
        syncRecipientSelection() {
            const visibleRecipientIds = this.filteredRecipients.map((recipient) => recipient?.id).filter((id) => !!id);

            this.selectedRecipientIds = this.selectedRecipientIds.filter((id) => {
                return this.recipients.some((recipient) => recipient?.id === id);
            });

            if (this.selectedRecipientIds.length === 0 && visibleRecipientIds.length > 0) {
                this.selectedRecipientIds = [...visibleRecipientIds];
            }
        },
        toggleAllFilteredRecipients(event) {
            const checked = !!event?.target?.checked;
            const filteredIds = this.filteredRecipients.map((recipient) => recipient?.id).filter((id) => !!id);

            if (checked) {
                this.selectedRecipientIds = Array.from(new Set([...this.selectedRecipientIds, ...filteredIds]));
                return;
            }

            this.selectedRecipientIds = this.selectedRecipientIds.filter((id) => !filteredIds.includes(id));
        },
        toggleRecipientSelection(recipient) {
            const index = this.selectedRecipientIds.indexOf(recipient.id);
            if (index === -1) {
                this.selectedRecipientIds.push(recipient.id);
            } else {
                this.selectedRecipientIds.splice(index, 1);
            }
        },
        recipientDisplayValue(recipient, keyHint) {
            const source = recipient?.response_data && typeof recipient.response_data === "object" ? recipient.response_data : {};

            if (keyHint && source[keyHint] !== undefined && source[keyHint] !== null) {
                return String(source[keyHint]);
            }

            if (!keyHint) {
                return "";
            }

            const normalizedHint = String(keyHint).toLowerCase().replace(/\s+/g, "").replace(/_/g, "");
            const fallbackKey = Object.keys(source).find((key) => {
                const normalizedKey = String(key).toLowerCase().replace(/\s+/g, "").replace(/_/g, "");
                return normalizedKey === normalizedHint;
            });

            if (fallbackKey && source[fallbackKey] !== undefined && source[fallbackKey] !== null) {
                return String(source[fallbackKey]);
            }

            return "";
        },
        recipientName(recipient) {
            return this.recipientDisplayValue(recipient, this.selectedNameColumn) || "—";
        },
        recipientEmail(recipient) {
            return this.recipientDisplayValue(recipient, this.selectedEmailColumn) || "—";
        },
        recipientCertificateStatus(recipient) {
            return recipient?.certificate_delivery_status || "not_sent";
        },
        recipientCertificateStatusConfig(recipient) {
            const status = this.recipientCertificateStatus(recipient);
            const configs = {
                sent: {
                    label: "Sent",
                    badge: "bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30",
                },
                queued: {
                    label: "Queued",
                    badge: "bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30",
                },
                failed: {
                    label: "Failed",
                    badge: "bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30",
                },
                not_sent: {
                    label: "Not sent",
                    badge: "bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700",
                },
            };

            return configs[status] || configs.not_sent;
        },
        formatSubmittedAt(value) {
            if (!value) return "—";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "—";
            return date.toLocaleString();
        },
        formatCertificateSentAt(recipient) {
            const value = recipient?.certificate_delivery_sent_at;
            if (!value) return "";

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";

            return date.toLocaleString();
        },
        async resolveErrorMessage(error, fallback = "Certificate request failed.") {
            if (!error?.response) return error?.message || fallback;
            if (error?.response?.status === 413) {
                return "Request is too large. Use Saved Template/Event Data to avoid uploading files.";
            }
            if (error?.response?.status === 422) {
                return error?.response?.data?.message || "Validation failed.";
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
                this.errorMessage = "Event ID is missing.";
                return;
            }
            const templateError = this.validateFile(this.templateFile, "template");
            if (!this.useSavedTemplate && templateError) {
                this.errorMessage = templateError;
                return;
            }
            if (!this.useEventData) {
                const dataError = this.validateFile(this.dataFile, "data");
                if (dataError) {
                    this.errorMessage = dataError;
                    return;
                }
            }
            if (this.useEventData && (!this.selectedEmailColumn || !this.selectedNameColumn)) {
                this.errorMessage = "Select both name and email columns.";
                return;
            }
            if (this.useEventData && this.selectedRecipientIds.length === 0) {
                this.errorMessage = "Select at least one recipient.";
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
                this.message = "Submitting request...";
            }

            try {
                let payload;
                let requestConfig;
                if (hasFileUpload) {
                    const formData = new FormData();
                    if (shouldUploadTemplate) formData.append("template", this.templateFile);
                    if (shouldUploadData) formData.append("data", this.dataFile);
                    formData.append("format", this.outputFormat);
                    formData.append("use_saved_template", this.useSavedTemplate ? "1" : "0");
                    formData.append("use_event_data", this.useEventData ? "1" : "0");
                    if (this.useEventData) {
                        formData.append("name_column", this.selectedNameColumn);
                        formData.append("email_column", this.selectedEmailColumn);
                        if (this.selectedSubformType) formData.append("subform_type", this.selectedSubformType);
                        this.selectedRecipientIds.forEach((id) => formData.append("recipient_ids[]", id));
                    }
                    if (this.namingTemplate?.trim()) formData.append("name_template", this.namingTemplate.trim());

                    payload = formData;
                    requestConfig = {
                        headers: { "Content-Type": "multipart/form-data" },
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
                                  recipient_ids: this.selectedRecipientIds,
                              }
                            : {}),
                        ...(this.namingTemplate?.trim() ? { name_template: this.namingTemplate.trim() } : {}),
                    };
                    requestConfig = { routeParams: this.eventId };
                }

                const response = await this.fetchPostApi("api.event.certificates.generate", payload, requestConfig);
                this.batchId = response?.data?.data?.batch_id || null;
                this.serverStatus = "queued";
                this.processingOnServer = true;
                this.message = hasFileUpload ? "Upload complete. Processing..." : "Request submitted. Processing...";
                this.configureRealtime();
                this.startPolling();
            } catch (error) {
                this.processingOnServer = false;
                this.errorMessage = await this.resolveErrorMessage(error, "Failed to submit request.");
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
                const response = await this.fetchGetApi("api.event.certificates.status", {
                    routeParams: [this.eventId, this.batchId],
                });
                this.applyBatchStatus(response?.data || {});
            } catch (error) {
                this.processingOnServer = false;
                this.stopPolling();
                this.errorMessage = await this.resolveErrorMessage(error, "Failed to fetch status.");
            }
        },
        downloadZip() {
            if (!this.eventId || !this.batchId || this.serverStatus !== "completed") return;
            window.location.href = route("api.event.certificates.download", [this.eventId, this.batchId]);
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Main Generator Card -->
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden transition-all duration-300">
            <!-- Premium Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 relative overflow-hidden">
                <div class="absolute inset-0 bg-white/10 dark:bg-black/10 mix-blend-overlay"></div>
                <div class="relative z-10 flex items-center gap-3.5">
                    <div class="p-2.5 bg-white/20 rounded-xl shadow-sm border border-white/30 shrink-0">
                        <LuFileText class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight leading-none drop-shadow-md">Bulk Certificate Generator</h3>
                        <p class="text-sm font-semibold text-blue-100 mt-1 drop-shadow-sm">Generate and email certificates automatically</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">
                <!-- Section 1: Template Source -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/50">
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 uppercase">
                            <LuFileType class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                            Template Source
                        </div>
                        <label
                            class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:bg-slate-100 dark:hover:bg-slate-800"
                            :class="{ 'opacity-50 cursor-not-allowed': !hasSavedTemplate }">
                            <input
                                v-model="useSavedTemplate"
                                type="checkbox"
                                :disabled="!hasSavedTemplate"
                                class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-white dark:bg-slate-900" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Use saved template</span>
                        </label>
                    </div>

                    <!-- Saved Template Info Box -->
                    <div
                        v-if="useSavedTemplate && hasSavedTemplate"
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-500/30">
                                <LuFileCheck class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-base font-bold text-slate-900 dark:text-white">
                                    {{ savedTemplateName }}
                                </p>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Event-specific template</p>
                            </div>
                        </div>
                        <button
                            @click="viewSavedTemplate"
                            class="inline-flex justify-center items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-700 dark:text-indigo-300 bg-white dark:bg-slate-800 border border-indigo-200 dark:border-indigo-500/30 hover:bg-indigo-100 dark:hover:bg-indigo-500/30 rounded-xl shadow-sm transition-all active:scale-95 w-full sm:w-auto">
                            <LuEye class="w-4 h-4" />
                            Preview
                        </button>
                    </div>

                    <!-- Upload Template Dropzone -->
                    <div
                        v-else
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="dragOver.template ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 hover:border-indigo-400 dark:hover:border-indigo-500/50'"
                        @dragover.prevent="onDragOver('template', $event)"
                        @dragleave.prevent="onDragLeave('template', $event)"
                        @drop.prevent="onDrop('template', $event)">
                        <input
                            type="file"
                            accept=".pptx"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            @change="onFileInput('template', $event)" />

                        <div
                            v-if="!templateFile"
                            class="space-y-3 pointer-events-none">
                            <div
                                class="mx-auto w-14 h-14 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center shadow-sm transition-transform duration-300"
                                :class="{
                                    'scale-110 border-indigo-300 dark:border-indigo-500': dragOver.template,
                                }">
                                <LuUploadCloud
                                    class="w-6 h-6 text-slate-400 dark:text-slate-500"
                                    :class="{
                                        'text-indigo-500 dark:text-indigo-400': dragOver.template,
                                    }" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Drop your .pptx template here or click to browse</p>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Maximum file size: 10MB</p>
                            </div>
                        </div>

                        <!-- Selected File -->
                        <div
                            v-else
                            class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <div class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl shadow-sm pointer-events-auto">
                                <LuFile class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                                    {{ templateFile.name }}
                                </span>
                                <div class="w-px h-4 bg-emerald-200 dark:bg-emerald-500/30 mx-1"></div>
                                <button
                                    @click.stop="templateFile = null"
                                    class="p-1 hover:bg-emerald-200 dark:hover:bg-emerald-500/30 rounded-md transition-colors">
                                    <LuX class="w-4 h-4 text-emerald-700 dark:text-emerald-400" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Source -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 uppercase">
                            <LuDatabase class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                            Data Source to Template Column Mapping
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:bg-slate-100 dark:hover:bg-slate-800">
                            <input
                                v-model="useEventData"
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-white dark:bg-slate-900" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Use event responses</span>
                        </label>
                    </div>

                    <!-- Event Data Configuration -->
                    <div
                        v-if="useEventData"
                        class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 sm:p-5 shadow-sm">
                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Name Column</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedNameColumn"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg shadow-sm appearance-none transition-colors">
                                        <option
                                            value=""
                                            disabled>
                                            Select column
                                        </option>
                                        <option
                                            v-for="column in responseColumns"
                                            :key="`name-${column}`"
                                            :value="column">
                                            {{ column }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="absolute right-3 top-3 w-4 h-4 text-slate-400 pointer-events-none" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Email Column</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedEmailColumn"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg shadow-sm appearance-none transition-colors">
                                        <option
                                            value=""
                                            disabled>
                                            Select column
                                        </option>
                                        <option
                                            v-for="column in responseColumns"
                                            :key="`email-${column}`"
                                            :value="column">
                                            {{ column }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="absolute right-3 top-3 w-4 h-4 text-slate-400 pointer-events-none" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Subform Filter</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedSubformType"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg shadow-sm appearance-none transition-colors">
                                        <option value="">All types</option>
                                        <option
                                            v-for="type in subformTypes"
                                            :key="type"
                                            :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="absolute right-3 top-3 w-4 h-4 text-slate-400 pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        <!-- Data Table (Recipients) -->
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm bg-white dark:bg-slate-900">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 px-2 py-0.5 rounded-full text-xs font-black">
                                        {{ selectedRecipientCount }}
                                    </span>
                                    Possible Recipients
                                </div>
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer shadow-sm">
                                    <input
                                        type="checkbox"
                                        :checked="allFilteredRecipientsSelected"
                                        @change="toggleAllFilteredRecipients"
                                        class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900" />
                                    Select all shown
                                </label>
                            </div>

                            <div v-if="filteredRecipients.length">
                                <DataTable
                                    :mode="'offline'"
                                    :rows="tableRows"
                                    @row-dblclick="toggleRecipientSelection"
                                    :columns="[
                                        { key: 'select', label: 'Select', class: 'w-10' },
                                        { key: 'name', label: 'Name' },
                                        { key: 'email', label: 'Email' },
                                        { key: 'status', label: 'Status' },
                                        { key: 'subform', label: 'Subform' },
                                    ]"
                                    class="border-t-0"
                                    :enablePagination="true"
                                    :enableSearch="true"
                                    :enableFilters="true"
                                    :enableExport="true"
                                    emptyMessage="No responses available.">
                                    <template #cell-select="{ row: recipient }">
                                        <input
                                            v-model="selectedRecipientIds"
                                            :value="recipient.id"
                                            type="checkbox"
                                            class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800" />
                                    </template>

                                    <template #cell-name="{ row: recipient }">
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[150px] inline-block align-bottom">
                                            {{ recipientName(recipient) }}
                                        </span>
                                    </template>

                                    <template #cell-email="{ row: recipient }">
                                        <span class="font-medium text-slate-600 dark:text-slate-400 truncate max-w-[180px] inline-block align-bottom">
                                            {{ recipientEmail(recipient) }}
                                        </span>
                                    </template>

                                    <template #cell-status="{ row: recipient }">
                                        <div class="flex flex-col gap-1 w-fit">
                                            <span
                                                class="inline-flex w-fit items-center border rounded-full px-2.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wider"
                                                :class="recipientCertificateStatusConfig(recipient).badge">
                                                {{ recipientCertificateStatusConfig(recipient).label }}
                                            </span>
                                            <span
                                                v-if="recipientCertificateStatus(recipient) === 'sent' && formatCertificateSentAt(recipient)"
                                                class="text-[0.65rem] font-semibold text-slate-500 dark:text-slate-500 ml-1">
                                                {{ formatCertificateSentAt(recipient) }}
                                            </span>
                                        </div>
                                    </template>

                                    <template #cell-subform="{ row: recipient }">
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            {{ recipient.subform_type || "—" }}
                                        </span>
                                    </template>
                                </DataTable>
                            </div>
                            <div
                                v-else
                                class="px-6 py-12 text-sm font-medium text-slate-400 dark:text-slate-500 text-center flex flex-col items-center">
                                <LuInbox class="w-8 h-8 opacity-40 mb-3" />
                                No recipients found for the selected filters.
                            </div>
                        </div>

                        <!-- Missing Data Warning -->
                        <div
                            v-if="!loadingColumns && responseColumns.length === 0"
                            class="flex items-center gap-2 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-sm font-bold text-amber-700 dark:text-amber-400 shadow-sm">
                            <LuAlertTriangle class="w-5 h-5 shrink-0" />
                            No response data found for this event.
                        </div>
                    </div>

                    <!-- Upload Data Dropzone -->
                    <div
                        v-else
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="dragOver.data ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 hover:border-indigo-400 dark:hover:border-indigo-500/50'"
                        @dragover.prevent="onDragOver('data', $event)"
                        @dragleave.prevent="onDragLeave('data', $event)"
                        @drop.prevent="onDrop('data', $event)">
                        <input
                            type="file"
                            accept=".xlsx,.csv"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            @change="onFileInput('data', $event)" />

                        <div
                            v-if="!dataFile"
                            class="space-y-3 pointer-events-none">
                            <div
                                class="mx-auto w-14 h-14 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full flex items-center justify-center shadow-sm transition-transform duration-300"
                                :class="{
                                    'scale-110 border-indigo-300 dark:border-indigo-500': dragOver.data,
                                }">
                                <LuSheet
                                    class="w-6 h-6 text-slate-400 dark:text-slate-500"
                                    :class="{
                                        'text-indigo-500 dark:text-indigo-400': dragOver.data,
                                    }" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Drop your Excel/CSV file here or click to browse</p>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Supports .xlsx and .csv formats up to 10MB</p>
                            </div>
                        </div>

                        <!-- Selected File -->
                        <div
                            v-else
                            class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <div class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl shadow-sm pointer-events-auto">
                                <LuSheet class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                                    {{ dataFile.name }}
                                </span>
                                <div class="w-px h-4 bg-emerald-200 dark:bg-emerald-500/30 mx-1"></div>
                                <button
                                    @click.stop="dataFile = null"
                                    class="p-1 hover:bg-emerald-200 dark:hover:bg-emerald-500/30 rounded-md transition-colors">
                                    <LuX class="w-4 h-4 text-emerald-700 dark:text-emerald-400" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Output Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm">
                    <div class="space-y-2">
                        <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Output Format</label>
                        <div class="relative">
                            <select
                                v-model="outputFormat"
                                class="block w-full pl-3 pr-10 py-2.5 text-sm font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg shadow-sm appearance-none transition-colors">
                                <option value="pdf">PDF Document (.pdf)</option>
                                <option value="png">PNG Image (.png)</option>
                                <option value="jpg">JPG Image (.jpg)</option>
                                <option value="pptx">PowerPoint (.pptx)</option>
                            </select>
                            <LuChevronDown class="absolute right-3 top-3 w-4 h-4 text-slate-400 pointer-events-none" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">File Naming Pattern</label>
                        <input
                            v-model="namingTemplate"
                            type="text"
                            class="block w-full px-4 py-2.5 text-sm font-mono font-medium border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors"
                            placeholder="{event}_{Fullname}_{date}" />
                        <p class="text-[0.65rem] font-semibold text-slate-500 dark:text-slate-400 mt-1">Placeholders: {event}, {Fullname}, {date}</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div
                    v-if="uploading"
                    class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
                    <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-2">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <LuUploadCloud class="w-4 h-4" />
                            Uploading files...
                        </span>
                        <span class="text-indigo-600 dark:text-indigo-400">{{ uploadProgress }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-inner">
                        <div
                            class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full transition-all duration-300 ease-out"
                            :style="{ width: `${uploadProgress}%` }"></div>
                    </div>
                </div>

                <!-- Server Status Panel -->
                <div
                    v-if="processingOnServer || serverStatus !== 'idle'"
                    class="flex items-center gap-4 p-5 rounded-xl border shadow-sm transition-all"
                    :class="{
                        'bg-blue-50 border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/30': statusConfig.color === 'blue',
                        'bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30': statusConfig.color === 'amber',
                        'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30': statusConfig.color === 'emerald',
                        'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/30': statusConfig.color === 'red',
                        'bg-slate-50 border-slate-200 dark:bg-slate-800/50 dark:border-slate-700': statusConfig.color === 'slate',
                    }">
                    <div
                        class="p-2 rounded-lg bg-white/50 dark:bg-slate-900/50 shadow-sm shrink-0"
                        :class="`text-${statusConfig.color}-600 dark:text-${statusConfig.color}-400`">
                        <component
                            :is="statusConfig.icon"
                            class="w-6 h-6"
                            :class="{ 'animate-spin': statusConfig.spin }" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-sm font-bold tracking-wide"
                            :class="`text-${statusConfig.color}-900 dark:text-${statusConfig.color}-200`">
                            {{ statusConfig.text }}
                        </p>
                        <p
                            v-if="message"
                            class="text-xs font-semibold mt-0.5 truncate"
                            :class="`text-${statusConfig.color}-700 dark:text-${statusConfig.color}-400/80`">
                            {{ message }}
                        </p>
                    </div>
                </div>

                <!-- Alerts -->
                <TransitionGroup
                    name="alert"
                    tag="div"
                    class="space-y-3">
                    <div
                        v-if="errorMessage"
                        key="error"
                        class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl shadow-sm">
                        <LuAlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-bold text-red-900 dark:text-red-200">Error</p>
                            <p class="text-sm font-medium text-red-700 dark:text-red-300 mt-0.5 leading-relaxed">
                                {{ errorMessage }}
                            </p>
                        </div>
                        <button
                            @click="errorMessage = ''"
                            class="p-1 hover:bg-red-100 dark:hover:bg-red-500/20 rounded-md transition-colors text-red-500">
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>

                    <div
                        v-if="message && !processingOnServer && serverStatus === 'completed'"
                        key="success"
                        class="flex items-start gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl shadow-sm">
                        <LuCheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Success</p>
                            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300 mt-0.5 leading-relaxed">
                                {{ message }}
                            </p>
                        </div>
                        <button
                            @click="message = ''"
                            class="p-1 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 rounded-md transition-colors text-emerald-500">
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>
                </TransitionGroup>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button
                        class="inline-flex items-center justify-center gap-2.5 flex-1 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black tracking-wide rounded-xl shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:pointer-events-none disabled:shadow-none"
                        :disabled="uploading || processingOnServer || !isReadyToProcess"
                        @click="submitForProcessing">
                        <LuLoader2
                            v-if="uploading || processingOnServer"
                            class="w-5 h-5 animate-spin" />
                        <LuPlay
                            v-else
                            class="w-5 h-5" />
                        {{ uploading ? "Uploading..." : processingOnServer ? "Processing..." : "Generate Certificates" }}
                    </button>

                    <button
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-black tracking-wide rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:pointer-events-none"
                        :disabled="serverStatus !== 'completed'"
                        @click="downloadZip">
                        <LuDownload class="w-5 h-5" />
                        Download ZIP
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <div class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 uppercase">
                    <LuInfo class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                    Template Formatting Guide
                </div>
            </div>
            <div class="p-6">
                <ul class="space-y-4 text-sm font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span>
                            Use placeholder format
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold">&lt;&lt;COLUMN_NAME&gt;&gt;</code>
                            in text boxes.
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span>
                            Match column names exactly from your data source (e.g.,
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold">fullname_8647</code>
                            ).
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span class="leading-relaxed">
                            You can use built-in system placeholders
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold">&lt;&lt;EVENT_TITLE&gt;&gt;</code>
                            ,
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold">&lt;&lt;EVENT_DATE&gt;&gt;</code>
                            , and
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold">&lt;&lt;DATE_GIVEN&gt;&gt;</code>
                            .
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span>Keep each placeholder inside a single text element—do not split them across multiple boxes.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span>
                            Save your template explicitly in
                            <strong>.pptx</strong>
                            format only.
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.alert-enter-active,
.alert-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
