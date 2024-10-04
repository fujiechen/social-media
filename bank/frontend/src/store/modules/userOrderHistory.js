import UserOrder from '@/services/UserOrder';
import UserOrderConstant from '@/constants/UserOrder';

const initialState = {
  userOrders: [],
  userOrdersNextPage: -1,
  depositUserOrder: {},
  withdrawUserOrder: {},
  exchangeUserOrder: {},
  transferUserOrder: {},
  purchaseUserOrder: {},
};

const getters = {};

const actions = {
  listUserOrders({ commit, state }, { init }) {
    if (init) {
      commit('cleanUserOrderList');
    }
    return UserOrder.list({
      limit: UserOrderConstant.USER_ORDER_LIST_LIMIT,
      page: state.userOrdersNextPage > 0 ? state.userOrdersNextPage : 1,
    })
      .then(
        (response) => {
          const userOrders = response.data;
          const lastPage = response.meta.last_page;
          const currentPage = response.meta.current_page;
          if (userOrders !== undefined) {
            commit('loadUserOrdersSuccess', {
              userOrders,
              page: currentPage < lastPage ? currentPage + 1 : -1,
            });
            return Promise.resolve(userOrders);
          }
          commit('loadUserOrdersFailed');
          return Promise.reject(Error('Error getting user order history list'));
        },
        (error) => {
          if (init) {
            commit('loadUserOrdersFailed');
          }
          return Promise.reject(error);
        },
      );
  },
};

const mutations = {
  cleanUserOrderList(state) {
    state.userOrders = [];
    state.userOrdersNextPage = -1;
  },
  loadUserOrdersSuccess(state, { userOrders, page }) {
    state.userOrders = [...state.userOrders, ...userOrders];
    state.userOrdersNextPage = page;
  },
  loadUserOrdersFailed(state) {
    state.userProductReturns = [];
  },
  openPurchasePopup(state, { userOrder }) {
    state.purchaseUserOrder = userOrder;
  },
  openDepositPopup(state, { userOrder }) {
    state.depositUserOrder = userOrder;
  },
  openWithdrawPopup(state, { userOrder }) {
    state.withdrawUserOrder = userOrder;
  },
  openExchangePopup(state, { userOrder }) {
    state.exchangeUserOrder = userOrder;
  },
  openTransferPopup(state, { userOrder }) {
    state.transferUserOrder = userOrder;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
