import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const baseUrl = `${process.env.VUE_APP_API_URL}/apps`;

export const useAppStore = defineStore({
  id: 'app',
  state: () => ({
    appCategories: [],
    apps: [],
  }),
  actions: {
    async fetchAppCategories() {
      await fetchWrapper.get(`${baseUrl}` + '/categories').then(response => {
        this.appCategories = response.data;
      });
    },

    async fetchApps(appCategoryId) {
      await fetchWrapper.get(`${baseUrl}` + '?app_category_id=' + appCategoryId).then(response => {
        this.apps = response.data;
      });
    },
  }
});
