import Products from '../../services/Products';
import UserAccount from '../../services/UserAccount';
import UserOrder from '../../services/UserOrder';

const initialState = {
  popupOpenTrigger: 0,
  investProductId: 0,
  productInfo: {},
  accountDetail: {},
};

const getters = {
  productPurchaseEnabled: (state) => {
    if (state.productInfo && state.productInfo.currency) {
      return state.productInfo.currency.purchase_enabled;
    }
    return false;
  },
};

const actions = {
  getProduct({ commit }, { id }) {
    return Products
      .get(id)
      .then(
        (response) => {
          const product = response.data;
          if (product) {
            commit('loadProductSuccess', { product });
            return Promise.resolve(product);
          }
          commit('loadProductFailed');
          return Promise.reject(Error('Error getting the product'));
        },
        (error) => {
          commit('loadProductFailed');
          return Promise.reject(error);
        },
      );
  },
  getAccount({ commit }, { currencyId }) {
    return UserAccount
      .list({ currencyId })
      .then(
        (response) => {
          const accounts = response.data;
          if (accounts && accounts.length > 0 && accounts[0].currency?.id === currencyId) {
            commit('loadAccountSuccess', { account: accounts[0] });
            return Promise.resolve(accounts[0]);
          }
          commit('loadAccountFailed');
          return Promise.reject(Error('Error getting the account for purchasing'));
        },
        (error) => {
          commit('loadAccountFailed');
          return Promise.reject(error);
        },
      );
  },
  purchase(_, { productId, amount }) {
    return UserOrder.purchase({
      productId,
      amount,
    });
  },
};

const mutations = {
  loadProductSuccess(state, { product }) {
    state.productInfo = product;
  },
  loadProductFailed(state) {
    state.productInfo = {};
  },
  loadAccountSuccess(state, { account }) {
    state.accountDetail = account;
  },
  loadAccountFailed(state) {
    state.accountDetail = {};
  },
  openCreatePopup(state, { id }) {
    state.investProductId = id;
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
