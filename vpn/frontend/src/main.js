import {createApp, markRaw} from 'vue';
import App from './App.vue';
import router from './router';
import Vant, { Lazyload } from "vant";
import { createPinia } from 'pinia';


import 'vant/lib/index.css';

const pinia = createPinia();

pinia.use(({ store }) => {
    store.$router = markRaw(router);
});

createApp(App)
    .use(router)
    .use(Vant)
    .use(Lazyload)
    .use(pinia)
    .mount('#app');

