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
                    return "bg-emerald-100 text-emerald-800 border-emerald-300";
                case "already_scanned":
                    return "bg-yellow-100 text-yellow-800 border-yellow-300";
                case "invalid":
                case "wrong_event":
                case "full":
                case "ineligible":
                    return "bg-rose-100 text-rose-800 border-rose-300";
                default:
                    return "bg-slate-100 text-slate-700 border-slate-200";
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
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-5">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end">
                            <div>
                                <label class="text-xs uppercase tracking-[0.3em] text-slate-400">Event ID</label>
                                <input
                                    v-model="eventId"
                                    type="text"
                                    maxlength="4"
                                    class="mt-1 w-32 rounded-xl border border-slate-200 px-3 py-2 text-lg font-semibold tracking-widest text-slate-700"
                                    placeholder="0000"
                                />
                            </div>
                            <div>
                                <label class="text-xs uppercase tracking-[0.3em] text-slate-400">Scan Type</label>
                                <select v-model="scanType" class="mt-1 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <option value="checkin">Check-in</option>
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch">Lunch</option>
                                     <option value="dinner">Dinner</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="snack_am">Snack (AM)</option>
                                    <option value="snack_pm">Snack (PM)</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-[2fr,1fr]">
                        <div>
                            <CameraScanner
                                :enabled="!isProcessing && !!eventId"
                                label="Available Camera Devices"
                                :defaultOpenSmall="true"
                                @decoded="handleDecode"
                                @error="(err) => setStatus('invalid', 'Camera error', err?.toString() || 'Unknown error')"
                            />
                            <p class="text-xs text-slate-400 mt-2">Scanning for {{ scanTypeLabel }} • {{ eventId || "Select event" }}</p>
                        </div>

                        <div class="flex flex-col gap-3">
                            <div :class="['rounded-xl border px-4 py-3', statusClass]">
                                <p class="text-xs uppercase tracking-[0.3em]">Status</p>
                                <p class="text-lg font-semibold mt-1">{{ statusMessage }}</p>
                                <p class="text-sm mt-1" v-if="statusDetail">{{ statusDetail }}</p>
                            </div>

                            <div class="rounded-xl border border-slate-200 px-4 py-3 bg-slate-50">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Last Scan</p>
                                <p class="text-sm font-semibold text-slate-700" v-if="lastScan?.registration?.name">
                                    {{ lastScan.registration.name }}
                                </p>
                                <p class="text-xs text-slate-500" v-if="lastScan?.registration?.organization">
                                    {{ lastScan.registration.organization }}
                                </p>
                                <p class="text-xs text-slate-400 mt-2" v-if="lastScan?.registration?.id">
                                    {{ lastScan.registration.id }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-600">Recent Scans</p>
                            <p class="text-xs text-slate-400">Search participants and filter by scan type</p>
                        </div>
                        <p class="text-xs text-slate-400">{{ filteredRecentScans.length }} / {{ recentScans.length }}</p>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-[2fr,1fr]">
                        <input
                            v-model="recentScanSearch"
                            type="text"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm"
                            placeholder="Search by name, email, organization, status"
                        />
                        <select
                            v-model="recentScanTypeFilter"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm"
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
                    <div class="mt-3">
                        <data-table
                            :api-response="recentScansTableResponse"
                            :processing="false"
                            :model="recentScanModel"
                            :append-actions="false"
                        />
                        <div v-if="!filteredRecentScans.length" class="text-sm text-slate-400 mt-2">
                            No matching scans.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
