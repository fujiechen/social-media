import LanguageConstants from '@/constants/Language';
import { upsertGuestData, getGuestData } from '@/services/guest';

export const updateGuestLocaleSetting = (feLanguageCode) => {
  upsertGuestData({
    language: feLanguageCode,
  });
};

export const loadBrowserLanguageForGuest = () => {
  const guest = getGuestData();
  if (guest && guest.language) {
    return guest.language;
  }

  // initial load, get from browser
  // const browserLanguage = window.navigator.language;
  // const twoDigitCode = browserLanguage.substring(0, 2);
  // const match = LanguageConstants.browserToFe[twoDigitCode];
  // if (match === undefined) {
  updateGuestLocaleSetting(LanguageConstants.feDefault);
  return LanguageConstants.feDefault;
  // }
  // updateGuestLocaleSetting(match);
  // return match;
};
