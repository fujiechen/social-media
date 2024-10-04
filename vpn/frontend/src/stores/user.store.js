import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import {showFailToast, showSuccessToast, showToast} from "vant";
import {useGlobalStore} from "@/stores/global.store";
import { useAuthStore } from "@/stores/auth.store";

const baseUrl = `${process.env.VUE_APP_API_URL}/user`;
const unionUrl = `${process.env.VUE_APP_UNION_API_URL}/user`;

export const useUserStore = defineStore({
  id: 'user',
  state: () => ({
    user: {},
    servers: [],
    userAccounts: [],
    userAccount: {
      currencyName: '',
      balance: 0,
    },
    userStatistics: {
      referrals: {
        users_count: 0,
        total_cash: 0,
      },
      categories: [],
    },
    userCategory: {},
  }),
  actions: {
    async fetchUnionUser() {
      await fetchWrapper.get(unionUrl).then(response => {
        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
      });
    },

    async fetchUser() {
      await fetchWrapper.get(baseUrl).then(response => {
        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
      })
    },

    async updateUserNickname() {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.put(unionUrl, {
        "nickname": this.user.nickname,
      }).then(response => {
        globalStore.loading = false;

        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
        showSuccessToast('更新成功');
      }).catch(error => {
        globalStore.loading = false;
        showToast({
          message: error.message,
          wordBreak: 'break-word',
        });
      })
    },

    async updateUserEmail(email) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.put(unionUrl, {
        "email": email,
      }).then(response => {
        globalStore.loading = false;
        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
        showSuccessToast('更新成功');
      }).catch(error => {
        globalStore.loading = false;
        if (error.reason === 'email') {
          showToast({
            message: '邮箱已被占用',
            wordBreak: 'break-word',
          });
        } else {
          showToast({
            message: '更新失败',
            wordBreak: 'break-word',
          });
        }
      });
    },

    async updateUserUsername(oldPassword, username) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.put(`${unionUrl}/auth`, {
        "old_password": oldPassword,
        "username": username,
      }).then(response => {
        globalStore.loading = false;
        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
        showSuccessToast('更新成功');
      }).catch(error => {
        globalStore.loading = false;
        if (error.reason === 'password') {
          showFailToast('密码错误');
        } else {
          showToast({
            message: '更新失败',
            wordBreak: 'break-word',
          });
        }
      })
    },

    async updateUserPassword(oldPassword, newPassword, passwordConfirmation) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.put(`${unionUrl}/auth`, {
        "old_password": oldPassword,
        "password": newPassword,
        "confirm_password": passwordConfirmation,
      }).then(response => {
        globalStore.loading = false;
        this.user = {
          ...this.user,
          ...response.data,
        };
        const authStore = useAuthStore();
        authStore.updateUser(this.user);
        showSuccessToast('更新成功');
      }).catch(error => {
        globalStore.loading = false;
        if (error.reason === 'password') {
          showFailToast('密码错误');
        } else if (error.reason === 'confirm_password') {
          showFailToast('密码不一致');
        } else {
          showFailToast(error.message);
        }
      })
    },

    async fetchUserCategory() {
      const response = await fetchWrapper.get(`${baseUrl}/category`);
      this.userCategory = response.data;
    },


    async fetchServers(categoryId) {
      const response = await fetchWrapper.get(`${baseUrl}/servers/${categoryId}`);
      this.servers = response.data;
    },

    async fetchAllUserAccounts() {
      await fetchWrapper.get(`${baseUrl}/payment/balance`)
        .then(response => {
        this.userAccounts = response.data;
      })
    },

    async fetchOneUserAccount(currencyName) {
      await fetchWrapper.get(`${baseUrl}/payment/balance?currency_name=${currencyName}`).then(response => {
        this.userAccount = response.data[0];
      })
    },

    async fetchUserStatistics() {
      await fetchWrapper.get(`${baseUrl}/statistics`).then(response => {
        this.userStatistics = response.data;
      })
    },

    async sendResetEmail(email) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;
      try {
        await fetchWrapper.post(`${baseUrl}/sendResetEmail`, {email});
        globalStore.loading = false;

        showSuccessToast('密码重置邮件已发送');
      } catch (error) {
        globalStore.loading = false;
        showToast({
          message: '邮箱不存在,邮件发送失败',
          wordBreak: 'break-word',
        });
      }
    },

    async confirmResetUserPassword(accessToken, newPassword, passwordConfirmation) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.put(`${unionUrl}/auth/reset`, {
        "access_token": accessToken,
        "password": newPassword,
        "confirm_password": passwordConfirmation,
      }).then(response => {
        globalStore.loading = false;
        this.user = {
          ...this.user,
          ...response.data,
        };

        const authStore = useAuthStore();
        authStore.updateUser(this.user);

        showSuccessToast('密码重置成功');
        this.$router.push('/');
      }).catch(error => {
        globalStore.loading = false;
        if (error.reason === 'access_token') {
          showFailToast('重置密码链接失效，请重新发送邮件');
        } else if (error.reason === 'password') {
          showFailToast('密码长度必须至少8位');
        } else if (error.reason === 'confirm_password') {
          showFailToast('密码不一致');
        } else {
          showFailToast(error.message);
        }
      });
    },


  }
});
