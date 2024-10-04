import LanguageConstants from '@/constants/Language';
import client from './api';

const BASE_URL = 'settings';

class Settings {
  getCurrencyRate({ params }) {
    return client
      .get(`${BASE_URL}/currency/rate`, {
        params: {
          amount: params.amount,
          from_currency_id: params.fromCurrencyId,
          to_currency_id: params.toCurrencyId,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  getSetting({ name, language }) {
    const convertedLanguage = language ? LanguageConstants.beToFe[language] : undefined;

    return client
      .get(`${BASE_URL}/${name}`, {
        params: {
          language: convertedLanguage,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  getSettings({ names, language }) {
    const convertedLanguage = language ? LanguageConstants.beToFe[language] : undefined;

    return client
      .get(`${BASE_URL}/index`, {
        params: {
          names,
          language: convertedLanguage,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new Settings();
