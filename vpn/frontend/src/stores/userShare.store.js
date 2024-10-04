import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import {useGlobalStore} from "@/stores/global.store";
import {showFailToast} from "vant";

const baseUrl = `${process.env.VUE_APP_API_URL}/user`;

export const useUserShareStore = defineStore({
    id: 'userShare',
    state: () => ({
      userShares: [],
      userChildren: [],
      userChildrenOrders: [],
      userPayouts: [],
      otherUserPayouts: [],
      newUserShare: {
        background_image: {
          url: ''
        }
      },
      loadingNewUserShare: false,
      showUserSharePopup: false,
    }),
    actions: {
      async fetchUserShares() {
        const globalStore = useGlobalStore();
        globalStore.loading = true;
        await fetchWrapper.get(`${baseUrl}/shares`).then(response => {
          this.userShares = response.data;
          globalStore.loading = false;
        });
      },

      async findUserShare(id) {
        await fetchWrapper.get(`${baseUrl}/shares/` + id)
          .then(response => {
          this.newUserShare = response.data;
        })
      },

      async createUserShares(type, shareableId, url) {
        this.loadingNewUserShare = true;
        await fetchWrapper.post(`${baseUrl}/shares`, {
          type: type,
          shareable_id: shareableId,
          url: url,
        }).then(response => {
          this.loadingNewUserShare = false;
          this.newUserShare = response.data;
        }).catch(() => {
          showFailToast('无法获取分享信息');
          this.loadingNewUserShare = false;
          this.newUserShare = {};
        });
      },

      async fetchUserChildren() {
        const globalStore = useGlobalStore();
        globalStore.loading = true;
        await fetchWrapper.get(`${baseUrl}/children`).then(response => {
          this.userChildren = response.data;
          globalStore.loading = false;
        })
      },

      async fetchUserChildrenOrders() {
        const globalStore = useGlobalStore();
        globalStore.loading = true;
        await fetchWrapper.get(`${baseUrl}/children/orders`).then(response => {
          this.userChildrenOrders = response.data;
          globalStore.loading = false;
        })
      },

      async fetchUserPayouts() {
        const globalStore = useGlobalStore();
        globalStore.loading = true;
        await fetchWrapper.get(`${baseUrl}/payouts`).then(response => {
          this.userPayouts = response.data;
          globalStore.loading = false;
        });
      },

      async fetchOtherUserPayouts() {
        await fetchWrapper.get(`${baseUrl}/payouts/others`).then(response => {
          this.otherUserPayouts = response.data;
        });
      }
    }
  })
;
