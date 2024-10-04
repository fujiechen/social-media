import UserWithdrawAccountService from '../../services/UserWithdrawAccount';

// initial state
const initialState = {
  withdrawAccounts: [],
  isCreated: true,
  popupOpenTrigger: 0,
  deleteOpenTrigger: 0,
  editId: 0,
  deleteId: 0,
};

// getters
const getters = {
  editWithdrawAccount: (state) => state.withdrawAccounts.find(({ id }) => id === state.editId),
  deleteWithdrawAccount: (state) => state.withdrawAccounts.find(({ id }) => id === state.deleteId),
};

// actions
const actions = {
  list({ commit }) {
    return UserWithdrawAccountService
      .list()
      .then(
        (response) => {
          const withdrawAccount = response.data;
          if (withdrawAccount !== undefined) {
            commit('loadListSuccess', {
              withdrawAccounts: response.data,
            });
            return Promise.resolve(withdrawAccount);
          }
          commit('loadListFailed');
          return Promise.reject(Error('Error getting user withdraw account list'));
        },
        (error) => {
          commit('loadListFailed');
          return Promise.reject(error);
        },
      );
  },
  create({ commit }, withdrawAccount) {
    return UserWithdrawAccountService
      .create(withdrawAccount)
      .then((response) => {
        const newWithdrawAccount = response.data;
        if (newWithdrawAccount) {
          commit('addNewWithdrawAccountToList', newWithdrawAccount);
          return Promise.resolve(newWithdrawAccount);
        }
        return Promise.reject(Error('Error making new withdraw account'));
      });
  },
  edit({ commit, state }, { withdrawAccount }) {
    return UserWithdrawAccountService
      .update(state.editId, withdrawAccount)
      .then((response) => {
        const updatedWithdrawAccount = response.data;
        if (updatedWithdrawAccount) {
          commit('updateWithdrawAccountToList', { id: state.editId, updatedWithdrawAccount });
          return Promise.resolve(updatedWithdrawAccount);
        }
        return Promise.reject(Error('Error updating the withdraw account'));
      });
  },
  delete({ commit, state }) {
    return UserWithdrawAccountService
      .delete(state.deleteId)
      .then(() => {
        commit('deleteWithdrawAccountFromList', state.deleteId);
      });
  },
};

// mutations
const mutations = {
  loadListSuccess(state, { withdrawAccounts }) {
    state.withdrawAccounts = withdrawAccounts;
  },
  loadListFailed(state) {
    state.withdrawAccounts = [];
  },
  addNewWithdrawAccountToList(state, withdrawAccount) {
    state.withdrawAccounts.push(withdrawAccount);
  },
  updateWithdrawAccountToList(state, { id, updatedWithdrawAccount }) {
    const updatedIndex = state.withdrawAccounts.findIndex(((withdrawAccount) => withdrawAccount.id === id));
    state.withdrawAccounts[updatedIndex] = updatedWithdrawAccount;
  },
  deleteWithdrawAccountFromList(state, id) {
    state.withdrawAccounts = state.withdrawAccounts.filter(((withdrawAccount) => withdrawAccount.id !== id));
  },
  openCreatePopup(state) {
    state.isCreated = true;
    state.editId = 0;
    state.popupOpenTrigger += 1;
  },
  openEditPopup(state, { id }) {
    state.isCreated = false;
    state.editId = id;
    state.popupOpenTrigger += 1;
  },
  openDeletePopup(state, { id }) {
    state.deleteId = id;
    state.deleteOpenTrigger += 1;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
