import { defineStore } from "pinia";

import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { showFailToast } from "vant";
import { useGlobalStore } from "@/stores/global.store";
import { useUserStore } from "@/stores/user.store";

const apiBaseUrl = process.env.VUE_APP_UNION_API_URL;
const baseUrl = `${apiBaseUrl}/auth`;

export const useAuthStore = defineStore({
  id: 'auth',
  state: () => ({
    // initialize state from local storage to enable user to stay logged in
    user: JSON.parse(localStorage.getItem('video-user')),
  }),
  actions: {
    async login(
      username,
      password,
      captcha_key,
      captcha,
    ) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      try {
        const response = await fetchWrapper.post(`${baseUrl}/login`, {
          username,
          password,
          captcha_key,
          captcha,
        });

        this.updateUser(response.data);

        // call user show to register user on video
        const userStore = useUserStore();
        await userStore.fetchUser();

        globalStore.loading = false;

        // redirect to previous url or default to home page
        const returnUrl = localStorage.getItem('video-ref');
        if (returnUrl) {
          localStorage.removeItem('video-ref');
          window.location.href = returnUrl;
        } else {
          this.$router.push('/account/index');
        }

        return true;
      } catch (error) {
        globalStore.loading = false;
        showFailToast({
          message: '登录失败',
          wordBreak: 'break-word',
        });
        return false;
      }
    },

    async register(
      username,
      password,
      confirm_password,
      nickname,
      email,
      language,
      userShareId,
      captcha_key,
      captcha,
    ) {
      const globalStore = useGlobalStore();

      globalStore.loading = true;

      let response = null;
      try {
        // register account on union
        response = await fetchWrapper.post(`${baseUrl}/register`, {
          username,
          password,
          confirm_password,
          nickname,
          email,
          language,
          user_share_id: userShareId,
          captcha_key,
          captcha,
        });
      } catch (error) {
        if (error.reason === 'captcha') {
          showFailToast('验证码错误');
        } else if (error.reason === 'username') {
          showFailToast('登录名已被占用');
        } else if (error.reason === 'email') {
          showFailToast('邮箱已被占用');
        } else if (error.reason === 'confirm_password') {
          showFailToast('密码不一致');
        } else if (error.reason === 'password') {
          showFailToast('密码至少8位数');
        } else {
          showFailToast('注册失败');
        }
        globalStore.loading = false;
        return false;
      }

      this.updateUser(response.data);

      // call user show to register user on video
      const userStore = useUserStore();
      await userStore.fetchUser();

      globalStore.loading = false;

      // redirect to previous url or default to home page
      const returnUrl = localStorage.getItem('video-ref');
      if (returnUrl) {
        localStorage.removeItem('video-ref');
        window.location.href = returnUrl;
      } else {
        this.$router.push('/');
      }

      return true;
    },

    logout() {
      this.user = null;
      localStorage.removeItem('video-user');
      localStorage.removeItem('video-user_share_id');
      this.$router.push('/login');
    },

    async getCaptcha() {
      try {
        return await fetchWrapper.get(`${baseUrl}/captcha`, null);
      } catch (e) {
        showFailToast({
          message: '验证码获取失败，请刷新重试',
          wordBreak: 'break-word',
        });
        return {
          captcha_key: '',
          captcha_image: '',
        };
      }
    },

    updateUser(user) {
      this.user = {
        ...user,
      };
      localStorage.removeItem('video-user');
      localStorage.setItem('video-user', JSON.stringify(user));
    },
  }
});
