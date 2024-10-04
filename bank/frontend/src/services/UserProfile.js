import LanguageConstants from '@/constants/Language';
import unionClient from './union';

const BASE_URL = 'user';

class UserProfile {
  get() {
    return unionClient
      .get(BASE_URL)
      .then((axiosResponse) => axiosResponse.data);
  }

  update(profile) {
    let { language } = profile;
    if (language) {
      language = LanguageConstants.feToBe[language];
    }

    return unionClient
      .put(BASE_URL, {
        nickname: profile.nickname,
        email: profile.email,
        phone: profile.phone,
        language,
        whatsapp: profile.whatsapp,
        facebook: profile.facebook,
        telegram: profile.telegram,
        alipay: profile.alipay,
        wechat: profile.wechat,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  updateAuth(profile) {
    return unionClient
      .put(`${BASE_URL}/auth`, {
        username: profile.username,
        password: profile.password,
        confirm_password: profile.confirmPassword,
        old_password: profile.oldPassword,
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserProfile();
