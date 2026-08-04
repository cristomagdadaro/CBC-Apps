import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const realtimeConfig = typeof window !== 'undefined' ? (window.__CBC_REALTIME__ || {}) : {};

window.Pusher = Pusher;

function resolvePort(forceTls) {
    if (forceTls) {
        return Number(import.meta.env.VITE_REVERB_PORT || 443);
    }

    return Number(import.meta.env.VITE_REVERB_PORT || 80);
}

if (typeof window !== 'undefined' && realtimeConfig.enabled !== false && reverbKey) {
    const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
    const forceTLS = scheme === 'https';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: resolvePort(false),
        wssPort: resolvePort(true),
        forceTLS,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        },
    });
}

export default window.Echo ?? null;
