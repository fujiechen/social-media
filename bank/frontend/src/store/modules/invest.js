import UserProduct from '@/services/UserProduct';

const initialState = {
  showSuccessMessage: false,
  successMessage: {
    title: '',
    content: '',
  },
  userProducts: [],
  userProductsNextPage: -1,
  popupOpenTrigger: 0,
  userProductId: 0,
  userProductInfo: {}, // {title, currencySymbol, currencySymbolColor, numberColorClass}
  userProductReturns: [],
  userProductReturnsNextPage: -1,
};

const getters = {};

const actions = {
  listUserProducts({ commit, state }, { init }) {
    if (init) {
      commit('cleanUserProducts');
    }
    return UserProduct.getActiveUserProducts({
      page: state.userProductsNextPage > 0 ? state.userProductsNextPage : 1,
    })
      .then(
        (response) => {
          const userProducts = response.data;
          const lastPage = response.meta.last_page;
          const currentPage = response.meta.current_page;
          if (userProducts !== undefined) {
            commit('loadUserProductsSuccess', {
              userProducts,
              page: currentPage < lastPage ? currentPage + 1 : -1,
            });
            return Promise.resolve(userProducts);
          }
          commit('loadUserProductsFailed');
          return Promise.reject(Error('Error getting user invested product list'));
        },
        (error) => {
          commit('loadUserProductsFailed');
          return Promise.reject(error);
        },
      );
  },
  listUserProductReturns({ commit, state }, { id, init }) {
    if (init) {
      commit('cleanUserProductReturns');
    }
    return UserProduct.getActiveUserProductReturns({
      id,
      page: state.userProductReturnsNextPage > 0 ? state.userProductReturnsNextPage : 1,
    })
      .then(
        (response) => {
          const userProductReturns = response.data;
          const lastPage = response.meta.last_page;
          const currentPage = response.meta.current_page;
          if (userProductReturns !== undefined) {
            commit('loadUserProductReturnsSuccess', {
              userProductReturns,
              page: currentPage < lastPage ? currentPage + 1 : -1,
            });
            return Promise.resolve(userProductReturns);
          }
          commit('loadUserProductReturnsFailed');
          return Promise.reject(Error('Error getting user invested product return list'));
        },
        (error) => {
          if (init) {
            commit('loadUserProductReturnsFailed');
          }
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
  loadUserProductsSuccess(state, { userProducts, page }) {
    state.userProducts = [...state.userProducts, ...userProducts];
    state.userProductsNextPage = page;
  },
  loadUserProductsFailed(state) {
    state.userProducts = [];
  },
  cleanUserProducts(state) {
    state.userProducts = [];
    state.userProductsNextPage = -1;
  },
  cleanUserProductReturns(state) {
    state.userProductReturns = [];
    state.userProductReturnsNextPage = -1;
  },
  loadUserProductReturnsSuccess(state, { userProductReturns, page }) {
    state.userProductReturns = [...state.userProductReturns, ...userProductReturns];
    state.userProductReturnsNextPage = page;
  },
  loadUserProductReturnsFailed(state) {
    state.userProductReturns = [];
  },
  openPopup(state, { id, productInfo }) {
    state.popupOpenTrigger += 1;
    state.userProductId = id;
    state.userProductInfo = productInfo;
    state.userProductReturnsNextPage = -1;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
