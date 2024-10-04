<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter} from "vue-router";
import {
  getOtherUserPayouts,
  getUserChildren,
  getUserChildrenOrders,
  getUserPayouts,
  getUserShares, getUserStat,
} from "@/services/user";
import { showFailToast } from "vant";
import SharePopup from "@/components/SharePopup.vue";
import { useUserShareStore } from "@/stores/userShare.store";
import { useGlobalStore } from "@/stores/global.store";

const route = useRoute();
const router = useRouter();
const userShareStore = useUserShareStore();
const globalStore = useGlobalStore();

const active = ref('');

const userShares = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});
const userChildren = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});
const userChildrenOrders = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});
const userPayouts = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});

const loadUserShares = async () => {
  userShares.value.loading = true;
  try {
    const response = await getUserShares({
      page: userShares.value.currentPage++,
      per_page: 10,
    });
    userShares.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    userShares.value.list = [
      ...userShares.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取邀请码列表，请稍后重试！');
    userShares.value.hasMorePages = false;
  }
  userShares.value.loading = false;
};

const loadUserChildrenOrders = async () => {
  userChildrenOrders.value.loading = true;
  try {
    const response = await getUserChildrenOrders({
      page: userChildrenOrders.value.currentPage++,
      per_page: 10,
    });
    userChildrenOrders.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    userChildrenOrders.value.list = [
      ...userChildrenOrders.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取推荐订单列表，请稍后重试！');
    userChildrenOrders.value.hasMorePages = false;
  }
  userChildrenOrders.value.loading = false;
};

const loadUserChildren = async () => {
  userChildren.value.loading = true;
  try {
    const response = await getUserChildren({
      page: userChildren.value.currentPage++,
      per_page: 10,
    });
    userChildren.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    userChildren.value.list = [
      ...userChildren.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取推荐用户列表，请稍后重试！');
    userChildren.value.hasMorePages = false;
  }
  userChildren.value.loading = false;
};

const loadUserPayouts = async () => {
  userPayouts.value.loading = true;
  try {
    const response = await getUserPayouts({
      page: userPayouts.value.currentPage++,
      per_page: 10,
    });
    userPayouts.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    userPayouts.value.list = [
      ...userPayouts.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取收益列表，请稍后重试！');
    userPayouts.value.hasMorePages = false;
  }
  userPayouts.value.loading = false;
};

const getUserShare = async (userShareId) => {
  globalStore.loading = true;
  await userShareStore.getUserShare(userShareId);
  globalStore.loading = false;
  userShareStore.sharePopup.show = true;
};

const otherUserPayouts = ref({
  list: [],
  loading: false,
});

const loadOtherUserPayouts = async () => {
  otherUserPayouts.value.loading = true;
  try {
    const response = await getOtherUserPayouts({
      page: 1,
      per_page: 10,
    });
    otherUserPayouts.value.list = response.data;
  } catch (e) {
    // do nothing
  }
  otherUserPayouts.value.loading = false;
};

const userStats = ref({
  loading: false,
  data: {},
})
const loadUserStats = async () => {
  userStats.value.loading = true;
  try {
    userStats.value.data = await getUserStat()
  } catch (e) {
    showFailToast('无法获取用户信息，请稍后重试！');
  }
  userStats.value.loading = false;
};

const switchTabs = async () => {
  const query = { ...route.query };
  delete query.tab;
  await router.replace({ query })

  if (active.value === 'children') {
    if (userChildren.value.list.length === 0) {
      await loadUserChildren();
    }
  } else if (active.value === 'orders') {
    if (userChildrenOrders.value.list.length === 0) {
      await loadUserChildrenOrders();
    }
  } else if (active.value === 'payouts') {
    if (userPayouts.value.list.length === 0) {
      await loadUserPayouts();
    }
  } else if (active.value === 'shares') {
    if (userShares.value.list.length === 0) {
      await loadUserShares();
    }
  }
};

onMounted(async () => {
  await loadOtherUserPayouts();
  await loadUserStats();
  active.value = route.query?.tab || 'shares';
  await switchTabs();
});

watch(async () => active.value, async () => {
  await switchTabs();
});

const onClickOtherUserPayoutBar = () => {
  router.push({
    name: 'help',
  });
};
</script>

<template>
  <van-notice-bar
    v-if="!otherUserPayouts.loading"
    mode="link"
    left-icon="volume-o"
    :scrollable="false"
    @click="onClickOtherUserPayoutBar"
  >
    <van-swipe
      vertical
      class="notice-swipe"
      :autoplay="3000"
      :touchable="false"
      :show-indicators="false"
    >
      <van-swipe-item
        v-for="otherUserPayout in otherUserPayouts.list"
        :key="otherUserPayout.id"
        class="w-100"
      >
        <div class="w-100 d-flex justify-content-between">
          <span>恭喜用户 {{otherUserPayout.user_nickname}} , 赚取 {{otherUserPayout.amount_formatted}} 佣金</span>
          <span>查看详情</span>
        </div>
      </van-swipe-item>
    </van-swipe>
  </van-notice-bar>
  <van-tabs
    v-model:active="active"
    class="share-list-page-tabs"
  >
    <van-tab title="邀请码" name="shares">
      <van-notice-bar v-if="!userStats.loading && userStats.data.referrals" color="#1989fa" background="#ecf9ff" left-icon="info-o">
        您已经创建了<b>{{ userStats.data.referrals.shares_count }}个</b>邀请码
      </van-notice-bar>
      <van-row>&nbsp;</van-row>
      <template v-if="userShares.list.length > 0">
        <div v-for="userShare in userShares.list" :key="userShare.id">
          <van-cell-group inset>
            <van-cell :value="userShare.id">
              <template #title>
                <span class="custom-title">邀请码</span>
              </template>
            </van-cell>
            <van-cell is-link @click="getUserShare(userShare.id)">
              <template #title>
                <span class="custom-title">立即分享</span>
              </template>
            </van-cell>
            <van-cell :value="userShare.created_at_formatted">
              <template #title>
                <span class="custom-title">创建时间</span>
              </template>
            </van-cell>
          </van-cell-group>
          <van-row>&nbsp;</van-row>
        </div>
      </template>
      <div v-if="userShares.loading" class="mt-3 text-center">
        <van-loading class="mb-5" vertical>加载中...</van-loading>
      </div>
      <div v-else-if="userShares.hasMorePages" @click="loadUserShares">
        <van-divider class="mb-5" dashed>加载更多</van-divider>
      </div>
      <van-empty
        v-else-if="userShares.list.length === 0"
        description="您还未有分享"
      />
    </van-tab>
    <van-tab title="推荐用户" name="children">
      <van-notice-bar v-if="!userStats.loading && userStats.data.referrals" color="#1989fa" background="#ecf9ff" left-icon="info-o">
        您已经推荐了<b>{{ userStats.data.referrals.users_count }}个</b>用户
      </van-notice-bar>
      <van-row>&nbsp;</van-row>
      <template v-if="userChildren.list.length > 0">
        <div v-for="child in userChildren.list" :key="child.id">
          <van-cell-group inset>
            <van-cell :value="child.sub_user_nickname" title="用户" />
            <van-cell :value="child?.top_user_role?.role?.name" title="等级" />
            <van-cell :value="child.level" title="层级" />
            <van-cell :value="child.created_at_formatted" title="日期" />
          </van-cell-group>
          <van-row>&nbsp;</van-row>
        </div>
      </template>
      <div v-if="userChildren.loading" class="mt-3 text-center">
        <van-loading class="mb-5" vertical>加载中...</van-loading>
      </div>
      <div v-else-if="userChildren.hasMorePages" @click="loadUserChildren">
        <van-divider class="mb-5" dashed>加载更多</van-divider>
      </div>
      <van-empty
        v-else-if="userChildren.list.length === 0"
        description="您还未有推荐用户"
      />
    </van-tab>
    <van-tab title="推荐订单" name="orders">
      <van-notice-bar v-if="!userStats.loading && userStats.data.referrals" color="#1989fa" background="#ecf9ff" left-icon="info-o">
        您已经推荐了<b>{{ userStats.data.referrals.orders_count }}个</b>订单
      </van-notice-bar>
      <van-row>&nbsp;</van-row>
      <template v-if="userChildrenOrders.list.length > 0">
        <div v-for="order in userChildrenOrders.list" :key="order.id">
          <van-cell-group inset>
            <van-cell :value="order.user_nickname" title="用户"/>
            <van-cell :value="order.amount_formatted" title="金额"/>
            <van-cell :value="order.status_name" title="状态"/>
            <van-cell :value="order.created_at_formatted" title="日期"/>
          </van-cell-group>
          <van-row>&nbsp;</van-row>
        </div>
      </template>
      <div v-if="userChildrenOrders.loading" class="mt-3 text-center">
        <van-loading class="mb-5" vertical>加载中...</van-loading>
      </div>
      <div v-else-if="userChildrenOrders.hasMorePages" @click="loadUserChildrenOrders">
        <van-divider class="mb-5" dashed>加载更多</van-divider>
      </div>
      <van-empty
        v-else-if="userChildrenOrders.list.length === 0"
        description="您还未有推荐订单"
      />
    </van-tab>
    <van-tab title="我的收益" name="payouts">
      <van-notice-bar v-if="!userStats.loading && userStats.data.referrals" color="#1989fa" background="#ecf9ff" left-icon="info-o">
        您已经获得收益: <b>{{ userStats.data.referrals.total_cash }}</b>
      </van-notice-bar>
      <van-row>&nbsp;</van-row>
      <template v-if="userPayouts.list.length > 0">
        <div v-for="userPayout in userPayouts.list" :key="userPayout.id">
          <van-cell-group inset>
            <van-cell :value="userPayout.order_user_nickname" title="用户" />
            <van-cell :value="userPayout.amount_formatted" title="金额" />
            <van-cell :value="userPayout.status_name" title="状态"/>
            <van-cell :value="userPayout.comment" title="备注"/>
            <van-cell :value="userPayout.created_at_formatted" title="日期"/>
          </van-cell-group>
          <van-row>&nbsp;</van-row>
        </div>
      </template>
      <div v-if="userPayouts.loading" class="mt-3 text-center">
        <van-loading class="mb-5" vertical>加载中...</van-loading>
      </div>
      <div v-else-if="userPayouts.hasMorePages" @click="loadUserPayouts">
        <van-divider class="mb-5" dashed>加载更多</van-divider>
      </div>
      <van-empty
        v-else-if="userPayouts.list.length === 0"
        description="您还未有收益"
      />
    </van-tab>
  </van-tabs>
  <SharePopup />
</template>

<style>
.notice-swipe {
  height: 40px;
  line-height: 40px;
}

.van-notice-bar__content {
  width: 100%;
}

.share-list-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
