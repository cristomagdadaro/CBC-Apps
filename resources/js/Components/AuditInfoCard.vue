<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'AuditInfoCard',
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
    computed: {
        createdByInfo() {
            if (!this.auditLogs || this.auditLogs.length === 0) {
                return {
                    user: 'Unknown',
                    timestamp: this.createdAt,
                };
            }

            const createdLog = this.auditLogs.find(log => log.action === 'created');
            if (createdLog) {
                return {
                    user: createdLog.user?.name || 'Unknown',
                    timestamp: createdLog.created_at,
                };
            }

            return {
                user: 'Unknown',
                timestamp: this.createdAt,
            };
        },
        lastModifiedByInfo() {
            if (!this.auditLogs || this.auditLogs.length === 0) {
                return {
                    user: 'Unknown',
                    timestamp: this.updatedAt,
                };
            }

            // Find the last updated log
            const updatedLogs = this.auditLogs.filter(log => log.action === 'updated');
            if (updatedLogs.length > 0) {
                const lastUpdate = updatedLogs[0]; // Assuming logs are ordered by newest first
                return {
                    user: lastUpdate.user?.name || 'Unknown',
                    timestamp: lastUpdate.created_at,
                };
            }

            return {
                user: 'Unknown',
                timestamp: this.updatedAt,
            };
        },
        hasBeenModified() {
            return this.auditLogs && this.auditLogs.some(log => log.action === 'updated');
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
});
</script>

<template>
    <div class="flex flex-col w-full text-xs text-gray-400 border-t border-gray-500 pt-3 gap-2">
        <!-- Created Info -->
        <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-gray-500">Created</span>
            <span class="text-gray-400">
                {{ formatDate(createdByInfo.timestamp) }}
            </span>
            <span class="text-gray-500 italic">
                by {{ createdByInfo.user }}
            </span>
        </div>

        <!-- Last Modified Info (only if modified) -->
        <div v-if="hasBeenModified" class="flex flex-col gap-0.5">
            <span class="font-semibold text-gray-500">Last Modified</span>
            <span class="text-gray-400">
                {{ formatDate(lastModifiedByInfo.timestamp) }}
            </span>
            <span class="text-gray-500 italic">
                by {{ lastModifiedByInfo.user }}
            </span>
        </div>

        <!-- Not Modified Notice -->
        <div v-else class="text-gray-500 italic">
            No modifications since creation
        </div>
    </div>
</template>

<style scoped>
</style>
