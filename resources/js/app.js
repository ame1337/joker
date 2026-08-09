// styles
import '../sass/app.scss';

// bootstrap js
import 'bootstrap';

// axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// jQuery
import jquery from 'jquery';
window.$ = window.jQuery = jquery

// websockets
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
if (window.App && window.App.user) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws'],
    });
}

// Vue.js
import { createApp } from 'vue';
import Lobby from './components/Lobby.vue';
import GameView from './components/GameView.vue';
import AdminPanel from './components/AdminPanel.vue';
import Flash from './components/Flash.vue';

const lobby = document.getElementById('lobby')
if (lobby) {
    createApp(Lobby, {
        initialGames: JSON.parse(lobby.dataset.initialGames),
    }).mount(lobby)
}

const game_view = document.getElementById('game-view')
if (game_view) {
    createApp(GameView, {
        gameId: JSON.parse(game_view.dataset.gameId),
        hasPassword: JSON.parse(game_view.dataset.hasPassword),
        pinCode: JSON.parse(game_view.dataset.pinCode),
        is_bot_disabled: JSON.parse(game_view.dataset.is_bot_disabled),
        init_bot_timer: JSON.parse(game_view.dataset.init_bot_timer),
    }).mount(game_view)
}

const admin_panel = document.getElementById('_admin-panel')
if (admin_panel) {
    createApp(AdminPanel, {
        gameId: JSON.parse(admin_panel.dataset.gameId),
    }).mount(admin_panel)
}

const flash = document.getElementById('flash')
if (flash) {
    createApp(Flash, {
        message: JSON.parse(flash.dataset.message),
        type: JSON.parse(flash.dataset.type),
    }).mount(flash)
}
