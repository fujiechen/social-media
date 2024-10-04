import Products from '../../services/Products';

const SUGGEST_PRODUCT_LIMIT = 3;
const SUGGEST_INVESTMENT_LIMIT = 7;

const initialState = {
  suggestProducts: [],
  suggestInvestments: [],
  productListsGroupByCategories: [],
};

const getters = {};

const actions = {
  listSuggestProducts({ commit }) {
    return Products
      .list({
        limit: SUGGEST_PRODUCT_LIMIT,
        isRecommend: true,
        orderBy: 'id',
        sort: 'desc',
      })
      .then(
        (response) => {
          const products = response.data;
          if (products !== undefined) {
            commit('loadSuggestProductsSuccess', {
              products,
            });
            return Promise.resolve(products);
          }
          commit('loadSuggestProductsFail');
          return Promise.reject(Error('Error getting suggest products list'));
        },
        (error) => {
          commit('loadSuggestProductsFail');
          return Promise.reject(error);
        },
      );
  },
  listSuggestInvestments({ commit }) {
    return Products
      .list({
        limit: SUGGEST_INVESTMENT_LIMIT,
        isRecommend: false,
        orderBy: 'id',
        sort: 'desc',
      })
      .then(
        (response) => {
          const products = response.data;
          if (products !== undefined) {
            commit('loadSuggestInvestmentsSuccess', {
              products,
            });
            return Promise.resolve(products);
          }
          commit('loadSuggestInvestmentsFail');
          return Promise.reject(Error('Error getting suggest investments list'));
        },
        (error) => {
          commit('loadSuggestInvestmentsFail');
          return Promise.reject(error);
        },
      );
  },
  listProductsForMarketPage({ commit }) {
    return Products
      .listGroupByCategories()
      .then(
        (response) => {
          const productListsGroupByCategories = response.data;
          if (productListsGroupByCategories !== undefined) {
            commit('loadListProductsSuccess', {
              productListsGroupByCategories,
            });
            return Promise.resolve(productListsGroupByCategories);
          }
          commit('loadListProductsFail');
          return Promise.reject(Error('Error getting suggest investments list'));
        },
        (error) => {
          commit('loadListProductsFail');
          return Promise.reject(error);
        },
      );
  },
};

const mutations = {
  loadSuggestProductsSuccess(state, { products }) {
    state.suggestProducts = products.map((product) => ({
      id: product.id,
      title: product.title,
      name: product.name,
      currency_name: product.currency.name,
      description: product.description,
      start_amount: product.start_amount,
      freeze_days: product.freeze_days,
      return_rate: product.estimate_rate,
      fund_assets: product.fund_assets,
    }));
  },
  loadSuggestProductsFail(state) {
    state.suggestProducts = [];
  },
  loadSuggestInvestmentsSuccess(state, { products }) {
    state.suggestInvestments = products;
  },
  loadSuggestInvestmentsFail(state) {
    state.suggestInvestments = [];
  },
  loadListProductsSuccess(state, { productListsGroupByCategories }) {
    state.productListsGroupByCategories = productListsGroupByCategories;
  },
  loadListProductsFail(state) {
    state.productListsGroupByCategories = [];
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
