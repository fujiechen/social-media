import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import {useGlobalStore} from "@/stores/global.store";

const baseUrl = `${process.env.VUE_APP_API_URL}/tutorials`;

export const useTutorialStore = defineStore({
  id: 'tutorial',
  state: () => ({
    tutorial: {},
  }),
  actions: {
    async fetchTutorial(os) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.get(`${baseUrl}` + '/' + os).then(response => {
        globalStore.loading = false;
        this.tutorial = response.data;
      })

    },
  }
});
