import UserSupport from '../../services/UserSupport';

const initialState = {
  showSuccessMessage: false,
  popupOpenTrigger: 0,
};

const getters = {};

const actions = {
  create({ commit }, support) {
    return UserSupport
      .create(support)
      .then(
        () => {
          commit('createSupportRequest');
          return Promise.resolve();
        },
        (error) => Promise.reject(error),
      );
  },
};

const mutations = {
  createSupportRequest(state) {
    state.showSuccessMessage = true;
  },
  closeSuccessMessage(state) {
    state.showSuccessMessage = false;
  },
  openPopup(state) {
    state.popupOpenTrigger += 1;
    state.showSuccessMessage = false;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
