import BrowserConstants from '@/constants/Browser';
import { decodeBase64UriSafeToJson } from '@/helpers/base64uri';

export const setupRedirect = (r) => {
  try {
    const rObj = decodeBase64UriSafeToJson(r);
    localStorage.setItem(BrowserConstants.LOCAL_STORAGE_REDIRECT_KEY, JSON.stringify(rObj));

    return rObj;
  } catch (e) {
    return null;
  }
};

export const getRedirect = () => JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_REDIRECT_KEY));

export const removeRedirect = () => localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_REDIRECT_KEY);
