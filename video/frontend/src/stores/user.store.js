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
    user: {
      top_user_role: {
        role: {
          slug: '',
          name: '',
        }
      }
    },
    userAccounts: [],
    userStatistics: {
      publisher: {
        subscribers_count: 0,
        subscriptions_count: 0,
      },
      referrals: {
        orders_count: 0,
        shares_count: 0,
        total_cash: 0,
        total_points: 0,
        users_count: 0,
      },
    },
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
      });
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

    async fetchOneUserAccount(currencyName) {
      const response = await fetchWrapper.get(`${baseUrl}/payment/balance?currency_name=${currencyName}`, null);
      const newAccount = response.data[0];

      const index = this.userAccounts.findIndex(account => account.accountNumber === newAccount.accountNumber);
      if (index !== -1) {
        this.userAccounts[index] = newAccount;
      } else {
        this.userAccounts.push(newAccount);
      }
    },

    async fetchUserStatistics() {
      const response = await fetchWrapper.get(`${baseUrl}/statistics`, null);
      this.userStatistics = response.data;
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
