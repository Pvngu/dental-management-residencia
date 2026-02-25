import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging for debugging
Pusher.logToConsole = true;

// Function to get the current auth token
const getAuthToken = () => {
    return localStorage.getItem('auth_token');
};

// Initialize Laravel Echo with Reverb
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
    authEndpoint: '/api/v1/broadcasting/auth',
    auth: {
        headers: {
            get Authorization() {
                // Dynamically get the token each time auth is called
                const token = getAuthToken();
                return token ? `Bearer ${token}` : '';
            },
        },
    },
});

console.log('Laravel Echo initialized with Reverb', {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
});

// Helper function to update Echo auth headers when token changes
window.updateEchoAuth = (token) => {
    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.config.auth = {
            headers: {
                Authorization: token ? `Bearer ${token}` : '',
            },
        };
        console.log('Echo auth headers updated with new token');
    }
};
