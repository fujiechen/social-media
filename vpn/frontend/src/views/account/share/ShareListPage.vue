<script setup>
import {onMounted, watch, computed} from 'vue';
import {storeToRefs} from "pinia";
import {useUserShareStore} from "@/stores/userShare.store";
import {useRoute} from "vue-router/dist/vue-router";
import {useUserStore} from "@/stores/user.store";
import {useGlobalStore} from "@/stores/global.store";


const route = useRoute();
const globalStore = useGlobalStore();
const userShareStore = useUserShareStore();
const {otherUserPayouts, userShares, userChildren, userChildrenOrders, userPayouts} = storeToRefs(userShareStore);
const userStore = useUserStore();
const {userStatistics} = storeToRefs(userStore);
const {loading} = storeToRefs(globalStore);

const active = computed({
      get() {
        return route.params.type;
      },
      // eslint-disable-next-line no-unused-vars
      set(value) {
      }
    }
);

const findUserShare = async (userShareId) => {
  loading.value = true;
  userShareStore.showUserSharePopup = true;
  await userShareStore.findUserShare(userShareId);
  loading.value = false;
};

onMounted(async () => {
  loading.value = true;
  await userStore.fetchUserStatistics();
  await updateContent();
  await userShareStore.fetchOtherUserPayouts();
  loading.value = false;
});

watch(active, async (newActive) => {
  if (newActive) {
    loading.value = true;
    await updateContent();
    loading.value = false;
  }
});

const updateContent = async () => {
  if (active.value === 'shares') {
    await userShareStore.fetchUserShares();
  } else if (active.value === 'children') {
    await userShareStore.fetchUserChildren();
  } else if (active.value === 'orders') {
    await userShareStore.fetchUserChildrenOrders();
  } else if (active.value === 'payouts') {
    await userShareStore.fetchUserPayouts();
  }
};
</script>

<template>
  <van-space direction="vertical" fill>
    <van-notice-bar left-icon="volume-o" :scrollable="false">
      <van-swipe
        vertical
        class="notice-swipe"
        :autoplay="3000"
        :touchable="false"
        :show-indicators="false"
      >
        <van-swipe-item v-for="otherUserPayout in otherUserPayouts" :key="otherUserPayout.id">
          恭喜用户 {{otherUserPayout.user_nickname}} , 赚取 {{otherUserPayout.amount_formatted}} 佣金
        </van-swipe-item>
      </van-swipe>
    </van-notice-bar>
    <van-tabs v-model:active="active">
      <van-tab title="我的分享" name="shares" :to="{name:'accountShare',params:{type:'shares'}}">
        <van-notice-bar color="#1989fa" background="#ecf9ff" left-icon="info-o">
          您已经创建了<b>{{ userStatistics.referrals.shares_count }}个</b>分享码
        </van-notice-bar>
        <van-row>&nbsp;</van-row>
        <template v-if="userShares.length > 0">
          <div v-for="userShare in userShares" :key="userShare.id">
            <van-cell-group inset>
              <van-cell :value="userShare.id">
                <template #title>
                  <span class="custom-title">邀请码</span>
                </template>
              </van-cell>
              <van-cell is-link @click="findUserShare(userShare.id)">
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
        <div v-else>
          <van-empty description="您还未有分享"/>
        </div>
      </van-tab>
      <van-tab title="推荐用户" name="children" :to="{name:'accountShare',params:{type:'children'}}">
        <van-notice-bar color="#1989fa" background="#ecf9ff" left-icon="info-o">
          您已经推荐了<b>{{ userStatistics.referrals.users_count }}个</b>用户
        </van-notice-bar>
        <van-row>&nbsp;</van-row>
        <template v-if="userChildren.length > 0">
          <div v-for="child in userChildren" :key="child.id">
            <van-cell-group inset>
              <van-cell :value="child.sub_user_nickname" title="用户" />
              <van-cell :value="child.level" title="层级" />
              <van-cell :value="child.created_at_formatted" title="日期" />
            </van-cell-group>
            <van-row>&nbsp;</van-row>
          </div>
        </template>
        <div v-else>
          <van-empty description="您还未有推荐用户"/>
        </div>
      </van-tab>
      <van-tab title="推荐订单" name="orders" :to="{name:'accountShare',params:{type:'orders'}}">
        <van-notice-bar color="#1989fa" background="#ecf9ff" left-icon="info-o">
          您已经推荐了<b>{{ userStatistics.referrals.orders_count }}个</b>订单
        </van-notice-bar>
        <van-row>&nbsp;</van-row>
        <template v-if="userChildrenOrders.length > 0">
          <div v-for="order in userChildrenOrders" :key="order.id">
            <van-cell-group inset>
              <van-cell :value="order.user_nickname" title="用户"/>
              <van-cell :value="order.amount_formatted" title="金额"/>
              <van-cell :value="order.status_name" title="状态"/>
              <van-cell :value="order.created_at_formatted" title="日期"/>
            </van-cell-group>
            <van-row>&nbsp;</van-row>
          </div>
        </template>
        <div v-else>
          <van-empty description="您还未有推荐订单"/>
        </div>
      </van-tab>

      <van-tab title="我的收益" name="payouts" :to="{name:'accountShare',params:{type:'payouts'}}">
        <van-notice-bar color="#1989fa" background="#ecf9ff" left-icon="info-o">
          您已经获得了<b>{{ userStatistics.referrals.total_cash }}</b>收益
        </van-notice-bar>
        <van-row>&nbsp;</van-row>
        <template v-if="userPayouts.length > 0">
          <div v-for="userPayout in userPayouts" :key="userPayout.id">
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
        <div v-else>
          <van-empty description="您还未有收益"/>
        </div>
      </van-tab>
    </van-tabs>
  </van-space>
</template>

<style>
.notice-swipe {
  height: 40px;
  line-height: 40px;
}
</style>
