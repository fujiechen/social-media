import {createRouter, createWebHistory} from 'vue-router';
import LoginPage from "@/views/auth/LoginPage";
import ConfirmResetPage from "@/views/auth/ConfirmResetPage";
import ResetPage from "@/views/auth/ResetPage";
import RegistrationPage from "@/views/auth/RegistrationPage";
import OrderListPage from "@/views/order/OrderListPage";
import OrderPage from "@/views/order/OrderPage";
import MinePage from "@/views/account/IndexPage";
import EmailPage from "@/views/account/profile/EmailPage";
import NamePage from "@/views/account/profile/NamePage";
import UsernamePage from "@/views/account/profile/UsernamePage";
import PasswordPage from "@/views/account/profile/PasswordPage";
import ShareListPage from "@/views/account/ShareListPage";
import AccountActivityListPage from "@/views/account/AccountActivityListPage";
import AccountMediaListPage from "@/views/account/AccountMediaListPage";
import NotFoundPage from "@/views/NotFoundPage.vue";
import HomePage from "@/views/HomePage";
import MediaCategoryPage from "@/views/media/MediaCategoryPage";
import MediaTagPage from "@/views/media/MediaTagPage";
import MediaActorPage from "@/views/media/MediaActorPage";
import MediaUserPage from "@/views/media/MediaUserPage";
import MediaSearchPage from "@/views/media/MediaSearchPage";
import MediaAddPage from "@/views/media/MediaAddPage";
import SubscriptionPage from "@/views/media/SubscriptionPage";
import ProductListPage from "@/views/mall/ProductListPage";
import ProductPage from "@/views/mall/ProductPage";
import UserProductListPage from "@/views/mall/UserProductListPage";
import MediaPage from "@/views/media/MediaPage";
import SubscriptionUserListPage from "@/views/account/SubscriptionUserListPage";
import AccountFavoritePage from "@/views/account/AccountFavoritePage.vue";
import HelpPage from "@/views/HelpPage.vue";

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: {auth: false, title: '登录', 'showBackBar': false, 'showBottomBar': false}
  },
  {
    path: '/reset',
    name: 'reset',
    component: ResetPage,
    meta: {auth: false, title: '请求重置密码', 'showBackBar': false, 'showBottomBar': false}
  },
  {
    path: '/reset/confirm/:token',
    name: 'resetConfirm',
    component: ConfirmResetPage,
    meta: {auth: false, title: '重置密码', 'showBackBar': false, 'showBottomBar': false}
  },
  {
    path: '/register',
    name: 'registration',
    component: RegistrationPage,
    meta: {auth: false, title: 'Video账户注册', 'showBackBar': false, 'showBottomBar': false}
  },
  {
    path: '/order/list',
    name: 'order',
    component: OrderListPage,
    meta: {auth: true, title: '订单列表', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/order/:orderId',
    name: 'orderDetail',
    component: OrderPage,
    meta: {auth: true, title: '订单详情', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/index',
    name: 'mine',
    component: MinePage,
    meta: {auth: true, title: '我的', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/account/share',
    name: 'accountShare',
    component: ShareListPage,
    meta: {auth: true, title: '我的推荐', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/activity/favorites',
    name: 'AccountFavorites',
    component: AccountFavoritePage,
    meta: {auth: true, title: '我的收藏', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/activity',
    name: 'accountActivity',
    component: AccountActivityListPage,
    meta: {auth: true, title: '我的活动', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/subscription',
    name: 'accountSubscription',
    component: SubscriptionUserListPage,
    meta: {auth: true, title: '关注与粉丝', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/username',
    name: 'accountUsername',
    component: UsernamePage,
    meta: {auth: true, title: '我的登录名', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/email',
    name: 'accountEmail',
    component: EmailPage,
    meta: {auth: true, title: '我的邮箱', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/name',
    name: 'accountName',
    component: NamePage,
    meta: {auth: true, title: '我的昵称', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/account/password',
    name: 'accountPassword',
    component: PasswordPage,
    meta: {auth: true, title: '我的密码', 'showBackBar': { home: {name: 'mine'} }, 'showBottomBar': false}
  },
  {
    path: '/',
    name: 'home',
    component: HomePage,
    meta: {auth: false, title: 'Video', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/media/add',
    name: 'mediaAdd',
    component: MediaAddPage,
    meta: {auth: true, title: '添加媒体', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/media/:id',
    name: 'media',
    component: MediaPage,
    meta: {auth: false, title: '媒体', 'showBackBar': true, 'showBottomBar': false}
  },
  {
    path: '/media/search',
    name: 'mediaSearch',
    component: MediaSearchPage,
    meta: {auth: false, title: '搜索', 'showBackBar': true, 'showBottomBar': false}
  },
  {
    path: '/media/category/:id',
    name: 'mediaCategory',
    component: MediaCategoryPage,
    meta: {auth: false, title: '分类', 'showBackBar': { home: {name: 'home', query: {tab: 'category'}} }, 'showBottomBar': false}
  },
  {
    path: '/media/actor/:id',
    name: 'mediaActor',
    component: MediaActorPage,
    meta: {auth: false, title: '演员', 'showBackBar': { home: {name: 'home', query: {tab: 'actor'}} }, 'showBottomBar': false}
  },
  {
    path: '/media/tag/:id',
    name: 'mediaTag',
    component: MediaTagPage,
    meta: {auth: false, title: '标签', 'showBackBar': { home: {name: 'home', query: {tab: 'tag'}} }, 'showBottomBar': false}
  },
  {
    path: '/account/medias',
    name: 'accountMedias',
    component: AccountMediaListPage,
    meta: {auth: true, title: '我的主页', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/media/user/:id',
    name: 'mediaUser',
    component: MediaUserPage,
    meta: {auth: false, title: '用户', 'showBackBar': true, 'showBottomBar': false}
  },
  {
    path: '/products',
    name: 'products',
    component: ProductListPage,
    meta: {auth: false, title: '商城', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/user/products/:id',
    name: 'userProducts',
    component: UserProductListPage,
    meta: {auth: false, title: '商铺', 'showBackBar': { home: {name: 'products'} }, 'showBottomBar': false}
  },
  {
    path: '/products/:id',
    name: 'product',
    component: ProductPage,
    meta: {auth: true, title: '商品', 'showBackBar': { home: {name: 'products'} }, 'showBottomBar': false}
  },
  {
    path: '/subscription',
    name: 'subscription',
    component: SubscriptionPage,
    meta: {auth: true, title: '关注', 'showBackBar': false, 'showBottomBar': true}
  },
  {
    path: '/help',
    name: 'help',
    component: HelpPage,
    meta: {auth: false, title: '帮助', 'showBackBar': true, 'showBottomBar': false}
  },
  {
    path: '/:pathMatch(.*)',
    component: NotFoundPage,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
});

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem('video-user'));

  let isAuthenticated = false;
  if (user != null && user.access_token != null) {
    isAuthenticated = true;
  }

  if (to.matched.some(record => record.meta.auth) && !isAuthenticated) {
    localStorage.setItem('video-ref', window.location.href);
    next('/login');
  } else {
    next();
  }
});

export default router
