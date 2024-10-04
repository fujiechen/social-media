import { createApp } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import router from './routes';
import store from './store';
import App from './App.vue';
import i18n from './i18n';
import 'bootstrap';

createApp(App)
  .use(i18n)
  .use(VueApexCharts)
  .use(router)
  .use(store)
  .mount('#app');
