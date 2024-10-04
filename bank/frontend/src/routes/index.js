import { createRouter, createWebHistory } from 'vue-router';
import { setupToken } from '@/helpers/token';
import { setupRedirect } from '@/helpers/redirect';
import store from '@/store';
import Home from '@/pages/Home.vue';
import TermAndCondition from '@/pages/TermAndCondition.vue';
import NotFound from '@/pages/NotFound.vue';
import SignIn from '@/pages/SignIn.vue';
import SignUp from '@/pages/SignUp.vue';
import Recovery from '@/pages/Recovery.vue';
import Market from '@/pages/Market.vue';
import Invest from '@/pages/Invest.vue';
import Asset from '@/pages/Asset.vue';
import RecentTransactions from '@/pages/AssetManage/RecentTransactions.vue';
import Account from '@/pages/Account.vue';
import AccountAddressManage from '@/pages/AccountManage/AccountAddressManage.vue';
import AccountBankManage from '@/pages/AccountManage/AccountBankManage.vue';
import AccountSecurityManage from '@/pages/AccountManage/AccountSecurityManage.vue';
import AccountPersonalInfoManage from '@/pages/AccountManage/AccountPersonalInfoManage.vue';
import AccountSocialManage from '@/pages/AccountManage/AccountSocialManage.vue';
import Help from '@/pages/Help.vue';
import RecoveryConfirm from '@/pages/RecoveryConfirm.vue';
import PaymentCallback from '@/pages/PaymentCallback.vue';

const routes = [
  {
    path: '/',
    name: 'index',
    component: Home,
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
  },
  {
    path: '/market',
    name: 'market',
    component: Market,
  },
  {
    path: '/invest',
    name: 'invest',
    component: Invest,
  },
  {
    path: '/asset',
    name: 'asset',
    component: Asset,
  },
  {
    path: '/asset-recent-transaction',
    name: 'asset-recent-transaction',
    component: RecentTransactions,
  },
  {
    path: '/account',
    name: 'account',
    component: Account,
  },
  {
    path: '/account-info-edit',
    name: 'account-info-edit',
    component: AccountPersonalInfoManage,
  },
  {
    path: '/account-social-edit',
    name: 'account-social-edit',
    component: AccountSocialManage,
  },
  {
    path: '/account-security-edit',
    name: 'account-security-edit',
    component: AccountSecurityManage,
  },
  {
    path: '/addresses',
    name: 'account-address',
    component: AccountAddressManage,
  },
  {
    path: '/banks',
    name: 'account-bank',
    component: AccountBankManage,
  },
  {
    path: '/help',
    name: 'help',
    component: Help,
  },
  {
    path: '/sign-in',
    name: 'signIn',
    component: SignIn,
  },
  {
    path: '/sign-up',
    name: 'signUp',
    component: SignUp,
  },
  {
    path: '/recovery/confirm/:token',
    name: 'recovery-confirm',
    component: RecoveryConfirm,
  },
  {
    path: '/recovery',
    name: 'recovery',
    component: Recovery,
  },
  {
    path: '/termAndCondition',
    name: 'termAndCondition',
    component: TermAndCondition,
  },
  {
    path: '/paymentCallback',
    component: PaymentCallback,
  },
  {
    path: '/:pathMatch(.*)',
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

router.beforeEach(async (to, from, next) => {
  if (to.query.t) {
    const userData = await setupToken(to.query.t);
    if (userData) {
      store.commit('auth/loginSuccess', userData);
    } else {
      store.commit('auth/loginFailure');
    }
  }

  if (to.query.r) {
    const redirect = setupRedirect(to.query.r);
    store.commit('auth/updateRedirect', redirect);
  }

  next();
});

export default router;
