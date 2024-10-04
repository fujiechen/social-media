import UserAccount from '@/services/UserAccount';
import UserOrder from '@/services/UserOrder';
import Settings from '@/services/Settings';

const initialState = {
  popupOpenTrigger: 0,
  accounts: [],
  exchangeInfo: {},
};

const getters = {};

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
          return Promise.reject(Error('Error getting the account for exchanging'));
        },
        (error) => {
          commit('loadAccountFailed');
          return Promise.reject(error);
        },
      );
  },
  getExchangeInfo({ commit }, { amount, fromCurrencyId, toCurrencyId }) {
    return Settings
      .getCurrencyRate({
        params: {
          amount,
          fromCurrencyId,
          toCurrencyId,
        },
      })
      .then(
        (response) => {
          const exchangeInfo = response.data;
          if (exchangeInfo !== undefined) {
            commit('loadExchangeInfoSuccess', { exchangeInfo });
            return Promise.resolve(exchangeInfo);
          }
          commit('loadExchangeInfoFailed');
          return Promise.reject(Error('Error getting the exchange info for exchanging'));
        },
        (error) => {
          commit('loadExchangeInfoFailed');
          return Promise.reject(error);
        },
      );
  },
  exchange(_, {
    amount, fromUserAccountId, toUserAccountId, comment,
  }) {
    return UserOrder.exchange({
      amount,
      fromUserAccountId,
      toUserAccountId,
      comment,
    });
  },
};

const mutations = {
  loadAccountSuccess(state, { accounts }) {
    state.accounts = accounts.filter((account) => account?.currency?.exchange_enabled);
  },
  loadAccountFailed(state) {
    state.accounts = [];
  },
  loadExchangeInfoSuccess(state, { exchangeInfo }) {
    state.exchangeInfo = exchangeInfo;
  },
  loadExchangeInfoFailed(state) {
    state.exchangeInfo = {};
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
