<script>
import { defineComponent, ref } from "vue";

export default defineComponent({
    name: "AuditInfoCard",
    props: {
        auditLogs: {
            type: Array,
            default: () => [],
        },
        createdAt: {
            type: String,
            default: null,
        },
        updatedAt: {
            type: String,
            default: null,
        },
    },
    setup() {
        const showHistory = ref(false);
        return { showHistory };
    },
    computed: {
        createdByInfo() {
            if (!this.auditLogs || this.auditLogs.length === 0) {
                return {
                    user: "Unknown",
                    timestamp: this.createdAt,
                };
            }

            const createdLog = this.auditLogs.find((log) => log.action === "created");
            if (createdLog) {
                return {
                    user: createdLog.actor_name || createdLog.user?.name || "Unknown",
                    timestamp: createdLog.created_at,
                };
            }

            return {
                user: "Unknown",
                timestamp: this.createdAt,
            };
        },
        lastModifiedByInfo() {
            if (!this.auditLogs || this.auditLogs.length === 0) {
                return {
                    user: "Unknown",
                    timestamp: this.updatedAt,
                };
            }

            // Find the last updated log
            const updatedLogs = this.auditLogs.filter((log) => log.action === "updated");
            if (updatedLogs.length > 0) {
                const lastUpdate = updatedLogs[0]; // Assuming logs are ordered by newest first
                return {
                    user: lastUpdate.actor_name || lastUpdate.user?.name || "Unknown",
                    timestamp: lastUpdate.created_at,
                };
            }

            return {
                user: "Unknown",
                timestamp: this.updatedAt,
            };
        },
        hasBeenModified() {
            return this.auditLogs && this.auditLogs.some((log) => log.action === "updated");
        },
        sortedAuditLogs() {
            if (!this.auditLogs) return [];
            return [...this.auditLogs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "N/A";
            return new Date(date).toLocaleString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },
});
</script>

<template>
    <div class="flex w-full flex-col gap-2 border-t border-gray-500 pt-3 text-sm leading-tight text-gray-400">
        <!-- Created Info -->
        <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-gray-500">
                Created by
                <span class="italic text-gray-500">{{ createdByInfo.user }}</span>
            </span>
            <span class="text-gray-400">
                {{ formatDate(createdByInfo.timestamp) }}
            </span>
        </div>

        <!-- Last Modified Info (only if modified) -->
        <div
            v-if="hasBeenModified"
            class="flex flex-col gap-0.5">
            <span class="font-semibold text-gray-500">
                Last Modified by
                <span class="italic text-gray-500">{{ lastModifiedByInfo.user }}</span>
            </span>
            <span class="text-gray-400">
                {{ formatDate(lastModifiedByInfo.timestamp) }}
            </span>
        </div>

        <!-- Not Modified Notice -->
        <div
            v-else
            class="italic text-gray-500">
            No modifications since creation
        </div>

        <!-- Full History Toggle & View -->
        <div v-if="hasBeenModified" class="mt-2 border-t border-gray-600/50 pt-2">
            <button
                type="button"
                @click="showHistory = !showHistory"
                class="text-xs font-semibold text-indigo-500 hover:text-indigo-400 transition-colors">
                {{ showHistory ? 'Hide Full History' : 'View Full History (' + (auditLogs.length - 1) + ' edits)' }}
            </button>
            
            <div v-if="showHistory" class="mt-3 flex flex-col gap-3">
                <div 
                    v-for="log in sortedAuditLogs" 
                    :key="log.id"
                    class="flex flex-col gap-0.5 border-l-2 border-indigo-500/30 pl-2">
                    <span class="font-semibold text-gray-500 text-xs">
                        {{ log.change_summary || (log.action === 'created' ? 'Created' : 'Updated') }} by
                        <span class="italic text-gray-400">{{ log.actor_name || log.user?.name || 'Unknown' }}</span>
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ formatDate(log.created_at) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
