<script>
export default {
    name: 'NotificationToast',
    data() {
        return {
            notifications: [],
            nextId: 1,
            typeStyles: {
                success: {
                    box: 'bg-green-50 border-green-300 text-green-900',
                    badge: 'bg-green-600',
                    title: 'Success',
                },
                error: {
                    box: 'bg-red-50 border-red-300 text-red-900',
                    badge: 'bg-red-600',
                    title: 'Error',
                },
                warning: {
                    box: 'bg-yellow-50 border-yellow-300 text-yellow-900',
                    badge: 'bg-yellow-500',
                    title: 'Warning',
                },
            },
        };
    },
    methods: {
        pushNotification(payload = {}) {
            const id = this.nextId++;
            const type = ['success', 'error', 'warning'].includes(payload.type) ? payload.type : 'success';
            const item = {
                id,
                type,
                message: payload.message || 'Notification',
                duration: Number(payload.duration ?? 10000),
            };
            this.notifications.unshift(item);
            if (item.duration > 0) {
                window.setTimeout(() => this.removeNotification(id), item.duration);
            }
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter((n) => n.id !== id);
        },
        listener(event) {
            this.pushNotification(event?.detail || {});
        },
    },
    mounted() {
        window.addEventListener('cbc:notify', this.listener);
    },
    beforeUnmount() {
        window.removeEventListener('cbc:notify', this.listener);
    },
};
</script>

<template>
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[min(92vw,380px)]">
        <transition-group name="toast" tag="div" class="flex flex-col gap-2">
            <div
                v-for="item in notifications"
                :key="item.id"
                class="border rounded-lg shadow-lg px-3 py-3 flex items-start gap-3"
                :class="typeStyles[item.type].box"
            >
                <span class="w-2.5 h-2.5 mt-1 rounded-full" :class="typeStyles[item.type].badge"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide">{{ typeStyles[item.type].title }}</p>
                    <p class="text-sm leading-tight break-words">{{ item.message }}</p>
                </div>
                <button
                    type="button"
                    class="text-xs opacity-70 hover:opacity-100"
                    @click="removeNotification(item.id)"
                >
                    ✕
                </button>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
