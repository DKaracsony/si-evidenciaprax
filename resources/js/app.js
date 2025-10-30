import './bootstrap';
import { createApp } from 'vue';
import '../scss/app.scss';
import { router } from './router'

import App from './App.vue';

createApp(App).use(router).mount('#app');
