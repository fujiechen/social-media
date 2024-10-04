import UserAccount from '@/services/UserAccount';
import UserOrder from '@/services/UserOrder';
import UserAddressService from '@/services/UserAddress';
import UserWithdrawAccountService from '@/services/UserWithdrawAccount';

const initialState = {
  popupOpenTrigger: 0,
  accounts: [],
  addresses: [],
  banks: [],
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
          return Promise.reject(Error('Error getting the account for withdrawing'));
        },
        (error) => {
          commit('loadAccountFailed');
          return Promise.reject(error);
        },
      );
  },
  getAddressList({ commit }) {
    return UserAddressService
      .list()
      .then(
        (response) => {
          const addresses = response.data;
          if (addresses !== undefined) {
            commit('loadAddressesSuccess', {
              addresses: response.data,
            });
            return Promise.resolve(addresses);
          }
          commit('loadAddressesFailed');
          return Promise.reject(Error('Error getting withdraw address list'));
        },
        (error) => {
          commit('loadAddressesFailed');
          return Promise.reject(error);
        },
      );
  },
  getBanksList({ commit }) {
    return UserWithdrawAccountService
      .list()
      .then(
        (response) => {
          const withdrawAccount = response.data;
          if (withdrawAccount !== undefined) {
            commit('loadBanksSuccess', {
              banks: response.data,
            });
            return Promise.resolve(withdrawAccount);
          }
          commit('loadBanksFailed');
          return Promise.reject(Error('Error getting user withdraw account list'));
        },
        (error) => {
          commit('loadBanksFailed');
          return Promise.reject(error);
        },
      );
  },
  withdraw(_, {
    amount, accountId, addressId, bankId, comment,
  }) {
    return UserOrder.withdraw({
      amount,
      userAccountId: accountId,
      userWithdrawAccountId: bankId,
      userAddressId: addressId,
      comment,
    });
  },
};

const mutations = {
  loadAccountSuccess(state, { accounts }) {
    state.accounts = accounts;
  },
  loadAccountFailed(state) {
    state.accounts = [];
  },
  loadAddressesSuccess(state, { addresses }) {
    state.addresses = addresses;
  },
  loadAddressesFailed(state) {
    state.addresses = [];
  },
  loadBanksSuccess(state, { banks }) {
    state.banks = banks;
  },
  loadBanksFailed(state) {
    state.banks = [];
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
