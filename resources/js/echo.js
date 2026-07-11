import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Not in use: hosted Pusher Channels, for hosts that can't run a persistent
// Reverb process (e.g. shared hosting). To switch back on, set
// VITE_BROADCASTER=pusher + VITE_PUSHER_APP_KEY/VITE_PUSHER_APP_CLUSTER in
// .env and restore the ternary below.
// import.meta.env.VITE_BROADCASTER === 'pusher'
//     ? {
//         broadcaster: 'pusher',
//         key: import.meta.env.VITE_PUSHER_APP_KEY,
//         cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//         forceTLS: true,
//     }
//     :
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
