<script setup>

import { onMounted, ref } from "vue";
import {useUserStore} from "@/stores/user.store";
import {useAuthStore} from "@/stores/auth.store";
import {storeToRefs} from "pinia";
import GridText from "../../components/GridText";
import {redirectToBank} from "@/helpers/redirect";
import { useUserShareStore } from "@/stores/userShare.store";
import {useGlobalStore} from "@/stores/global.store";
import { showFailToast } from "vant";

const authStore = useAuthStore();
const userStore = useUserStore();
const userShareStore = useUserShareStore();
const globalStore = useGlobalStore();
const {user, userAccount, userStatistics} = storeToRefs(userStore);

const loading = ref({
  user: true,
  userStat: true,
  cnyAccount: true,
});

onMounted(async () => {
  try {
  loading.value = {
    user: true,
    userStat: true,
    cnyAccount: true,
  };
    await userStore.fetchUser();
    loading.value.user = false;
    await userStore.fetchUserStatistics();
    loading.value.userStat = false;
    await userStore.fetchOneUserAccount('CNY');
    loading.value.cnyAccount = false;
  } catch (e) {
    showFailToast('无法获取您的用户信息，请稍后重试！');
  }
});

const redirectToUrl = () => {
  redirectToBank(user.value.access_token, `返回${process.env.VUE_APP_NAME}: 我的钱包`);
};

const createShare = async () => {
  userShareStore.showUserSharePopup = true;
  await userShareStore.createUserShares(
    'user',
    user.value.id,
    `${process.env.VUE_APP_URL}/`
  );
};

</script>

<template>
  <van-space direction="vertical" fill>
    <van-grid :border="false">
      <van-grid-item :text="loading.user ? '...' : user.nickname" :to="{name:'home'}">
        <template #icon>
          <span class="index-page-avatar">
            {{loading.user ? '...' : (user.nickname ? user.nickname.charAt(0) : 'A')}}
          </span>
        </template>
      </van-grid-item>
      <van-grid-item
        v-for="category in userStatistics.categories"
        :key="category.name"
        :text="category.name"
        :to="{ name: 'category', params: { categoryId: category.id } }"
      >
        <template #icon>
          <GridText :text="loading.userStat ? '...' : category.valid_until_at_days"/>
        </template>
      </van-grid-item>
    </van-grid>

    <van-row>
      <div style="margin: 20px">我的钱包</div>
    </van-row>
    <van-grid column-num="4" clickable>
      <van-grid-item text="收益总额" :to="{name:'accountShare',params:{type:'payouts'}}">
        <template #icon>
          <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.total_cash)"/>
        </template>
      </van-grid-item>
      <van-grid-item icon="gold-coin-o" text="收益" :to="{name:'accountShare',params:{type:'payouts'}}"/>
      <van-grid-item icon="gem" text="余额" @click="redirectToUrl">
        <template #icon>
          <GridText :text="loading.cnyAccount ? '...' : String(userAccount.balance)"/>
        </template>
      </van-grid-item>
      <van-grid-item icon="paid" text="钱包" @click="redirectToUrl"/>
    </van-grid>

    <van-row>
      <div style="margin: 20px">我的推荐</div>
    </van-row>
    <van-grid column-num="4" clickable>
      <van-grid-item text="我的分享" :to="{name:'accountShare',params:{type:'shares'}}">
        <template #icon>
          <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.shares_count)"/>
        </template>
      </van-grid-item>
      <van-grid-item text="推荐用户" :to="{name:'accountShare',params:{type:'children'}}">
        <template #icon>
          <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.users_count)"/>
        </template>
      </van-grid-item>
      <van-grid-item text="推荐订单" :to="{name:'accountShare',params:{type:'orders'}}">
        <template #icon>
          <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.orders_count)"/>
        </template>
      </van-grid-item>
      <van-grid-item icon="share-o" text="分享" @click="createShare"/>
    </van-grid>

    <van-row>
      <div style="margin: 20px">我的内容</div>
    </van-row>
    <van-grid column-num="4" clickable>
      <van-grid-item icon="shopping-cart-o" text="全部订单" :to="{name:'order',params:{status:'all'}}"/>
      <van-grid-item icon="cart-o" text="待支付" :to="{name:'order',params:{status:'pending'}}"/>
      <van-grid-item icon="cart-circle-o" text="已完成" :to="{name:'order',params:{status:'completed'}}"/>
      <van-grid-item icon="service-o" text="客服" @click="globalStore.showCustomerServicePopup = true"/>

      <van-grid-item icon="manager-o" text="个人信息" :to="{name:'accountName'}"/>
      <van-grid-item icon="envelop-o" text="邮箱" :to="{name:'accountEmail'}"/>
      <van-grid-item icon="user-o" text="登录名" :to="{name:'accountUsername'}"/>
      <van-grid-item icon="setting-o" text="密码" :to="{name:'accountPassword'}"/>
    </van-grid>

    <van-row>&nbsp;</van-row>

    <van-cell-group inset>
      <van-button block plain round type="warning" :onclick="authStore.logout">
        登出
      </van-button>
    </van-cell-group>
  </van-space>
</template>
<style scoped>
.index-page-avatar {
  font-size: 17px;
  height: 65px;
  line-height: 65px;
  width: 65px;
  transform: translateY(2px);

  font-family: "Source Sans Pro", sans-serif;
  text-transform: uppercase;
  font-weight: 700;

  display: inline-block;
  text-align: center;
  transition: all 120ms ease;

  box-shadow: 0 8px 24px 0 rgba(0, 0, 0, 0.08) !important;
  border-radius: 20px !important;
  background-color: #8CC152 !important;
  color: #FFF !important;
  box-sizing: border-box;
}
</style>
