import LanguageConstants from '@/constants/Language';
import UserProfile from '../../services/UserProfile';

const initialState = {
  user: null,
};

const getters = {
  personalInfo: (state) => (state.user ? {
    nickname: state.user.nickname,
    email: state.user.email,
    phone: state.user.phone,
    language: state.user.language,
    wechat: state.user.wechat ?? '',
    alipay: state.user.alipay ?? '',
  } : {}),
  socialNetwork: (state) => (state.user ? {
    whatsapp: state.user.whatsapp ?? '',
    facebook: state.user.facebook ?? '',
    telegram: state.user.telegram ?? '',
  } : {}),
  password: (state) => (state.user ? {
    password: state.user.password,
  } : {}),
};

const actions = {
  get({ commit }) {
    return UserProfile
      .get()
      .then(
        (response) => {
          const user = response.data;
          const convertedUser = {
            ...user,
            language: LanguageConstants.beToFe[user.language],
          };
          commit('getUserProfile', convertedUser);
          return Promise.resolve(convertedUser);
        },
        (error) => {
          commit('getUserProfileFail');
          return Promise.reject(error);
        },
      );
  },
  update({ commit }, userUpdateData) {
    return UserProfile
      .update(userUpdateData)
      .then(
        (response) => {
          const user = response.data;
          const convertedUser = {
            ...user,
            language: LanguageConstants.beToFe[user.language],
          };
          commit('updateUserProfile', convertedUser);
          commit('auth/updateUserProfile', convertedUser, { root: true });
          return Promise.resolve(convertedUser);
        },
        (error) => Promise.reject(error),
      );
  },
  updateAuth({ commit }, userUpdateCredential) {
    return UserProfile
      .updateAuth(userUpdateCredential)
      .then(
        (response) => {
          const user = response.data;
          const convertedUser = {
            ...user,
            language: LanguageConstants.beToFe[user.language],
          };
          commit('updateUserProfile', convertedUser);
          commit('auth/updateUserProfile', convertedUser, { root: true });
          return Promise.resolve(convertedUser);
        },
        (error) => Promise.reject(error),
      );
  },
};

const mutations = {
  getUserProfile(state, user) {
    state.user = user;
  },
  getUserProfileFail(state) {
    state.user = {};
  },
  updateUserProfile(state, user) {
    state.user = {
      ...state.user,
      ...user,
    };
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
