import BrowserConstants from '@/constants/Browser';
import { getRedirect, removeRedirect } from '@/helpers/redirect';
import AuthService from '../../services/Auth';

// initial state
const initialState = () => {
  let init = {};
  const userFromLocalStorage = JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_USER_KEY));
  if (userFromLocalStorage) {
    init = { status: { loggedIn: true }, user: userFromLocalStorage };
  } else {
    init = { status: { loggedIn: false }, user: null };
  }

  const redirect = getRedirect();

  return {
    ...init,
    redirect,
  };
};

// getters
const getters = {
  locale: (state) => state.user?.language ?? 'en',
  redirect: (state) => {
    if (state.redirect) {
      return state.redirect;
    }

    const redirect = getRedirect();
    if (redirect) {
      return redirect;
    }

    return null;
  },
};

// actions
const actions = {
  login({ commit }, user) {
    return AuthService.login(user).then(
      (newUser) => {
        commit('loginSuccess', newUser);
        return Promise.resolve(newUser);
      },
      (error) => {
        commit('loginFailure');
        return Promise.reject(error);
      },
    );
  },
  async logout({ commit }) {
    // await AuthService.logout();
    await commit('logout');
  },
  register({ commit }, user) {
    return AuthService.register(user).then(
      (newUser) => {
        commit('registerSuccess', newUser);
        return Promise.resolve(newUser);
      },
      (error) => {
        commit('registerFailure');
        return Promise.reject(error);
      },
    );
  },
  getCaptcha() {
    return AuthService.getCaptcha();
  },
  reset(_, { email }) {
    return AuthService.reset(email);
  },
  resetConfirm({ commit }, { token, password, confirmPassword }) {
    return AuthService.resetConfirm(token, password, confirmPassword)
      .then(
        (newUser) => {
          commit('loginSuccess', newUser);
          return Promise.resolve(newUser);
        },
        (error) => {
          commit('loginFailure');
          return Promise.reject(error);
        },
      );
  },
};

// mutations
const mutations = {
  loginSuccess(state, user) {
    state.status.loggedIn = true;
    state.user = user;
  },
  loginFailure(state) {
    state.status.loggedIn = false;
    state.user = null;
  },
  logout(state) {
    localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_USER_KEY);
    localStorage.removeItem(BrowserConstants.LOCAL_STORAGE_REDIRECT_KEY);
    state.status.loggedIn = false;
    state.user = null;
  },
  registerSuccess(state, user) {
    state.status.loggedIn = true;
    state.user = user;
  },
  registerFailure(state) {
    state.status.loggedIn = false;
  },
  updateUserProfile(state, updatedUserProfile) {
    state.user = {
      ...state.user,
      ...updatedUserProfile,
    };
    localStorage.setItem(BrowserConstants.LOCAL_STORAGE_USER_KEY, JSON.stringify(state.user));
  },
  updateRedirect(state, r) {
    state.redirect = r;
  },
  removeRedirect(state) {
    state.redirect = null;
    removeRedirect();
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
