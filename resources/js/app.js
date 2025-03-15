/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

import { createApp } from 'vue';

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/Lobby.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

import Lobby from './components/Lobby.vue';
import GameView from './components/GameView.vue';
import AdminPanel from './components/AdminPanel.vue';
import Flash from './components/Flash.vue';
import ThemeChanger from './components/ThemeChanger.vue';


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Create the Vue app instance
const app = createApp({});

// Register the component
app.component('lobby', Lobby);
app.component('game-view', GameView);
app.component('admin-panel', AdminPanel);
app.component('flash', Flash);
app.component('theme-changer', ThemeChanger);

// Mount the app to the DOM element with id="app"
app.mount('#app');
