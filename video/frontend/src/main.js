import {createApp, markRaw} from 'vue';
import App from './App.vue';
import router from './router';
import Vant, { Lazyload } from "vant";
import { createPinia } from 'pinia';
import "bootstrap";
import VuePlyr from 'vue-plyr'

import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.css";
import 'vant/lib/index.css';
import 'vue-plyr/dist/vue-plyr.css'
import "./styles/index.scss";

const pinia = createPinia();

pinia.use(({ store }) => {
    store.$router = markRaw(router);
});

createApp(App)
  .use(router)
  .use(Vant)
  .use(Lazyload)
  .use(pinia)
  .use(VuePlyr, {
    plyr: {}
  })
  .mount('#app');
