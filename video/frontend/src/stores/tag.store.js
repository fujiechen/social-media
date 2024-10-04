import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/medias/tags`;

export const useTagStore = defineStore({
  id: 'tags',
  state: () => ({
    tags: [],
    tag: {},
  }),
  actions: {
    async fetchAll() {
      await fetchWrapper.get(baseUrl, null)
        .then(response => {
        this.tags = response.data;
      })
    },

    async fetchOne(tagId) {
      await fetchWrapper.get(`${baseUrl}/${tagId}`, null)
        .then(response => {
          this.tag = response.data;
        })
    },

    async search(tagName) {
      await fetchWrapper.get(`${baseUrl}?name=${tagName}`, null)
        .then(response => {
          this.tags = response.data;
        })
    },
  }
});
