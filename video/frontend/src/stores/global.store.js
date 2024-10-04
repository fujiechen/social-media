import {defineStore} from 'pinia';
import {fetchWrapper} from "@/helpers/fetch-wrapper";


const apiBaseUrl = process.env.VUE_APP_API_URL;

export const useGlobalStore = defineStore({
  id: 'global',
  state: () => ({
    loading: false,
    showCustomerServicePopup: false,
    connected: false,
  }),
  actions: {
    async updateVpnConnectedStatus() {
      await fetchWrapper.get(`${apiBaseUrl}/server/connected`, null)
        .then(response => {
          this.connected = response.data.connected;
        })
    },
  }
});
