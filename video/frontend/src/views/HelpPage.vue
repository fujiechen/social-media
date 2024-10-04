<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  getOtherUserPayouts,
} from "@/services/user";
import SharePopup from "@/components/SharePopup.vue";
import { useMetaStore } from "@/stores/meta.store";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();
const metaStore = useMetaStore();
const { metas } = storeToRefs(metaStore);

const active = ref('');

const content = ref({
  coop: '',
  earnPoints: '',
});

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

const switchTabs = async () => {
  const query = { ...route.query };
  delete query.tab;
  await router.replace({ query })

  if (active.value === 'coop') {
    content.value.coop = metas.value.find(m => {
      return m.meta_key === 'MEMBERSHIP_REFERRAL_HTML'
    })?.meta_value;
  } else if (active.value === 'earnPoints') {
    content.value.earnPoints = metas.value.find(m => {
      return m.meta_key === 'EARN_POINTS_HTML'
    })?.meta_value;
  }
};

onMounted(async () => {
  await loadOtherUserPayouts();
  active.value = route.query?.tab || 'coop';
  await switchTabs();
});

watch(async () => active.value, async () => {
  await switchTabs();
});
</script>

<template>
  <van-notice-bar
    v-if="!otherUserPayouts.loading"
    left-icon="volume-o"
    :scrollable="false"
  >
    <van-swipe
      vertical
      class="notice-swipe"
      :autoplay="3000"
      :touchable="false"
      :show-indicators="false"
    >
      <van-swipe-item v-for="otherUserPayout in otherUserPayouts.list" :key="otherUserPayout.id">
        恭喜用户 {{ otherUserPayout.user_nickname }} , 赚取 {{ otherUserPayout.amount_formatted }} 佣金
      </van-swipe-item>
    </van-swipe>
  </van-notice-bar>
  <van-tabs
    v-model:active="active"
    class="help-page-tabs"
  >
    <van-tab title="合作赚钱" name="coop">
      <div class="m-4">
        <h2>推广邀请规则介绍</h2>
        <div v-html="content.coop" />
      </div>
    </van-tab>
    <van-tab title="赚取积分" name="earnPoints">
      <div class="m-4">
        <h2>赚取积分介绍</h2>
        <div v-html="content.earnPoints" />
      </div>
    </van-tab>
  </van-tabs>
  <SharePopup />
</template>

<style>
.notice-swipe {
  height: 40px;
  line-height: 40px;
}

.help-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
