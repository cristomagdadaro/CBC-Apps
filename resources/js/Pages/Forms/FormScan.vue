<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import axios from "axios";
import CameraScanner from "@/Components/CameraScanner.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
// @ts-ignore
import AppLayout from "@/Layouts/AppLayout.vue";

const eventId = ref("");
const scanType = ref("checkin");
const terminalId = ref("");
const isProcessing = ref(false);
const lastScan = ref<any>(null);
const recentScans = ref<any[]>([]);

const status = ref("idle");
const statusMessage = ref("Ready to scan");
const statusDetail = ref("");

const statusClass = computed(() => {
    switch (status.value) {
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
});

const scanTypeLabel = computed(() => {
    return scanType.value.charAt(0).toUpperCase() + scanType.value.slice(1);
});

const setStatus = (nextStatus: string, message: string, detail = "") => {
    status.value = nextStatus;
    statusMessage.value = message;
    statusDetail.value = detail;
    playTone(nextStatus);
};

const handleDecode = async (text: string) => {
    if (!eventId.value) {
        setStatus("invalid", "Select event first", "Event ID is required.");
        return;
    }

    isProcessing.value = true;

    try {
        const response = await axios.post(`/api/forms/event/${eventId.value}/scan`, {
            payload: text,
            scan_type: scanType.value,
            terminal_id: terminalId.value || null,
        });

        lastScan.value = response.data;
        const scanStatus = response.data.status;
        const message = response.data.message || "Scan processed";
        const displayName = response.data?.registration?.name
            ? `${response.data.registration.name}`
            : "";

        setStatus(scanStatus, message, displayName);
        recentScans.value.unshift({
            ...response.data,
            scanned_at: response.data.scanned_at,
        });

        if (recentScans.value.length > 8) {
            recentScans.value.pop();
        }
    } catch (error: any) {
        const message = error?.response?.data?.message || "Scan failed";
        setStatus("invalid", message, "Try again.");
    } finally {
        isProcessing.value = false;
    }
};

const playTone = (toneStatus: string) => {
    try {
        const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
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
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    eventId.value = params.get("event_id") ?? "";

    terminalId.value = localStorage.getItem("formscan_terminal_id") || "";
    if (!terminalId.value) {
        terminalId.value = window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
        localStorage.setItem("formscan_terminal_id", terminalId.value);
    }
});
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
                                    <option value="certificate">Certificate</option>
                                    <option value="meal">Meal</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="workshop">Workshop</option>
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
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-600">Recent Scans</p>
                        <p class="text-xs text-slate-400">{{ recentScans.length }} / 8</p>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div
                            v-for="scan in recentScans"
                            :key="scan.scanned_at + scan.registration?.id"
                            class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2"
                        >
                            <div>
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ scan.registration?.name || "Unknown" }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ scan.scan_type }} • {{ scan.status }}
                                </p>
                            </div>
                            <span class="text-xs text-slate-400">{{ scan.scanned_at }}</span>
                        </div>
                        <p v-if="!recentScans.length" class="text-sm text-slate-400">No scans yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
