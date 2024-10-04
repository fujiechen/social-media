<script setup>

import { computed, onMounted, ref } from "vue";
import {useUserStore} from "@/stores/user.store";
import {useAuthStore} from "@/stores/auth.store";
import {storeToRefs} from "pinia";
import GridText from "@/components/GridText";
import {redirectToBank} from "@/helpers/redirect";
import {useUserShareStore} from "@/stores/userShare.store";
import {useGlobalStore} from "@/stores/global.store";
import { formatNumberToChineseDecimal } from "@/utils";
import SharePopup from "@/components/SharePopup.vue";
import { showFailToast } from "vant";

const authStore = useAuthStore();
const userStore = useUserStore();
const userShareStore = useUserShareStore();
const globalStore = useGlobalStore();
const {user, userAccounts, userStatistics} = storeToRefs(userStore);

const loading = ref({
  user: true,
  userStat: true,
  cnyAccount: true,
  coinAccount: true,
});

onMounted(async () => {
  try {
    loading.value = {
      user: true,
      userStat: true,
      cnyAccount: true,
      coinAccount: true,
    };
    await userStore.fetchUser();
    loading.value.user = false;
    await userStore.fetchUserStatistics();
    loading.value.userStat = false;
    await userStore.fetchOneUserAccount('CNY');
    loading.value.cnyAccount = false;
    await userStore.fetchOneUserAccount('COIN');
    loading.value.coinAccount = false;
  } catch (e) {
    showFailToast('无法获取您的用户信息，请稍后重试！');
  }
});

const redirectToUrl = (currencyName = null) => {
  redirectToBank(user.value.access_token, `返回${process.env.VUE_APP_NAME}: 我的钱包`, currencyName);
};

const createShare = async () => {
  globalStore.loading = true;
  await userShareStore.createUserShare(
    'user',
    user.value.id,
    `${process.env.VUE_APP_URL}/`
  );
  globalStore.loading = false;
  userShareStore.sharePopup.show = true;
};

const cnyAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'CNY');
});

const coinAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'COIN');
});

</script>

<template>
  <van-space direction="vertical" class="index-page-container">
    <div class="d-flex mx-3 mt-3 mb-2 align-items-center">
      <span class="index-page-avatar">
        {{ loading.user ? '...' : (user.nickname ? user.nickname.charAt(0) : 'A') }}
      </span>
      <div class="mx-3">
        <span class="d-block fs-xl text-body fw-bold">{{ loading.user ? '...' : user.nickname }}</span>
        <span class="d-block fs-m text-body">{{ loading.user ? '...' : user.username }}</span>
      </div>
    </div>
    <div class="index-page-card">
      <van-row align="center" class="mx-3">
        <span class="membership-title">
          用户等级
        </span>
        <van-divider vertical :hairline="false" :style="{ borderColor: '#9f1447' }" />
        <span class="flex-grow-1 membership-description">
          {{ loading.user ? '...' : user.top_user_role.role.name }}
          <span v-if="user.top_user_role.role.slug === 'membership'">
            ({{ loading.user ? '...' : `${user.top_user_role.valid_until_at_days}天` }})
          </span>
        </span>
        <van-button
          class="membership-button"
          type="danger"
          size="small"
          round
          plain
          :to="{name:'products', query:{user_type:'self', product_type:'membership'}}"
        >
          {{ loading.user ? '...' : (user.top_user_role.role.slug === 'membership' ? '续费会员' : '升级会员') }}
        </van-button>
      </van-row>
      <van-grid :border="false" :column-num="2">
        <van-grid-item
          text="关注"
          :to="{name:'accountSubscription',query:{type:'subscriptions'}}"
        >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : (String(formatNumberToChineseDecimal(userStatistics.publisher.subscriptions_count)))"/>
          </template>
        </van-grid-item>
        <van-grid-item
          text="粉丝"
          :to="{name:'accountSubscription',query:{type:'subscribers'}}"
        >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : (String(formatNumberToChineseDecimal(userStatistics.publisher.subscribers_count)))"/>
          </template>
        </van-grid-item>
      </van-grid>
    </div>
    <div class="index-page-card">
      <van-grid :border="false" :column-num="4">
        <van-grid-item icon="star-o" text="收藏" :to="{name:'AccountFavorites'}" />
        <van-grid-item icon="good-job-o" text="喜欢" :to="{name:'accountActivity',query:{type:'like'}}"/>
        <van-grid-item icon="underway-o" text="历史" :to="{name:'accountActivity',query:{type:'history'}}"/>
        <van-grid-item icon="comment-o" text="评论" :to="{name:'accountActivity',query:{type:'comment'}}"/>
      </van-grid>
    </div>
    <div class="index-page-card">
      <van-row align="center" class="mx-3">
        <span class="card-title flex-grow-1">
          我的钱包
        </span>
        <span class="card-see-more" @click="redirectToUrl">
          查看详情 >
        </span>
      </van-row>
      <van-grid :column-num="3" clickable :border="false">
        <van-grid-item text="积分" @click="redirectToUrl({ currency: 'COIN'})">
          <template #icon>
            <GridText :text="loading.coinAccount ? '...' : (coinAccount?.balance || '0')"/>
          </template>
        </van-grid-item>
        <van-grid-item text="余额" @click="redirectToUrl({ currency: 'CNY'})">
          <template #icon>
            <GridText :text="loading.cnyAccount ? '...' : (cnyAccount?.balance || '0')"/>
          </template>
        </van-grid-item>
        <van-grid-item icon="balance-o" text="合作赚钱" :to="{name:'help',query:{tab:'coop'}}" />
      </van-grid>
    </div>
    <div class="index-page-card">
      <van-row align="center" class="mx-3">
        <span class="card-title flex-grow-1">
          我的分享
        </span>
        <span class="card-see-more" @click="createShare">
          立即分享 >
        </span>
      </van-row>
      <van-grid :column-num="4" clickable :border="false">
        <van-grid-item text="邀请码" :to="{name:'accountShare',query:{tab:'shares'}}" >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.shares_count)" />
          </template>
        </van-grid-item>
        <van-grid-item text="推荐用户" :to="{name:'accountShare',query:{tab:'children'}}" >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.users_count)" />
          </template>
        </van-grid-item>
        <van-grid-item text="推荐订单" :to="{name:'accountShare',query:{tab:'orders'}}" >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.orders_count)" />
          </template>
        </van-grid-item>
        <van-grid-item text="收益总额" :to="{name:'accountShare',query:{tab:'payouts'}}" >
          <template #icon>
            <GridText :text="loading.userStat ? '...' : String(userStatistics.referrals.total_cash)"/>
          </template>
        </van-grid-item>
      </van-grid>
    </div>
    <div class="index-page-card">
      <van-grid column-num="4" clickable :border="false">
        <van-grid-item icon="shopping-cart-o" text="全部订单" :to="{name:'order',query:{status:'all'}}" />
        <van-grid-item icon="cart-o" text="待支付" :to="{name:'order',query:{status:'pending'}}" />
        <van-grid-item icon="cart-circle-o" text="已完成" :to="{name:'order',query:{status:'completed'}}" />
        <van-grid-item icon="service-o" text="客服" @click="globalStore.showCustomerServicePopup = true"/>

        <van-grid-item icon="manager-o" text="个人信息" :to="{name:'accountName'}" />
        <van-grid-item icon="envelop-o" text="邮箱" :to="{name:'accountEmail'}" />
        <van-grid-item icon="user-o" text="登录名" :to="{name:'accountUsername'}" />
        <van-grid-item icon="setting-o" text="密码" :to="{name:'accountPassword'}" />
      </van-grid>
    </div>
    <div class="mx-3 w-auto pb-5">
      <van-button
        block
        plain
        round
        type="success"
        :onclick="authStore.logout"
      >
        登出
      </van-button>
    </div>
  </van-space>
  <SharePopup />
</template>
<style scoped>
.index-page-avatar {
  font-size: 1.5rem;
  height: 3rem;
  line-height: 3rem;
  width: 3rem;

  font-family: "Source Sans Pro", sans-serif;
  text-transform: uppercase;
  font-weight: 700;

  display: inline-block;
  text-align: center;
  transition: all 120ms ease;

  box-shadow: 0 8px 24px 0 rgba(0, 0, 0, 0.08) !important;
  border-radius: 3rem !important;
  background-color: var(--van-primary-color) !important;
  color: #FFF !important;
  box-sizing: border-box;
}

.index-page-container {
  background-color: var(--van-background);
  width: 100%;
}

.membership-title {
  color: #9f1447;
  font-size: 1rem;
  font-weight: 600;
}

.membership-description {
  color: #9f1447;
  font-size: .9rem;
}

.membership-button {
  border-color: #9f1447 !important;
  color: #9f1447 !important;
}

.index-page-card {
  width: auto;
  margin-left: 1rem;
  margin-right: 1rem;
  margin-bottom: .2rem;
  background-color: var(--van-background);
  border-radius: .5rem;
  padding: .6rem;
  border-color: rgba(252, 116, 136, 0.5);
  border-style: solid;
  border-width: .5px;
}

.card-title {
  color: var(--bs-body-color);
  font-size: 1rem;
  font-weight: 600;
}

.card-see-more {
  color: var(--bs-body-color);
  font-size: .8rem;
}
</style>
