import LanguageConstants from '@/constants/Language';
import BrowserConstants from '@/constants/Browser';
import unionClient from './union';
import client from './api';

const BASE_URL = 'auth';

class Auth {
  async login(user) {
    return unionClient
      .post(`${BASE_URL}/login`, {
        username: user.username,
        password: user.password,
        captcha_key: user.captcha_key,
        captcha: user.captcha,
      })
      .then((axiosResponse) => {
        const response = axiosResponse.data;
        let userData = {};
        if (axiosResponse.status === 200 && response.data?.access_token) {
          const newUser = response.data;
          userData = {
            ...newUser,
            language: LanguageConstants.beToFe[newUser.language],
          };
          localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify(userData));
        } else {
          throw Error('cannot find access token');
        }

        return userData;
      });
  }

  async overrideUser(token) {
    localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify({
      access_token: token,
    }));
    return unionClient.get('user')
      .then((axiosResponse) => {
        const response = axiosResponse.data;
        if (axiosResponse.status === 200 && response.data?.access_token) {
          const newUser = response.data;
          const userData = {
            ...newUser,
            language: LanguageConstants.beToFe[newUser.language],
          };
          localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify(userData));
          return userData;
        }

        throw Error('cannot find access token');
      }).catch(() => {
        localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_USER_KEY);
        window.location.href = `${process.env.VUE_APP_SUB_PATH}sign-in`;
        return null;
      });
  }

  // we don't need logout for now as the token never expire
  // logout() {
  //   return unionClient.post(`${BASE_URL}/logout`)
  //     .then(() => {
  //       localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_USER_KEY);
  //     }).catch(() => {
  //       localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_USER_KEY);
  //     });
  // }

  register(user) {
    return unionClient
      .post(`${BASE_URL}/register`, {
        username: user.username,
        nickname: user.nickname,
        email: user.email,
        password: user.password,
        confirm_password: user.passwordConfirm,
        language: LanguageConstants.feToBe[user.language],
        captcha_key: user.captcha_key,
        captcha: user.captcha,
      })
      .then((axiosResponse) => {
        const response = axiosResponse.data;
        let userData = {};
        if (response?.data?.access_token) {
          const newUser = response.data;
          userData = {
            ...newUser,
            language: LanguageConstants.beToFe[newUser.language],
          };
          localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify(userData));
        } else {
          throw Error('Cannot find access token');
        }

        return userData;
      });
  }

  getCaptcha() {
    return unionClient
      .get(`${BASE_URL}/captcha`)
      .then((axiosResponse) => axiosResponse.data);
  }

  reset(email) {
    return client
      .post('/user/sendResetEmail', {
        email,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  resetConfirm(token, pass, confirmPass) {
    return unionClient
      .put('user/auth/reset', {
        access_token: token,
        password: pass,
        confirm_password: confirmPass,
      })
      .then((axiosResponse) => {
        const response = axiosResponse.data;
        let userData = {};
        if (axiosResponse.status === 200 && response.data?.access_token) {
          const newUser = response.data;
          userData = {
            ...newUser,
            language: LanguageConstants.beToFe[newUser.language],
          };
          localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify(userData));
        } else {
          throw Error('cannot find access token');
        }

        return userData;
      });
  }
}

export default new Auth();
