import { createStore, createLogger } from 'vuex';
import auth from './modules/auth';
import address from './modules/address';
import userProfile from './modules/userProfile';
import userWithdrawAccount from './modules/userWithdrawAccount';
import userSupport from './modules/userSupport';
import product from './modules/product';
import userPurchase from './modules/userPurchase';
import userDeposit from './modules/userDeposit';
import userWithdraw from './modules/userWithdraw';
import userExchange from './modules/userExchange';
import userTransfer from './modules/userTransfer';
import userOrderHistory from './modules/userOrderHistory';
import asset from './modules/asset';
import invest from './modules/invest';
import settings from './modules/settings';

const debug = process.env.NODE_ENV !== 'production';

const store = createStore({
  modules: {
    auth,
    address,
    product,
    userProfile,
    userWithdrawAccount,
    userSupport,
    userPurchase,
    userDeposit,
    userWithdraw,
    userExchange,
    userTransfer,
    userOrderHistory,
    asset,
    invest,
    settings,
  },
  strict: debug,
  plugins: debug ? [createLogger()] : [],
});

export default store;
