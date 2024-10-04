import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const baseUrl = `${process.env.VUE_APP_API_URL}/metas`;

export const useMetaStore = defineStore({
  id: 'meta',
  state: () => ({
    metas: []
  }),
  actions: {
    /**
     * @returns {Promise<void>}
     */
    async fetchMetas() {
      await fetchWrapper.get(`${baseUrl}`).then(response => {
        this.metas = response.data;
      })
    },
  }
});
