import axios from 'axios';
import BrowserConstants from '@/constants/Browser';
import LanguageConstants from '@/constants/Language';

const UNION_URL = process.env.VUE_APP_API_UNION_URL;

const union = axios.create({
  baseURL: UNION_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

// Request Interceptor
union.interceptors.request.use(
  (config) => {
    const user = JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_USER_KEY));
    const guest = JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_GUEST_KEY));
    if (user && user.access_token) {
      // eslint-disable-next-line no-param-reassign
      config.headers.common.Authorization = `Bearer ${user.access_token}`;
    } else if (guest && guest.language) {
      const beLang = LanguageConstants.feToBe[guest.language];
      // eslint-disable-next-line no-param-reassign
      config.params = {
        ...config.params,
        language: beLang ?? LanguageConstants.beDefault,
      };
    }
    return config;
  },
  (error) => Promise.reject(error),
);

// Response Interceptor
union.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !window.location.pathname.includes('sign-in')) {
      localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_USER_KEY);
      window.location.href = `${process.env.VUE_APP_SUB_PATH}sign-in`;
    }
    return Promise.reject(error);
  },
);

export default union;
