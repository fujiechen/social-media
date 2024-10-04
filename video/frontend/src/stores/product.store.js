import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import { processParamsObjectToString } from "@/utils";

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/products`;

export const useProductStore = defineStore({
  id: 'products',
  state: () => ({
    products: [],
    product: {
      thumbnail_file: {
        url: '',
      }
    },
    showProductPopup: false,
    productPopupTopic: '购买产品',
  }),
  actions: {
    async fetchAll(params = null) {
      const paramsString = processParamsObjectToString(params);
      const response = await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null)
      this.products = response.data;
      return response.data;
    },

    async fetchOne(productId) {
      await fetchWrapper.get(`${baseUrl}/${productId}`, null)
        .then(response => {
          this.product = response.data;
        });
    },

    async fetchMediaProducts(mediaId, params= null) {
      const paramsString = processParamsObjectToString(params);
      await fetchWrapper.get(`${apiBaseUrl}/medias/${mediaId}/products${paramsString && '?'+paramsString}`, null)
        .then(response => {
          this.products = response.data;
        });
    },
  }
});
