<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import * as XLSX from "xlsx";

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
            uploadedDataRows: [],
            uploadedDataColumns: [],
            detectedDataColumns: [],
            parsingDataFile: false,
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
            eventTitle: "",
            eventDate: "",
            dateGiven: new Date().toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }),
            sendEmails: false,
            sendingEmails: false,
            batchEmailsSent: false,
            pendingEmailCount: 0,
            generatingPreview: false,
            batchId: null,
            poller: null,
            realtimeCleanup: null,
            serverStatus: "idle",
            statusDismissed: false,
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
                return !!(this.selectedNameColumn && this.selectedEmailColumn && this.selectedRecipientIds.length > 0);
            }
            return !!(this.dataFile && !this.parsingDataFile && this.selectedNameColumn);
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
            if (value) {
                this.clearUploadedDataFile();
                this.syncColumnsFromEventData();
            } else {
                this.selectedNameColumn = "";
                this.selectedEmailColumn = "";
            }
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

            if (typeof data?.emails_sent !== "undefined") {
                this.batchEmailsSent = !!data.emails_sent;
            }
            if (typeof data?.pending_emails !== "undefined") {
                this.pendingEmailCount = Number(data.pending_emails || 0);
            } else if (data?.summary?.eligible_emails !== undefined && !data?.emails_sent) {
                this.pendingEmailCount = Number(data.summary.eligible_emails || 0);
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
                const successCount = summary.success ?? 0;
                const failCount = summary.fail ?? 0;
                if (!this.batchEmailsSent && (this.pendingEmailCount > 0 || !this.sendEmails)) {
                    this.message = `Generated ${successCount} certificate(s). Review rendered files before sending via email.`;
                } else {
                    this.message = `Completed! Success: ${successCount}, Failed: ${failCount}` + (this.batchEmailsSent ? " (Emails sent)" : "");
                }
                this.fetchResponseColumns();
                return;
            }

            this.processingOnServer = true;
        },
        resetMessages() {
            this.message = "";
            this.errorMessage = "";
            this.statusDismissed = false;
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
            if (type === "template") {
                this.templateFile = file;
            } else {
                this.dataFile = file;
                this.parseUploadedDataFile(file);
            }
            this.errorMessage = "";
        },
        clearUploadedDataFile() {
            this.dataFile = null;
            this.uploadedDataRows = [];
            this.uploadedDataColumns = [];
            this.detectedDataColumns = [];
            this.parsingDataFile = false;
        },
        async parseUploadedDataFile(file) {
            if (!file) {
                this.clearUploadedDataFile();
                return;
            }

            this.parsingDataFile = true;
            try {
                const buffer = await file.arrayBuffer();
                const workbook = XLSX.read(buffer, { type: "array", cellDates: true });
                const firstSheetName = workbook.SheetNames[0];
                if (!firstSheetName) {
                    throw new Error("The file has no sheets or content.");
                }

                const worksheet = workbook.Sheets[firstSheetName];
                const rawSheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1, raw: false, defval: "" });
                if (!rawSheetData || rawSheetData.length === 0) {
                    this.uploadedDataRows = [];
                    this.uploadedDataColumns = [];
                    this.detectedDataColumns = [];
                    return;
                }

                const rawHeaderRow = rawSheetData[0] || [];
                const detectedColumns = [];
                const tableColumns = [];

                rawHeaderRow.forEach((col, idx) => {
                    const title = String(col || "").replace(/^\ufeff/, "").trim().replace(/^["']|["']$/g, "") || `Column ${idx + 1}`;
                    detectedColumns.push(title);
                    tableColumns.push({
                        key: `col_${idx}`,
                        title: title,
                        label: title,
                        sortable: true,
                    });
                });

                const tableRows = [];
                for (let r = 1; r < rawSheetData.length; r++) {
                    const rowData = rawSheetData[r];
                    if (!rowData || rowData.length === 0) continue;
                    const hasContent = rowData.some((cell) => cell !== undefined && cell !== null && String(cell).trim() !== "");
                    if (!hasContent) continue;

                    const rowObj = { id: r };
                    tableColumns.forEach((colDef, idx) => {
                        const cellVal = rowData[idx];
                        rowObj[colDef.key] = cellVal !== undefined && cellVal !== null ? String(cellVal) : "";
                    });
                    tableRows.push(rowObj);
                }

                this.detectedDataColumns = detectedColumns;
                this.uploadedDataColumns = tableColumns;
                this.uploadedDataRows = tableRows;

                // Auto-detect Name Column
                const nameColMatch = detectedColumns.find((col) => /full\s*name|fullname|name|participant/i.test(col));
                if (nameColMatch) {
                    this.selectedNameColumn = nameColMatch;
                } else if (detectedColumns.length > 0 && !this.selectedNameColumn) {
                    this.selectedNameColumn = detectedColumns[0];
                }

                // Auto-detect Email Column
                const emailColMatch = detectedColumns.find((col) => /email|mail/i.test(col));
                if (emailColMatch) {
                    this.selectedEmailColumn = emailColMatch;
                } else if (detectedColumns.length > 1 && !this.selectedEmailColumn) {
                    this.selectedEmailColumn = detectedColumns[1];
                }

                // Auto-detect Event Title from file if present and not set
                const titleColMatch = detectedColumns.find((col) => /event[_\s]*title|^title$/i.test(col));
                if (titleColMatch && tableRows.length > 0 && !this.eventTitle) {
                    const val = tableRows[0][`col_${detectedColumns.indexOf(titleColMatch)}`];
                    if (val && String(val).trim()) this.eventTitle = String(val).trim();
                }

                // Auto-detect Event Date from file if present and not set
                const dateColMatch = detectedColumns.find((col) => /event[_\s]*date|^date$/i.test(col));
                if (dateColMatch && tableRows.length > 0 && !this.eventDate) {
                    const val = tableRows[0][`col_${detectedColumns.indexOf(dateColMatch)}`];
                    if (val && String(val).trim()) this.eventDate = String(val).trim();
                }
            } catch (err) {
                console.error("Failed to parse data file:", err);
                this.errorMessage = "Failed to parse data file: " + (err.message || "Invalid file format");
            } finally {
                this.parsingDataFile = false;
            }
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
                this.syncColumnsFromEventData();

                const eventInfo = payload?.event || null;
                if (eventInfo?.title && !this.eventTitle) {
                    this.eventTitle = eventInfo.title;
                }
                if (eventInfo?.formatted_date && !this.eventDate) {
                    this.eventDate = eventInfo.formatted_date;
                }
                if (!this.hasSavedTemplate) this.useSavedTemplate = false;
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error, "Failed to fetch response columns.");
            } finally {
                this.loadingColumns = false;
            }
        },
        syncColumnsFromEventData() {
            if (!this.responseColumns.length) return;
            if (!this.selectedEmailColumn || !this.responseColumns.includes(this.selectedEmailColumn)) {
                const emailMatch = this.responseColumns.find((col) => /email/i.test(String(col)));
                this.selectedEmailColumn = emailMatch || this.responseColumns[0] || "";
            }
            if (!this.selectedNameColumn || !this.responseColumns.includes(this.selectedNameColumn)) {
                const nameMatch = this.responseColumns.find((col) => /full\s*name|fullname|name/i.test(String(col)));
                this.selectedNameColumn = nameMatch || this.responseColumns[0] || "";
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
            if (!this.useEventData && !this.selectedNameColumn) {
                this.errorMessage = "Select a name column from your spreadsheet.";
                return;
            }
            if (this.useEventData && this.selectedRecipientIds.length === 0) {
                this.errorMessage = "Select at least one recipient.";
                return;
            }

            this.resetMessages();
            this.uploadProgress = 0;
            this.batchEmailsSent = false;
            this.pendingEmailCount = 0;
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
                    formData.append("send_emails", this.sendEmails ? "1" : "0");
                    formData.append("use_saved_template", this.useSavedTemplate ? "1" : "0");
                    formData.append("use_event_data", this.useEventData ? "1" : "0");
                    if (this.selectedNameColumn) formData.append("name_column", this.selectedNameColumn);
                    if (this.selectedEmailColumn) formData.append("email_column", this.selectedEmailColumn);
                    if (this.useEventData) {
                        if (this.selectedSubformType) formData.append("subform_type", this.selectedSubformType);
                        this.selectedRecipientIds.forEach((id) => formData.append("recipient_ids[]", id));
                    }
                    if (this.namingTemplate?.trim()) formData.append("name_template", this.namingTemplate.trim());
                    if (this.eventTitle?.trim()) formData.append("event_title", this.eventTitle.trim());
                    if (this.eventDate?.trim()) formData.append("event_date", this.eventDate.trim());
                    if (this.dateGiven?.trim()) formData.append("date_given", this.dateGiven.trim());

                    payload = formData;
                    requestConfig = {
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
                        send_emails: Boolean(this.sendEmails),
                        use_saved_template: this.useSavedTemplate,
                        use_event_data: this.useEventData,
                        ...(this.selectedNameColumn ? { name_column: this.selectedNameColumn } : {}),
                        ...(this.selectedEmailColumn ? { email_column: this.selectedEmailColumn } : {}),
                        ...(this.useEventData
                            ? {
                                  ...(this.selectedSubformType ? { subform_type: this.selectedSubformType } : {}),
                                  recipient_ids: this.selectedRecipientIds,
                              }
                            : {}),
                        ...(this.namingTemplate?.trim() ? { name_template: this.namingTemplate.trim() } : {}),
                        ...(this.eventTitle?.trim() ? { event_title: this.eventTitle.trim() } : {}),
                        ...(this.eventDate?.trim() ? { event_date: this.eventDate.trim() } : {}),
                        ...(this.dateGiven?.trim() ? { date_given: this.dateGiven.trim() } : {}),
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
        viewSavedTemplate() {
            if (!this.eventId) return;
            window.open(route("api.event.certificates.template.view", this.eventId), "_blank");
        },
        viewRenderedCertificate() {
            if (!this.eventId || !this.batchId || this.serverStatus !== "completed") return;
            window.open(route("api.event.certificates.preview", [this.eventId, this.batchId]), "_blank");
        },
        async sendBatchEmails() {
            if (!this.eventId || !this.batchId || this.sendingEmails) return;
            const count = this.pendingEmailCount;
            const confirmed = window.confirm(
                count > 0
                    ? `Are you ready to send generated certificates via email to ${count} recipient(s)?`
                    : "Are you ready to dispatch certificate emails to all eligible recipients?"
            );
            if (!confirmed) return;

            this.sendingEmails = true;
            this.resetMessages();
            try {
                const response = await this.fetchPostApi(
                    "api.event.certificates.send_emails",
                    {},
                    { routeParams: [this.eventId, this.batchId] }
                );
                this.batchEmailsSent = true;
                this.pendingEmailCount = 0;
                this.message = response?.data?.message || "Certificates are now being delivered to recipient inboxes.";
            } catch (error) {
                this.errorMessage = await this.resolveErrorMessage(error, "Failed to dispatch certificate emails.");
            } finally {
                this.sendingEmails = false;
            }
        },
        async previewSample() {
            if (this.generatingPreview || this.processingOnServer || !this.isReadyToProcess) return;

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
            if (!this.useEventData && !this.selectedNameColumn) {
                this.errorMessage = "Select a name column from your spreadsheet.";
                return;
            }

            this.resetMessages();
            this.generatingPreview = true;
            this.message = "Rendering sample certificate for preview...";

            try {
                const shouldUploadTemplate = !this.useSavedTemplate && !!this.templateFile;
                const shouldUploadData = !this.useEventData && !!this.dataFile;
                const hasFileUpload = shouldUploadTemplate || shouldUploadData;

                let payload;
                let headers = {};

                if (hasFileUpload) {
                    const formData = new FormData();
                    if (shouldUploadTemplate) formData.append("template", this.templateFile);
                    if (shouldUploadData) formData.append("data", this.dataFile);
                    formData.append("format", this.outputFormat);
                    formData.append("use_saved_template", this.useSavedTemplate ? "1" : "0");
                    formData.append("use_event_data", this.useEventData ? "1" : "0");
                    if (this.selectedNameColumn) formData.append("name_column", this.selectedNameColumn);
                    if (this.selectedEmailColumn) formData.append("email_column", this.selectedEmailColumn);
                    if (this.useEventData) {
                        if (this.selectedSubformType) formData.append("subform_type", this.selectedSubformType);
                        this.selectedRecipientIds.forEach((id) => formData.append("recipient_ids[]", id));
                    }
                    if (this.namingTemplate?.trim()) formData.append("name_template", this.namingTemplate.trim());
                    if (this.eventTitle?.trim()) formData.append("event_title", this.eventTitle.trim());
                    if (this.eventDate?.trim()) formData.append("event_date", this.eventDate.trim());
                    if (this.dateGiven?.trim()) formData.append("date_given", this.dateGiven.trim());

                    payload = formData;
                    headers["Content-Type"] = "multipart/form-data";
                } else {
                    payload = {
                        format: this.outputFormat,
                        use_saved_template: this.useSavedTemplate,
                        use_event_data: this.useEventData,
                        ...(this.selectedNameColumn ? { name_column: this.selectedNameColumn } : {}),
                        ...(this.selectedEmailColumn ? { email_column: this.selectedEmailColumn } : {}),
                        ...(this.useEventData
                            ? {
                                  ...(this.selectedSubformType ? { subform_type: this.selectedSubformType } : {}),
                                  recipient_ids: this.selectedRecipientIds,
                              }
                            : {}),
                        ...(this.namingTemplate?.trim() ? { name_template: this.namingTemplate.trim() } : {}),
                        ...(this.eventTitle?.trim() ? { event_title: this.eventTitle.trim() } : {}),
                        ...(this.eventDate?.trim() ? { event_date: this.eventDate.trim() } : {}),
                        ...(this.dateGiven?.trim() ? { date_given: this.dateGiven.trim() } : {}),
                    };
                }

                const response = await this.fetchPostApi(
                    "api.event.certificates.preview_sample",
                    payload,
                    {
                        routeParams: this.eventId,
                        headers,
                        responseType: "blob",
                    }
                );

                const headerType = response.headers?.["content-type"] || response.headers?.["Content-Type"];
                const contentType = headerType || (this.outputFormat === "pdf" ? "application/pdf" : "image/png");
                const blob = new Blob([response.data], { type: contentType });
                const objectUrl = window.URL.createObjectURL(blob);
                window.open(objectUrl, "_blank");
                this.message = "Sample preview opened in a new tab.";
            } catch (error) {
                let errMsg = "Failed to render sample preview.";
                if (error?.response?.data instanceof Blob) {
                    try {
                        const text = await error.response.data.text();
                        const json = JSON.parse(text);
                        errMsg = json.message || errMsg;
                    } catch (_) {}
                } else {
                    errMsg = await this.resolveErrorMessage(error, errMsg);
                }
                this.errorMessage = errMsg;
            } finally {
                this.generatingPreview = false;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Main Generator Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/80">
            <!-- Premium Header -->
            <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                <div class="absolute inset-0 bg-white/10 mix-blend-overlay dark:bg-black/10"></div>
                <div class="relative z-10 flex items-center gap-3.5">
                    <div class="shrink-0 rounded-xl border border-white/30 bg-white/20 p-2.5 shadow-sm">
                        <LuFileText class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black leading-none text-white drop-shadow-md">Bulk Certificate Generator</h3>
                        <p class="mt-1 text-sm font-semibold text-blue-100 drop-shadow-sm">Generate and email certificates automatically</p>
                    </div>
                </div>
            </div>

            <div class="space-y-8 p-6">
                <!-- Section 1: Template Source -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800/50">
                        <div class="flex items-center gap-2 text-sm font-bold uppercase text-slate-800 dark:text-slate-200">
                            <LuFileType class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                            Template Source
                        </div>
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 shadow-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:bg-slate-800"
                            :class="{ 'cursor-not-allowed opacity-50': !hasSavedTemplate }">
                            <input
                                v-model="useSavedTemplate"
                                type="checkbox"
                                :disabled="!hasSavedTemplate"
                                class="h-4 w-4 rounded border-slate-300 bg-white text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Use saved template</span>
                        </label>
                    </div>

                    <!-- Saved Template Info Box -->
                    <div
                        v-if="useSavedTemplate && hasSavedTemplate"
                        class="flex flex-col justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm sm:flex-row sm:items-center dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <div class="flex items-center gap-4">
                            <div class="rounded-xl border border-indigo-100 bg-white p-3 shadow-sm dark:border-indigo-500/30 dark:bg-slate-800">
                                <LuFileCheck class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-base font-bold text-slate-900 dark:text-white">
                                    {{ savedTemplateName }}
                                </p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">Event-specific template</p>
                            </div>
                        </div>
                        <button
                            @click="viewSavedTemplate"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm transition-all hover:bg-indigo-100 active:scale-95 sm:w-auto dark:border-indigo-500/30 dark:bg-slate-800 dark:text-indigo-300 dark:hover:bg-indigo-500/30">
                            <LuEye class="h-4 w-4" />
                            Preview
                        </button>
                    </div>

                    <!-- Upload Template Dropzone -->
                    <div
                        v-else
                        class="relative rounded-xl border-2 border-dashed p-8 text-center transition-all duration-300"
                        :class="dragOver.template ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 bg-slate-50/50 hover:border-indigo-400 dark:border-slate-700 dark:bg-slate-800/30 dark:hover:border-indigo-500/50'"
                        @dragover.prevent="onDragOver('template', $event)"
                        @dragleave.prevent="onDragLeave('template', $event)"
                        @drop.prevent="onDrop('template', $event)">
                        <input
                            type="file"
                            accept=".pptx"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            @change="onFileInput('template', $event)" />

                        <div
                            v-if="!templateFile"
                            class="pointer-events-none space-y-3">
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm transition-transform duration-300 dark:border-slate-700 dark:bg-slate-800"
                                :class="{
                                    'scale-110 border-indigo-300 dark:border-indigo-500': dragOver.template,
                                }">
                                <LuUploadCloud
                                    class="h-6 w-6 text-slate-400 dark:text-slate-500"
                                    :class="{
                                        'text-indigo-500 dark:text-indigo-400': dragOver.template,
                                    }" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Drop your .pptx template here or click to browse</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Maximum file size: 10MB</p>
                            </div>
                        </div>

                        <!-- Selected File -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <div class="pointer-events-auto flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                <LuFile class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                                    {{ templateFile.name }}
                                </span>
                                <div class="mx-1 h-4 w-px bg-emerald-200 dark:bg-emerald-500/30"></div>
                                <button
                                    @click.stop="templateFile = null"
                                    class="rounded-md p-1 transition-colors hover:bg-emerald-200 dark:hover:bg-emerald-500/30">
                                    <LuX class="h-4 w-4 text-emerald-700 dark:text-emerald-400" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Source -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm font-bold uppercase text-slate-800 dark:text-slate-200">
                            <LuDatabase class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                            Data Source to Template Column Mapping
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 shadow-sm transition-all hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:bg-slate-800">
                            <input
                                v-model="useEventData"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 bg-white text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Use event responses</span>
                        </label>
                    </div>

                    <!-- Event Data Configuration -->
                    <div
                        v-if="useEventData"
                        class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5 md:grid-cols-3 dark:border-slate-700/60 dark:bg-slate-800/30">
                            <div class="space-y-2">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Name Column</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedNameColumn"
                                        class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
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
                                    <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Email Column</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedEmailColumn"
                                        class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
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
                                    <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Subform Filter</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedSubformType"
                                        class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                        <option value="">All types</option>
                                        <option
                                            v-for="type in subformTypes"
                                            :key="type"
                                            :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Data Table (Recipients) -->
                        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex flex-col justify-between gap-3 border-b border-slate-200 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800/50">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <span class="inline-flex items-center justify-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-black text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400">
                                        {{ selectedRecipientCount }}
                                    </span>
                                    Possible Recipients
                                </div>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <input
                                        type="checkbox"
                                        :checked="allFilteredRecipientsSelected"
                                        @change="toggleAllFilteredRecipients"
                                        class="h-4 w-4 rounded border-slate-300 bg-slate-50 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900" />
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
                                            class="h-4 w-4 rounded border-slate-300 bg-slate-50 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800" />
                                    </template>

                                    <template #cell-name="{ row: recipient }">
                                        <span class="inline-block max-w-[150px] truncate align-bottom font-semibold text-slate-800 dark:text-slate-200">
                                            {{ recipientName(recipient) }}
                                        </span>
                                    </template>

                                    <template #cell-email="{ row: recipient }">
                                        <span class="inline-block max-w-[180px] truncate align-bottom font-medium text-slate-600 dark:text-slate-400">
                                            {{ recipientEmail(recipient) }}
                                        </span>
                                    </template>

                                    <template #cell-status="{ row: recipient }">
                                        <div class="flex w-fit flex-col gap-1">
                                            <span
                                                class="inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-[0.65rem] font-bold uppercase"
                                                :class="recipientCertificateStatusConfig(recipient).badge">
                                                {{ recipientCertificateStatusConfig(recipient).label }}
                                            </span>
                                            <span
                                                v-if="recipientCertificateStatus(recipient) === 'sent' && formatCertificateSentAt(recipient)"
                                                class="ml-1 text-[0.65rem] font-semibold text-slate-500 dark:text-slate-500">
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
                                class="flex flex-col items-center px-6 py-12 text-center text-sm font-medium text-slate-400 dark:text-slate-500">
                                <LuInbox class="mb-3 h-8 w-8 opacity-40" />
                                No recipients found for the selected filters.
                            </div>
                        </div>

                        <!-- Missing Data Warning -->
                        <div
                            v-if="!loadingColumns && responseColumns.length === 0"
                            class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-700 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                            <LuAlertTriangle class="h-5 w-5 shrink-0" />
                            No response data found for this event.
                        </div>
                    </div>

                    <!-- Upload Data Section -->
                    <div
                        v-else
                        class="space-y-4">
                        <!-- Upload Data Dropzone -->
                        <div
                            class="relative rounded-xl border-2 border-dashed p-6 text-center transition-all duration-300"
                            :class="dragOver.data ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 bg-slate-50/50 hover:border-indigo-400 dark:border-slate-700 dark:bg-slate-800/30 dark:hover:border-indigo-500/50'"
                            @dragover.prevent="onDragOver('data', $event)"
                            @dragleave.prevent="onDragLeave('data', $event)"
                            @drop.prevent="onDrop('data', $event)">
                            <input
                                type="file"
                                accept=".xlsx,.csv"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                @change="onFileInput('data', $event)" />

                            <div
                                v-if="!dataFile"
                                class="pointer-events-none space-y-3">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm transition-transform duration-300 dark:border-slate-700 dark:bg-slate-800"
                                    :class="{
                                        'scale-110 border-indigo-300 dark:border-indigo-500': dragOver.data,
                                    }">
                                    <LuSheet
                                        class="h-6 w-6 text-slate-400 dark:text-slate-500"
                                        :class="{
                                            'text-indigo-500 dark:text-indigo-400': dragOver.data,
                                        }" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Drop your Excel/CSV file here or click to browse</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Supports .xlsx and .csv formats up to 10MB</p>
                                </div>
                            </div>

                            <!-- Selected File -->
                            <div
                                v-else
                                class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <div class="pointer-events-auto flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-2.5 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                    <LuSheet class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                                        {{ dataFile.name }}
                                    </span>
                                    <span
                                        v-if="uploadedDataRows.length"
                                        class="rounded-full bg-emerald-200/60 px-2 py-0.5 text-[0.65rem] font-bold text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                                        {{ uploadedDataRows.length }} rows
                                    </span>
                                    <div class="mx-1 h-4 w-px bg-emerald-200 dark:bg-emerald-500/30"></div>
                                    <button
                                        type="button"
                                        @click.stop="clearUploadedDataFile"
                                        title="Remove file"
                                        class="rounded-md p-1 transition-colors hover:bg-emerald-200 dark:hover:bg-emerald-500/30">
                                        <LuX class="h-4 w-4 text-emerald-700 dark:text-emerald-400" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Parsing Indicator -->
                        <div
                            v-if="parsingDataFile"
                            class="flex items-center justify-center gap-3 rounded-xl border border-indigo-200 bg-indigo-50/50 p-6 text-sm font-semibold text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <LuLoader2 class="h-5 w-5 animate-spin" />
                            Parsing spreadsheet data and detecting columns...
                        </div>

                        <!-- Column Mapping Dropdowns for Uploaded Data -->
                        <div
                            v-if="dataFile && uploadedDataColumns.length > 0 && !parsingDataFile"
                            class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5 md:grid-cols-2 dark:border-slate-700/60 dark:bg-slate-800/30">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                                        Name Column <span class="text-indigo-500 font-bold">*</span>
                                    </label>
                                    <span
                                        v-if="selectedNameColumn"
                                        class="text-[0.65rem] font-semibold text-emerald-600 dark:text-emerald-400">
                                        Mapped: {{ selectedNameColumn }}
                                    </span>
                                </div>
                                <div class="relative">
                                    <select
                                        v-model="selectedNameColumn"
                                        class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                        <option
                                            value=""
                                            disabled>
                                            Select column for recipient name
                                        </option>
                                        <option
                                            v-for="col in detectedDataColumns"
                                            :key="`uploaded-name-${col}`"
                                            :value="col">
                                            {{ col }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                                        Email Column <span class="text-indigo-500 font-bold">*</span>
                                    </label>
                                    <span
                                        v-if="selectedEmailColumn"
                                        class="text-[0.65rem] font-semibold text-emerald-600 dark:text-emerald-400">
                                        Mapped: {{ selectedEmailColumn }}
                                    </span>
                                </div>
                                <div class="relative">
                                    <select
                                        v-model="selectedEmailColumn"
                                        class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                        <option
                                            value=""
                                            disabled>
                                            Select column for recipient email
                                        </option>
                                        <option
                                            v-for="col in detectedDataColumns"
                                            :key="`uploaded-email-${col}`"
                                            :value="col">
                                            {{ col }}
                                        </option>
                                    </select>
                                    <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Data Table (Uploaded Content Preview) -->
                        <div
                            v-if="dataFile && uploadedDataRows.length > 0 && !parsingDataFile"
                            class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex flex-col justify-between gap-3 border-b border-slate-200 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800/50">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-black text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
                                        {{ uploadedDataRows.length }}
                                    </span>
                                    File Records Preview
                                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">
                                        ({{ uploadedDataColumns.length }} columns detected)
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <LuSheet class="h-4 w-4 text-emerald-600" />
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ dataFile.name }}</span>
                                </div>
                            </div>

                            <div>
                                <DataTable
                                    :mode="'offline'"
                                    :rows="uploadedDataRows"
                                    :columns="uploadedDataColumns"
                                    class="border-t-0"
                                    :enablePagination="true"
                                    :enableSearch="true"
                                    :enableFilters="true"
                                    :enableExport="true"
                                    emptyMessage="No records found in this file." />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Certificate Details -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-sm font-bold uppercase text-slate-800 dark:border-slate-800/50 dark:text-slate-200">
                        <LuAward class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                        Certificate Details (Event Title & Dates)
                    </div>

                    <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:p-5 md:grid-cols-3 dark:border-slate-700/60 dark:bg-slate-800/30">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                                    Event Title
                                </label>
                                <span class="rounded bg-indigo-50 px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    &lt;&lt;EVENT_TITLE&gt;&gt;
                                </span>
                            </div>
                            <input
                                v-model="eventTitle"
                                type="text"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                placeholder="e.g. Innovate for Impact..." />
                            <p class="text-[0.65rem] text-slate-500 dark:text-slate-400">Populates &lt;&lt;EVENT_TITLE&gt;&gt; and {event_title}</p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                                    Event Date
                                </label>
                                <span class="rounded bg-indigo-50 px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    &lt;&lt;EVENT_DATE&gt;&gt;
                                </span>
                            </div>
                            <input
                                v-model="eventDate"
                                type="text"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                placeholder="e.g. October 15, 2026" />
                            <p class="text-[0.65rem] text-slate-500 dark:text-slate-400">Populates &lt;&lt;EVENT_DATE&gt;&gt; and {event_date}</p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                                    Date Given / Issued
                                </label>
                                <span class="rounded bg-indigo-50 px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    &lt;&lt;DATE_GIVEN&gt;&gt;
                                </span>
                            </div>
                            <input
                                v-model="dateGiven"
                                type="text"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                placeholder="e.g. October 15, 2026" />
                            <p class="text-[0.65rem] text-slate-500 dark:text-slate-400">Populates &lt;&lt;DATE_GIVEN&gt;&gt; and {date_given}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Output Settings & Delivery Mode -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm md:grid-cols-2 dark:border-slate-700/60 dark:bg-slate-800/30">
                        <div class="space-y-2">
                            <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Output Format</label>
                            <div class="relative">
                                <select
                                    v-model="outputFormat"
                                    class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                    <option value="pdf">PDF Document (.pdf)</option>
                                    <option value="png">PNG Image (.png)</option>
                                    <option value="jpg">JPG Image (.jpg)</option>
                                    <option value="pptx">PowerPoint (.pptx)</option>
                                </select>
                                <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">File Naming Pattern</label>
                            <input
                                v-model="namingTemplate"
                                type="text"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-mono text-sm font-medium text-slate-800 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                placeholder="{event}_{Fullname}_{date}" />
                            <p class="mt-1 text-[0.65rem] font-semibold text-slate-500 dark:text-slate-400">Placeholders: {FirstName}, {Fullname}, {event}, {event_id}, {event_title}, {date}, {timestamp}</p>
                        </div>
                    </div>

                    <!-- Email Delivery Mode Toggle -->
                    <div class="flex flex-col items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:flex-row sm:items-center dark:border-slate-700/60 dark:bg-slate-800/30">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                <LuMail class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Email Certificates Automatically</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ sendEmails ? 'Certificates will be automatically emailed to recipients upon generation.' : 'Review mode: Generate and review certificates first before dispatching emails.' }}
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                v-model="sendEmails"
                                type="checkbox"
                                class="peer sr-only" />
                            <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:bg-slate-700 dark:after:border-slate-600 dark:after:bg-slate-200 dark:peer-checked:bg-indigo-500"></div>
                        </label>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div
                    v-if="uploading"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="mb-2 flex justify-between text-xs font-bold uppercase">
                        <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                            <LuUploadCloud class="h-4 w-4" />
                            Uploading files...
                        </span>
                        <span class="text-indigo-600 dark:text-indigo-400">{{ uploadProgress }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 shadow-inner dark:bg-slate-800">
                        <div
                            class="h-full rounded-full bg-indigo-600 transition-all duration-300 ease-out dark:bg-indigo-500"
                            :style="{ width: `${uploadProgress}%` }"></div>
                    </div>
                </div>

                <!-- Server Status Panel -->
                <div
                    v-if="(processingOnServer || serverStatus !== 'idle') && !statusDismissed"
                    class="flex items-center gap-4 rounded-xl border p-5 shadow-sm transition-all"
                    :class="{
                        'border-blue-200 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/10': statusConfig.color === 'blue',
                        'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10': statusConfig.color === 'amber',
                        'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10': statusConfig.color === 'emerald',
                        'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10': statusConfig.color === 'red',
                        'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50': statusConfig.color === 'slate',
                    }">
                    <div
                        class="shrink-0 rounded-lg bg-white/50 p-2 shadow-sm dark:bg-slate-900/50"
                        :class="`text-${statusConfig.color}-600 dark:text-${statusConfig.color}-400`">
                        <component
                            :is="statusConfig.icon"
                            class="h-6 w-6"
                            :class="{ 'animate-spin': statusConfig.spin }" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-bold"
                            :class="`text-${statusConfig.color}-900 dark:text-${statusConfig.color}-200`">
                            {{ statusConfig.text }}
                        </p>
                        <p
                            v-if="message"
                            class="mt-0.5 truncate text-xs font-semibold"
                            :class="`text-${statusConfig.color}-700 dark:text-${statusConfig.color}-400/80`">
                            {{ message }}
                        </p>
                    </div>
                    <button
                        v-if="!processingOnServer && ['completed', 'failed'].includes(serverStatus)"
                        @click="statusDismissed = true"
                        class="rounded-md p-1 transition-colors"
                        :class="`text-${statusConfig.color}-500 hover:bg-${statusConfig.color}-100 dark:hover:bg-${statusConfig.color}-500/20`">
                        <LuX class="h-4 w-4" />
                    </button>
                </div>

                <!-- Alerts -->
                <TransitionGroup
                    name="alert"
                    tag="div"
                    class="space-y-3">
                    <div
                        v-if="errorMessage"
                        key="error"
                        class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
                        <LuAlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
                        <div class="flex-1">
                            <p class="text-sm font-bold text-red-900 dark:text-red-200">Error</p>
                            <p class="mt-0.5 text-sm font-medium leading-relaxed text-red-700 dark:text-red-300">
                                {{ errorMessage }}
                            </p>
                        </div>
                        <button
                            @click="errorMessage = ''"
                            class="rounded-md p-1 text-red-500 transition-colors hover:bg-red-100 dark:hover:bg-red-500/20">
                            <LuX class="h-4 w-4" />
                        </button>
                    </div>
                </TransitionGroup>

                <!-- Action Buttons -->
                <div class="flex flex-col flex-wrap gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center dark:border-slate-800">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition-all duration-200 hover:border-indigo-300 hover:bg-slate-50 active:scale-95 disabled:pointer-events-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-500/50 dark:hover:bg-slate-700/80"
                        :disabled="uploading || processingOnServer || generatingPreview || !isReadyToProcess"
                        @click="previewSample"
                        title="Render a sample certificate for the first recipient in a new tab">
                        <LuLoader2
                            v-if="generatingPreview"
                            class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" />
                        <LuEye
                            v-else
                            class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        {{ generatingPreview ? "Rendering Preview..." : "Preview Sample" }}
                    </button>

                    <button
                        type="button"
                        class="inline-flex flex-1 items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-black text-white shadow-md shadow-indigo-600/20 transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                        :disabled="uploading || processingOnServer || generatingPreview || !isReadyToProcess"
                        @click="submitForProcessing">
                        <LuLoader2
                            v-if="uploading || processingOnServer"
                            class="h-5 w-5 animate-spin" />
                        <LuPlay
                            v-else
                            class="h-5 w-5" />
                        {{ uploading ? "Uploading..." : processingOnServer ? "Processing..." : (sendEmails ? "Generate & Email Certificates" : "Generate Certificates for Review") }}
                    </button>

                    <button
                        v-if="serverStatus === 'completed'"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50/80 px-5 py-3.5 text-sm font-bold text-indigo-700 shadow-sm transition-all duration-200 hover:bg-indigo-100 active:scale-95 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                        @click="viewRenderedCertificate"
                        title="View rendered certificate file in a new browser tab">
                        <LuEye class="h-5 w-5" />
                        Review File
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-5 py-3.5 text-sm font-black text-slate-700 transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 active:scale-95 disabled:pointer-events-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700/80"
                        :disabled="serverStatus !== 'completed'"
                        @click="downloadZip">
                        <LuDownload class="h-5 w-5" />
                        Download ZIP
                    </button>

                    <button
                        v-if="serverStatus === 'completed' && !batchEmailsSent && (pendingEmailCount > 0 || !sendEmails)"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-black text-white shadow-md shadow-emerald-600/20 transition-all duration-200 hover:bg-emerald-700 hover:shadow-lg active:scale-95 disabled:pointer-events-none disabled:opacity-50"
                        :disabled="sendingEmails"
                        @click="sendBatchEmails">
                        <LuLoader2
                            v-if="sendingEmails"
                            class="h-5 w-5 animate-spin" />
                        <LuSend
                            v-else
                            class="h-5 w-5" />
                        {{ sendingEmails ? "Sending Emails..." : pendingEmailCount > 0 ? `Send via Email (${pendingEmailCount})` : "Send via Email" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/20">
                <div class="flex items-center gap-2 text-sm font-bold uppercase text-slate-800 dark:text-slate-200">
                    <LuInfo class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                    Template Formatting Guide
                </div>
            </div>
            <div class="p-6">
                <ul class="space-y-4 text-sm font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <LuCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                        <span>
                            Use placeholder format
                            <code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">&lt;&lt;COLUMN_NAME&gt;&gt;</code>
                            in text boxes.
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                        <span>
                            Match column names exactly from your data source (e.g.,
                            <code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">fullname_8647</code>
                            ).
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                        <span class="leading-relaxed">
                            You can use built-in system placeholders
                            <code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">&lt;&lt;EVENT_TITLE&gt;&gt;</code>
                            ,
                            <code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">&lt;&lt;EVENT_DATE&gt;&gt;</code>
                            , and
                            <code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">&lt;&lt;DATE_GIVEN&gt;&gt;</code>
                            .
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                        <span>Keep each placeholder inside a single text element—do not split them across multiple boxes.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <LuCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
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
