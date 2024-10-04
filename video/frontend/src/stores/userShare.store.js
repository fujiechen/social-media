import { defineStore } from 'pinia';
import { showFailToast } from "vant";
import { createUserShare, getUserShareById } from "@/services/user";

export const useUserShareStore = defineStore({
    id: 'userShare',
    state: () => ({
      sharePopup: {
        loading: false,
        show: false,
        userShare: {},
      },
    }),
    actions: {
      async getUserShare(id) {
        this.sharePopup.loading = true;
        try {
          this.sharePopup.userShare = await getUserShareById(id);
        } catch (e) {
          showFailToast('无法获取分享信息');
          this.sharePopup.userShare = {};
        }
        this.sharePopup.loading = false;
      },

      async createUserShare(type, shareableId, url) {
        this.sharePopup.loading = true;
        try {
          this.sharePopup.userShare = await createUserShare(type, shareableId, url);
        } catch (e) {
          this.sharePopup.userShare = {};
        }
        this.sharePopup.loading = false;
      },
    }
  })
;
