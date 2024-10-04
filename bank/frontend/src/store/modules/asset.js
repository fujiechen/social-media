import UserAccount from '@/services/UserAccount';
import UserTransaction from '@/services/UserTransaction';

const initialState = {
  showSuccessMessage: false,
  successMessage: {
    title: '',
    content: '',
  },
  refreshTrigger: 0,
  accounts: [],
  selectAccountId: -1,
  recentTransactions: [],
  recentTransactionAdditionInfo: {},
};

const getters = {
  selectedAccount: (state) => {
    if (state.selectAccountId <= 0) {
      return {};
    }
    return state.accounts.find(({ id }) => id === state.selectAccountId);
  },
  depositEnabled: (state, currGetters) => {
    if (currGetters.selectedAccount && currGetters.selectedAccount.currency) {
      return currGetters.selectedAccount.currency.deposit_enabled;
    }
    return false;
  },
  exchangeEnabled: (state, currGetters) => {
    if (currGetters.selectedAccount && currGetters.selectedAccount.currency) {
      return currGetters.selectedAccount.currency.exchange_enabled;
    }
    return false;
  },
  purchaseEnabled: (state, currGetters) => {
    if (currGetters.selectedAccount && currGetters.selectedAccount.currency) {
      return currGetters.selectedAccount.currency.purchase_enabled;
    }
    return false;
  },
  transferEnabled: (state, currGetters) => {
    if (currGetters.selectedAccount && currGetters.selectedAccount.currency) {
      return currGetters.selectedAccount.currency.transfer_enabled;
    }
    return false;
  },
  withdrawEnabled: (state, currGetters) => {
    if (currGetters.selectedAccount && currGetters.selectedAccount.currency) {
      return currGetters.selectedAccount.currency.withdraw_enabled;
    }
    return false;
  },
};

const actions = {
  getAccounts({ commit }) {
    return UserAccount
      .list()
      .then(
        (response) => {
          const accounts = response.data;
          if (accounts && accounts.length > 0) {
            commit('loadAccountSuccess', { accounts });
            return Promise.resolve(accounts);
          }
          commit('loadAccountFailed');
          return Promise.reject(Error('Error getting the account for displaying'));
        },
        (error) => {
          commit('loadAccountFailed');
          return Promise.reject(error);
        },
      );
  },
  getAccountTransactions({ commit }, { userAccountId, lastDays }) {
    return UserTransaction
      .list({
        userAccountId,
        lastDays,
      })
      .then(
        (response) => {
          const transactions = response.data;
          const addInfo = response.additional;
          if (transactions && addInfo && transactions.length > 0) {
            commit('loadAccountTransactionSuccess', { transactions, addInfo });
            return Promise.resolve(transactions);
          }
          commit('loadAccountTransactionFail');
          return Promise.reject(Error('Error getting the account transactions for displaying'));
        },
        (error) => {
          commit('loadAccountTransactionFail');
          return Promise.reject(error);
        },
      );
  },
};

const mutations = {
  showSuccessMessage(state, { title, content }) {
    state.showSuccessMessage = true;
    state.successMessage = {
      title,
      content,
    };
  },
  cleanUpSuccessMessage(state) {
    state.showSuccessMessage = false;
    state.successMessage = {
      title: '',
      content: '',
    };
  },
  refreshAfterOrder(state) {
    state.refreshTrigger += 1;
  },
  loadAccountSuccess(state, { accounts }) {
    if (Array.isArray(accounts) && accounts.length > 0) {
      const selectedAccount = accounts.find((account) => account.id === state.selectAccountId);
      if (state.selectAccountId === -1 || selectedAccount === undefined) {
        const defaultAccount = accounts.find((account) => account.currency?.is_default);
        if (defaultAccount === undefined) {
          state.selectAccountId = accounts[0].id;
        } else {
          state.selectAccountId = defaultAccount.id;
        }
      }
    } else {
      state.selectAccountId = -1;
    }
    state.accounts = accounts;
  },
  selectAccount(state, { id }) {
    state.selectAccountId = id;
  },
  loadAccountFailed(state) {
    state.accounts = [];
  },
  loadAccountTransactionSuccess(state, { transactions, addInfo }) {
    state.recentTransactions = transactions;
    state.recentTransactionAdditionInfo = addInfo;
  },
  loadAccountTransactionFail(state) {
    state.recentTransactions = [];
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
