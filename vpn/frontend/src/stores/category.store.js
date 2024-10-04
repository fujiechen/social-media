import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const apiBaseUrl = `${process.env.VUE_APP_API_URL}`;
const baseUrl = apiBaseUrl + '/categories';

export const useCategoriesStore = defineStore({
  id: 'categories',
  state: () => ({
    categories: [],
    category: {
      name: '',
      description: '',
      thumbnail_file: {
        url: '',
      }
    },
  }),
  actions: {
    async fetchAll() {
      await fetchWrapper.get(`${baseUrl}`, null)
        .then(response => {
        this.categories = response.data;
      })
    },

    async fetchOne(categoryId) {
      await fetchWrapper.get(`${baseUrl}/` + categoryId, null).then(response => {
        this.category = response.data;
        this.categories = response.data;
      })
    },
  }
});
