<script>
import axios from "axios";
import CameraScanner from "@/Components/CameraScanner.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";

class FormScanRecentRowModel {
    static getColumns() {
        return [
            {
                title: "Name",
                key: "name",
                db_key: "name",
                align: "text-left",
                sortable: false,
                visible: true,
            },
            {
                title: "Email",
                key: "email",
                db_key: "email",
                align: "text-left",
                sortable: false,
                visible: true,
            },
            {
                title: "Organization",
                key: "organization",
                db_key: "organization",
                align: "text-left",
                sortable: false,
                visible: true,
            },
            {
                title: "Scan Type",
                key: "scan_type",
                db_key: "scan_type",
                align: "text-center",
                sortable: false,
                visible: true,
            },
            {
                title: "Status",
                key: "status",
                db_key: "status",
                align: "text-center",
                sortable: false,
                visible: true,
            },
            {
                title: "Scanned At",
                key: "scanned_at",
                db_key: "scanned_at",
                align: "text-center",
                sortable: false,
                visible: true,
            },
        ];
    }
}

export default {
    components: {
        CameraScanner,
        FormsHeaderActions,
        DataTable,
    },
    props: {
        event_id: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            eventId: "",
            scanType: "checkin",
            terminalId: "",
            isProcessing: false,
            lastScan: null,
            recentScans: [],
            status: "idle",
            statusMessage: "Ready to scan",
            statusDetail: "",
            recentScanSearch: "",
            recentScanTypeFilter: "all",
            recentScanModel: FormScanRecentRowModel,
        };
    },
    computed: {
        statusClass() {
            switch (this.status) {
                case "success":
                    return "bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30";
                case "already_scanned":
                    return "bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-400 border-amber-200 dark:border-amber-500/30";
                case "invalid":
                case "wrong_event":
                case "full":
                case "ineligible":
                    return "bg-rose-50 dark:bg-rose-500/10 text-rose-800 dark:text-rose-400 border-rose-200 dark:border-rose-500/30";
                default:
                    return "bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700";
            }
        },
        statusIcon() {
            switch (this.status) {
                case "success":
                    return "LuCheckCircle2";
                case "already_scanned":
                    return "LuAlertCircle";
                case "invalid":
                case "wrong_event":
                case "full":
                case "ineligible":
                    return "LuXCircle";
                default:
                    return "LuScanLine";
            }
        },
        scanTypeLabel() {
            return this.scanType.charAt(0).toUpperCase() + this.scanType.slice(1);
        },
        scanTypeFilterOptions() {
            return [
                { label: "All types", value: "all" },
                { label: "Check-in", value: "checkin" },
                { label: "Breakfast", value: "breakfast" },
                { label: "Lunch", value: "lunch" },
                { label: "Dinner", value: "dinner" },
                { label: "Certificate", value: "certificate" },
                { label: "Snack (AM)", value: "snack_am" },
                { label: "Snack (PM)", value: "snack_pm" },
            ];
        },
        filteredRecentScans() {
            const search = (this.recentScanSearch || "").trim().toLowerCase();

            return this.recentScans.filter((scan) => {
                if (this.recentScanTypeFilter !== "all" && scan.scan_type !== this.recentScanTypeFilter) {
                    return false;
                }

                if (!search) {
                    return true;
                }

                const name = (scan.registration?.name || "").toLowerCase();
                const email = (scan.registration?.email || "").toLowerCase();
                const organization = (scan.registration?.organization || "").toLowerCase();
                const registrationId = (scan.registration?.id || "").toLowerCase();
                const scanType = (scan.scan_type || "").toLowerCase();
                const status = (scan.status || "").toLowerCase();

                return [name, email, organization, registrationId, scanType, status].some((entry) => entry.includes(search));
            });
        },
        recentScansTableResponse() {
            return {
                from: 1,
                data: this.filteredRecentScans.map((scan) => ({
                    id: `${scan.scanned_at}-${scan.registration?.id || scan.status}`,
                    name: scan.registration?.name || "Unknown",
                    email: scan.registration?.email || "-",
                    organization: scan.registration?.organization || "-",
                    scan_type: scan.scan_type,
                    status: scan.status,
                    scanned_at: scan.scanned_at,
                })),
            };
        },
    },
    methods: {
        setStatus(nextStatus, message, detail = "") {
            this.status = nextStatus;
            this.statusMessage = message;
            this.statusDetail = detail;
            this.playTone(nextStatus);
        },
        async handleDecode(text) {
            if (!this.eventId) {
                this.setStatus("invalid", "Select event first", "Event ID is required.");
                return;
            }
            this.isProcessing = true;
            try {
                const response = await axios.post(`/api/forms/event/${this.eventId}/scan`, {
                    payload: text,
                    scan_type: this.scanType,
                    terminal_id: this.terminalId || null,
                });
                this.lastScan = response.data;
                const scanStatus = response.data.status;
                const message = response.data.message || "Scan processed";
                const displayName = response.data?.registration?.name ? `${response.data.registration.name}` : "";
                this.setStatus(scanStatus, message, displayName);
                this.recentScans.unshift({
                    ...response.data,
                    scanned_at: response.data.scanned_at,
                });
                if (this.recentScans.length > 8) {
                    this.recentScans.pop();
                }
            } catch (error) {
                const message = error?.response?.data?.message || "Scan failed";
                this.setStatus("invalid", message, "Try again.");
            } finally {
                this.isProcessing = false;
            }
        },
        playTone(toneStatus) {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                oscillator.type = "sine";
                oscillator.frequency.value = toneStatus === "success" ? 880 : toneStatus === "already_scanned" ? 520 : 220;
                gainNode.gain.value = 0.06;
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.12);
            } catch (error) {
                // Audio feedback is optional
            }
        },
    },
    mounted() {
        const params = new URLSearchParams(window.location.search);
        this.eventId = params.get("event_id") ?? "";
        this.terminalId = localStorage.getItem("formscan_terminal_id") || "";
        if (!this.terminalId) {
            this.terminalId = window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
            localStorage.setItem("formscan_terminal_id", this.terminalId);
        }

        if (this.event_id) {
            this.eventId = this.event_id;
        }
    },
};
</script>

<template>
    <AppLayout title="FormScan">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="space-y-6 sm:p-4 lg:p-8">
            <!-- Main Scanner & Controls Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/80">
                <!-- Control Panel Header -->
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800 dark:bg-slate-800/20">
                    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div class="flex w-full flex-col gap-4 md:flex-row md:items-end">
                            <!-- Event ID Input -->
                            <div>
                                <label class="mb-2 block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Event ID</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                        <LuHash class="h-4 w-4 text-slate-400" />
                                    </div>
                                    <input
                                        v-model="eventId"
                                        type="text"
                                        maxlength="4"
                                        class="block w-36 rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-lg font-black tracking-widest text-slate-900 shadow-sm transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        placeholder="0000" />
                                </div>
                            </div>

                            <!-- Scan Type Input -->
                            <div class="flex-1 md:max-w-xs">
                                <label class="mb-2 block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Scan Type</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                        <LuTags class="h-4 w-4 text-slate-400" />
                                    </div>
                                    <select
                                        v-model="scanType"
                                        class="block w-full appearance-none rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-10 text-sm font-semibold text-slate-700 shadow-sm transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                        <option
                                            v-for="option in scanTypeFilterOptions.filter((o) => o.value !== 'all')"
                                            :key="option.value"
                                            :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scanner & Status Display -->
                <div class="grid gap-6 p-6 md:grid-cols-[1.5fr,1fr] lg:gap-8">
                    <!-- Left Side: Camera -->
                    <div class="flex flex-col">
                        <div class="relative min-h-[300px] flex-1 overflow-visible">
                            <CameraScanner
                                v-if="eventId"
                                :enabled="!isProcessing"
                                label="Available Camera Devices"
                                :defaultOpenSmall="true"
                                @decoded="handleDecode"
                                @error="(err) => setStatus('invalid', 'Camera error', err?.toString() || 'Unknown error')"
                                class="absolute inset-0 h-full w-full object-cover"
                                :scannerHeight="'360px'" />
                            <div
                                v-else
                                class="absolute inset-0 flex flex-col items-center justify-center rounded-3xl border p-6 text-center backdrop-blur-sm">
                                <div class="mb-4 rounded-full bg-slate-800/80 p-4 shadow-inner ring-1 ring-white/10">
                                    <LuScanLine class="h-8 w-8 text-slate-100" />
                                </div>
                                <p class="text-lg font-bold text-slate-700 dark:text-slate-200">Event ID Required</p>
                                <p class="mt-2 max-w-[200px] text-sm font-medium text-slate-500">Please enter an Event ID above to initialize the scanner.</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between px-1">
                            <p class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                <LuScanLine class="h-3.5 w-3.5" />
                                Scanning for
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ scanTypeLabel }}
                                </span>
                            </p>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                Event:
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ eventId || "None selected" }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Side: Status & Last Scan -->
                    <div class="flex flex-col gap-5">
                        <!-- Dynamic Status Box -->
                        <div :class="['flex min-h-[140px] flex-col justify-center rounded-2xl border p-5 shadow-sm transition-colors duration-300', statusClass]">
                            <div class="flex items-start gap-4">
                                <component
                                    :is="statusIcon"
                                    class="mt-1 h-8 w-8 shrink-0 opacity-90" />
                                <div>
                                    <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-widest opacity-70">Current Status</p>
                                    <p class="text-xl font-black leading-tight tracking-tight">
                                        {{ statusMessage }}
                                    </p>
                                    <p
                                        class="mt-1.5 text-sm font-semibold opacity-90"
                                        v-if="statusDetail">
                                        {{ statusDetail }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Last Scan ID Card -->
                        <div class="flex-1 rounded-2xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="mb-4 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Last Scanned Profile</p>

                            <div
                                v-if="lastScan?.registration?.name"
                                class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-indigo-100 text-xl font-bold text-indigo-600 shadow-inner dark:border-indigo-500/30 dark:bg-indigo-500/20 dark:text-indigo-400">
                                    {{ lastScan.registration.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-bold text-slate-900 dark:text-white">
                                        {{ lastScan.registration.name }}
                                    </p>
                                    <p
                                        class="mt-0.5 truncate text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        v-if="lastScan?.registration?.organization">
                                        {{ lastScan.registration.organization }}
                                    </p>
                                    <div
                                        class="mt-2.5 inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                                        v-if="lastScan?.registration?.id">
                                        <LuHash class="h-3 w-3 text-slate-400" />
                                        <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                                            {{ lastScan.registration.id }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex h-full flex-col items-center justify-center pb-4 text-slate-400 opacity-70 dark:text-slate-500">
                                <LuUserSquare class="mb-2 h-10 w-10 stroke-1" />
                                <span class="text-sm font-semibold">Waiting for scan...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans Data Table Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/80">
                <!-- Table Header -->
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <LuHistory class="h-5 w-5 text-indigo-500" />
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Scans</h3>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Search participants and filter by scan type</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 dark:border-slate-700 dark:bg-slate-800">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Showing</span>
                        <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">
                            {{ filteredRecentScans.length }}
                            <span class="mx-0.5 font-medium text-slate-400">/</span>
                            {{ recentScans.length }}
                        </span>
                    </div>
                </div>

                <!-- Search & Filter Controls -->
                <div class="mb-5 grid gap-4 md:grid-cols-[2fr,1fr]">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <LuSearch class="h-4 w-4 text-slate-400" />
                        </div>
                        <input
                            v-model="recentScanSearch"
                            type="text"
                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-medium text-slate-900 shadow-sm transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            placeholder="Search by name, email, organization, or status..." />
                    </div>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <LuFilter class="h-4 w-4 text-slate-400" />
                        </div>
                        <select
                            v-model="recentScanTypeFilter"
                            class="block w-full appearance-none rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-9 pr-10 text-sm font-semibold text-slate-700 shadow-sm transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option
                                v-for="option in scanTypeFilterOptions"
                                :key="option.value"
                                :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="mt-2">
                    <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                        <data-table
                            :api-response="recentScansTableResponse"
                            :processing="false"
                            :model="recentScanModel"
                            :append-actions="false" />
                    </div>
                    <div
                        v-if="!filteredRecentScans.length"
                        class="py-8 text-center">
                        <LuSearchX class="mx-auto mb-3 h-8 w-8 text-slate-300 dark:text-slate-600" />
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No matching scans found.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
