function realtimeConfig() {
    if (typeof window === 'undefined') {
        return {};
    }

    return window.__CBC_REALTIME__ || {};
}

export function isRealtimeFeatureEnabled(feature) {
    const config = realtimeConfig();

    if (config.enabled === false) {
        return false;
    }

    if (!feature) {
        return true;
    }

    return config.features?.[feature] !== false;
}

export function isRealtimeAvailable() {
    return typeof window !== 'undefined' && isRealtimeFeatureEnabled() && !!window.Echo;
}

export function subscribeToRealtimeChannels(subscriptions = []) {
    if (!isRealtimeAvailable()) {
        return () => {};
    }

    const cleanups = subscriptions.map((subscription) => {
        const type = subscription.type || 'private';
        const channelName = subscription.channel;
        const eventName = subscription.event;
        const handler = subscription.handler;
        const feature = subscription.feature;

        if (!channelName || !eventName || typeof handler !== 'function' || !isRealtimeFeatureEnabled(feature)) {
            return () => {};
        }

        const channel = type === 'public'
            ? window.Echo.channel(channelName)
            : window.Echo.private(channelName);

        channel.listen(`.${eventName}`, handler);

        return () => {
            channel.stopListening(`.${eventName}`);
            window.Echo.leave(channelName);
        };
    });

    return () => {
        cleanups.forEach((cleanup) => cleanup());
    };
}

export function resolveDatatableRealtimeSubscriptions(indexApi) {
    const map = {
        'api.inventory.transactions.index': [
            {
                type: 'private',
                channel: 'inventory.transactions',
                event: 'inventory.transaction.changed',
                feature: 'inventory',
            },
        ],
        'api.inventory.transactions.index.public': [
            {
                type: 'public',
                channel: 'public.inventory.stock',
                event: 'inventory.transaction.changed',
                feature: 'inventory',
            },
        ],
        'api.inventory.items.index': [
            {
                type: 'private',
                channel: 'inventory.items',
                event: 'reference-data.changed',
                feature: 'inventory',
                shouldRefresh: (payload) => payload?.domain === 'items',
            },
        ],
        'api.inventory.personnels.index': [
            {
                type: 'private',
                channel: 'inventory.personnels',
                event: 'reference-data.changed',
                feature: 'inventory',
                shouldRefresh: (payload) => payload?.domain === 'personnels',
            },
        ],
        'api.inventory.suppliers.index': [
            {
                type: 'private',
                channel: 'inventory.suppliers',
                event: 'reference-data.changed',
                feature: 'inventory',
                shouldRefresh: (payload) => payload?.domain === 'suppliers',
            },
        ],
        'api.laboratory.logs.index': [
            {
                type: 'private',
                channel: 'laboratory.logs',
                event: 'equipment.log.changed',
                feature: 'laboratory',
            },
        ],
        'api.research.samples.inventory.index': [
            {
                type: 'private',
                channel: 'research.samples',
                event: 'research.sample.inventory.changed',
                feature: 'research',
            },
        ],
    };

    return map[indexApi] || [];
}
