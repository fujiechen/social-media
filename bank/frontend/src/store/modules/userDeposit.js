import UserAccount from '@/services/UserAccount';
import UserOrder from '@/services/UserOrder';

const initialState = {
  popupOpenTrigger: 0,
  accounts: [],
};

const getters = {};

const actions = {
  getAccounts({ commit }) {
    return UserAccount
      .list()
      .then(
        (response) => {
          const accounts = response.data;
          const depositAccounts = accounts.filter((account) => account?.currency?.deposit_enabled);

          if (depositAccounts && depositAccounts.length > 0) {
            commit('loadAccountsSuccess', { accounts: depositAccounts });
            return Promise.resolve(depositAccounts);
          }
          commit('loadAccountsFailed');
          return Promise.reject(Error('Error getting the accounts for depositing'));
        },
        (error) => {
          commit('loadAccountsFailed');
          return Promise.reject(error);
        },
      );
  },
  deposit(_, {
    amount,
    userAccountId,
    paymentMethod,
    callbackUrl,
  }) {
    return UserOrder.deposit({
      user_account_id: userAccountId,
      amount,
      payment_method: paymentMethod,
      callback_url: callbackUrl,
    });
  },
};

const mutations = {
  loadAccountsSuccess(state, { accounts }) {
    state.accounts = accounts;
  },
  loadAccountsFailed(state) {
    state.accounts = [];
  },
  openPopup(state) {
    state.popupOpenTrigger += 1;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
