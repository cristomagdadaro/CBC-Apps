<script>
import axios from "axios";
import CameraScanner from "@/Components/CameraScanner.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";

class FormScanRecentRowModel {
    static getColumns() {
        return [
            { title: 'Name', key: 'name', db_key: 'name', align: 'text-left', sortable: false, visible: true },
            { title: 'Email', key: 'email', db_key: 'email', align: 'text-left', sortable: false, visible: true },
            { title: 'Organization', key: 'organization', db_key: 'organization', align: 'text-left', sortable: false, visible: true },
            { title: 'Scan Type', key: 'scan_type', db_key: 'scan_type', align: 'text-center', sortable: false, visible: true },
            { title: 'Status', key: 'status', db_key: 'status', align: 'text-center', sortable: false, visible: true },
            { title: 'Scanned At', key: 'scanned_at', db_key: 'scanned_at', align: 'text-center', sortable: false, visible: true },
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
                { label: 'All types', value: 'all' },
                { label: 'Check-in', value: 'checkin' },
                { label: 'Breakfast', value: 'breakfast' },
                { label: 'Lunch', value: 'lunch' },
                { label: 'Dinner', value: 'dinner' },
                { label: 'Certificate', value: 'certificate' },
                { label: 'Snack (AM)', value: 'snack_am' },
                { label: 'Snack (PM)', value: 'snack_pm' },
            ];
        },
        filteredRecentScans() {
            const search = (this.recentScanSearch || '').trim().toLowerCase();

            return this.recentScans.filter((scan) => {
                if (this.recentScanTypeFilter !== 'all' && scan.scan_type !== this.recentScanTypeFilter) {
                    return false;
                }

                if (!search) {
                    return true;
                }

                const name = (scan.registration?.name || '').toLowerCase();
                const email = (scan.registration?.email || '').toLowerCase();
                const organization = (scan.registration?.organization || '').toLowerCase();
                const registrationId = (scan.registration?.id || '').toLowerCase();
                const scanType = (scan.scan_type || '').toLowerCase();
                const status = (scan.status || '').toLowerCase();

                return [name, email, organization, registrationId, scanType, status].some((entry) => entry.includes(search));
            });
        },
        recentScansTableResponse() {
            return {
                from: 1,
                data: this.filteredRecentScans.map((scan) => ({
                    id: `${scan.scanned_at}-${scan.registration?.id || scan.status}`,
                    name: scan.registration?.name || 'Unknown',
                    email: scan.registration?.email || '-',
                    organization: scan.registration?.organization || '-',
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
                const displayName = response.data?.registration?.name
                    ? `${response.data.registration.name}`
                    : "";
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

        <div class="py-10">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Main Scanner & Controls Card -->
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden transition-all duration-300">
                    
                    <!-- Control Panel Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                        <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                            <div class="flex flex-col gap-4 md:flex-row md:items-end w-full">
                                
                                <!-- Event ID Input -->
                                <div>
                                    <label class="text-[0.65rem] uppercase font-bold tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Event ID</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <LuHash class="w-4 h-4 text-slate-400" />
                                        </div>
                                        <input
                                            v-model="eventId"
                                            type="text"
                                            maxlength="4"
                                            class="block w-36 pl-10 pr-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-lg font-black tracking-widest text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all"
                                            placeholder="0000"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Scan Type Input -->
                                <div class="flex-1 md:max-w-xs">
                                    <label class="text-[0.65rem] uppercase font-bold tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Scan Type</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <LuTags class="w-4 h-4 text-slate-400" />
                                        </div>
                                        <select v-model="scanType" class="block w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all appearance-none">
                                            <option v-for="option in scanTypeFilterOptions.filter(o => o.value !== 'all')" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Scanner & Status Display -->
                    <div class="p-6 grid gap-6 lg:gap-8 md:grid-cols-[1.5fr,1fr]">
                        
                        <!-- Left Side: Camera -->
                        <div class="flex flex-col">
                            <div class="rounded-2xl overflow-hidden border-2 border-slate-200/50 dark:border-slate-700/50 bg-black shadow-inner flex-1 min-h-[300px] relative">
                                <CameraScanner
                                    :enabled="!isProcessing && !!eventId"
                                    label="Available Camera Devices"
                                    :defaultOpenSmall="true"
                                    @decoded="handleDecode"
                                    @error="(err) => setStatus('invalid', 'Camera error', err?.toString() || 'Unknown error')"
                                    class="absolute inset-0 w-full h-full object-cover"
                                />
                            </div>
                            <div class="mt-3 flex items-center justify-between px-1">
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <LuScanLine class="w-3.5 h-3.5" />
                                    Scanning for <span class="text-slate-700 dark:text-slate-300">{{ scanTypeLabel }}</span>
                                </p>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Event: <span class="text-slate-700 dark:text-slate-300">{{ eventId || "None selected" }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Right Side: Status & Last Scan -->
                        <div class="flex flex-col gap-5">
                            
                            <!-- Dynamic Status Box -->
                            <div :class="['rounded-2xl border p-5 transition-colors duration-300 shadow-sm flex flex-col justify-center min-h-[140px]', statusClass]">
                                <div class="flex items-start gap-4">
                                    <component :is="statusIcon" class="w-8 h-8 mt-1 shrink-0 opacity-90" />
                                    <div>
                                        <p class="text-[0.65rem] uppercase font-bold tracking-widest opacity-70 mb-1">Current Status</p>
                                        <p class="text-xl font-black tracking-tight leading-tight">{{ statusMessage }}</p>
                                        <p class="text-sm font-semibold mt-1.5 opacity-90" v-if="statusDetail">{{ statusDetail }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Last Scan ID Card -->
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/40 p-5 shadow-sm flex-1">
                                <p class="text-[0.65rem] uppercase font-bold tracking-widest text-slate-500 dark:text-slate-400 mb-4">Last Scanned Profile</p>
                                
                                <div v-if="lastScan?.registration?.name" class="flex gap-4 items-start">
                                    <div class="h-12 w-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shrink-0 border border-indigo-200 dark:border-indigo-500/30 shadow-inner">
                                        {{ lastScan.registration.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-bold text-slate-900 dark:text-white truncate">{{ lastScan.registration.name }}</p>
                                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5 truncate" v-if="lastScan?.registration?.organization">
                                            {{ lastScan.registration.organization }}
                                        </p>
                                        <div class="mt-2.5 inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm" v-if="lastScan?.registration?.id">
                                            <LuHash class="w-3 h-3 text-slate-400" />
                                            <span class="text-[0.65rem] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">{{ lastScan.registration.id }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="flex flex-col items-center justify-center h-full text-slate-400 dark:text-slate-500 opacity-70 pb-4">
                                    <LuUserSquare class="w-10 h-10 mb-2 stroke-1" />
                                    <span class="text-sm font-semibold">Waiting for scan...</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Recent Scans Data Table Card -->
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden transition-all duration-300 p-6">
                    
                    <!-- Table Header -->
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <LuHistory class="w-5 h-5 text-indigo-500" />
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Scans</h3>
                            </div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Search participants and filter by scan type</p>
                        </div>
                        <div class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Showing</span>
                            <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ filteredRecentScans.length }} <span class="text-slate-400 font-medium mx-0.5">/</span> {{ recentScans.length }}</span>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="grid gap-4 md:grid-cols-[2fr,1fr] mb-5">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <LuSearch class="w-4 h-4 text-slate-400" />
                            </div>
                            <input
                                v-model="recentScanSearch"
                                type="text"
                                class="block w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all"
                                placeholder="Search by name, email, organization, or status..."
                            />
                        </div>
                        
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <LuFilter class="w-4 h-4 text-slate-400" />
                            </div>
                            <select
                                v-model="recentScanTypeFilter"
                                class="block w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all appearance-none"
                            >
                                <option
                                    v-for="option in scanTypeFilterOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="mt-2">
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                            <data-table
                                :api-response="recentScansTableResponse"
                                :processing="false"
                                :model="recentScanModel"
                                :append-actions="false"
                            />
                        </div>
                        <div v-if="!filteredRecentScans.length" class="text-center py-8">
                            <LuSearchX class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No matching scans found.</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </AppLayout>
</template>