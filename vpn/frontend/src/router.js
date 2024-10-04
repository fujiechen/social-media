import {createRouter, createWebHistory} from 'vue-router';
import LoginPage from "@/views/auth/LoginPage";
import ConfirmResetPage from "@/views/auth/ConfirmResetPage";
import ResetPage from "@/views/auth/ResetPage";
import RegistrationPage from "@/views/auth/RegistrationPage";

import CategoryListPage from "@/views/category/CategoryListPage";
import TutorialListPage from "@/views/tutorial/TutorialListPage";
import AppListPage from "@/views/app/AppListPage";
import CategoryPage from "@/views/category/CategoryPage";
import OrderListPage from "@/views/order/OrderListPage";
import OrderPage from "@/views/order/OrderPage";
import MinePage from "@/views/account/IndexPage";
import VpnListPage from "@/views/vpn/VpnListPage";
import EmailPage from "@/views/account/profile/EmailPage";
import NamePage from "@/views/account/profile/NamePage";
import UsernamePage from "@/views/account/profile/UsernamePage";
import PasswordPage from "@/views/account/profile/PasswordPage";
import ShareListPage from "@/views/account/share/ShareListPage";
import NotFoundPage from "@/views/NotFoundPage.vue";

const routes = [
    {
      path: '/',
      name: 'home',
      component: CategoryListPage,
      meta: { auth: false,  title: 'VPN', 'showBackBar': false, 'showBottomBar': true}
    },
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
        meta: { auth: false,  title: '登录', 'showBackBar': false, 'showBottomBar': false}
    },
    {
      path: '/reset',
      name: 'reset',
      component: ResetPage,
      meta: { auth: false,  title: '请求重置密码', 'showBackBar': false, 'showBottomBar': true}
    },
    {
      path: '/reset/confirm/:token',
      name: 'resetConfirm',
      component: ConfirmResetPage,
      meta: { auth: false,  title: '重置密码', 'showBackBar': false, 'showBottomBar': false}
    },
    {
        path: '/register',
        name: 'registration',
        component: RegistrationPage,
        meta: { auth: false,  title: 'VPN账户注册', 'showBackBar': false, 'showBottomBar': false}
    },
    {
        path: '/category/:categoryId',
        name: 'category',
        component: CategoryPage,
        meta: { auth: true,  title: '购买', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/tutorial/:os',
        name: 'tutorial',
        component: TutorialListPage,
        meta: { auth: false,  title: '教程', 'showBackBar': false, 'showBottomBar': true}
    },
    {
        path: '/app',
        name: 'app',
        component: AppListPage,
        meta: { auth: false,  title: '导航', 'showBackBar': false, 'showBottomBar': true}
    },
    {
        path: '/order/list/:status',
        name: 'order',
        component: OrderListPage,
        meta: { auth: true,  title: '订单列表', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/order/:orderId',
        name: 'orderDetail',
        component: OrderPage,
        meta: { auth: true,  title: '订单详情', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/vpn',
        name: 'vpn',
        component: VpnListPage,
        meta: { auth: true,  title: '我的VPN', 'showBackBar': false, 'showBottomBar': true}
    },
    {
        path: '/account/index',
        name: 'mine',
        component: MinePage,
        meta: { auth: true,  title: '我的', 'showBackBar': false, 'showBottomBar': true}
    },
    {
        path: '/account/share/:type',
        name: 'accountShare',
        component: ShareListPage,
        meta: { auth: true,  title: '我的推荐', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/account/username',
        name: 'accountUsername',
        component: UsernamePage,
        meta: { auth: true,  title: '我的登录名', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/account/email',
        name: 'accountEmail',
        component: EmailPage,
        meta: { auth: true,  title: '我的邮箱', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/account/name',
        name: 'accountName',
        component: NamePage,
        meta: { auth: true,  title: '我的昵称', 'showBackBar': true, 'showBottomBar': false}
    },
    {
        path: '/account/password',
        name: 'accountPassword',
        component: PasswordPage,
        meta: { auth: true,  title: '我的密码', 'showBackBar': true, 'showBottomBar': false}
    },
    {
      path: '/:pathMatch(.*)',
      component: NotFoundPage,
    },
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
});

router.beforeEach((to, from, next) => {
    const user = JSON.parse(localStorage.getItem('cloud-user'));
    let isAuthenticated = false;
    if (user != null && user.access_token != null) {
        isAuthenticated = true;
    }
    if (to.matched.some(record => record.meta.auth) && !isAuthenticated) {
        localStorage.setItem('cloud-ref', window.location.href);
        next('/login');
    } else {
        next();
    }
});

export default router
