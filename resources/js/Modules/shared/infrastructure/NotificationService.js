export default class NotificationService {
    static notifications = [];

    constructor(payload = {}) {
        this.payload = payload;
        this.timestamp = Date.now();
    }

    static addNotification(notification) {
        const entry = notification instanceof NotificationService
            ? notification
            : new NotificationService(notification);

        NotificationService.notifications.push(entry);

        if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new CustomEvent('app-notification', { detail: entry }));
        }
    }

    static getNotifications() {
        return NotificationService.notifications;
    }

    static clear() {
        NotificationService.notifications = [];
    }
}
