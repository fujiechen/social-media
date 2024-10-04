<script setup>
import { onMounted, ref } from "vue";
import {useRouter} from "vue-router";
import { fetchUserSubscriptionMedias, fetchUserSubscriptions } from "@/services/userSubscription";
import MediaGrid from "@/components/MediaGrid";
import { fetchMediaUserList } from "@/services/mediaUser";
import UserList from "@/components/UserList.vue";
import { showFailToast } from "vant";

const router = useRouter();

const media = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
  refreshing: false,
});

const subscriptions = ref({
  list: [],
  loading: true,
});

const suggestedSub = ref({
  list: [],
  loading: true,
});

const onLoadMedias = async () => {
  if (media.value.refreshing) {
    media.value.list = [];
    media.value.refreshing = false;
    media.value.currentPage = 1;
  }

  media.value.loading = true;

  try {
    const response = await fetchUserSubscriptionMedias({
      page: media.value.currentPage++,
      per_page: 10,
    });
    media.value.list = [
      ...media.value.list,
      ...response.data,
    ];
    media.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
  } catch (e) {
    showFailToast('无法获取媒体列表，请稍后重试！');
    media.value.finished = true;
  }

  media.value.loading = false
};

const onRefreshMedias = async () => {
  media.value.finished = false;
  await onLoadMedias();
};

onMounted(async () => {
  subscriptions.value.loading = true;
  const response = await fetchUserSubscriptions({page: 1, per_page: 5});
  subscriptions.value.list = response.data;
  subscriptions.value.loading = false;

  if (subscriptions.value.list.length < 1) {
    const response = await fetchMediaUserList({
      page: 1,
      per_page: 20,
    });
    suggestedSub.value.list = [
      ...response.data,
    ];
  }

  suggestedSub.value.loading = false;
});

const handleUserClick = (userId) => {
  router.push({
    name: "mediaUser",
    params: {
      id: userId,
    },
  });
};

const handleUserSubscriptionClick = () => {
  router.push({
    name: "accountSubscription",
    params: {
      type: 'subscriptions',
    },
  });
};
</script>

<template>
  <van-space direction="vertical" fill>
    <van-skeleton :loading="subscriptions.loading">
      <template #template>
        <van-row class="skeleton-avatar-container">
          <van-skeleton-avatar />
          <van-skeleton-avatar />
          <van-skeleton-avatar />
          <van-skeleton-avatar />
        </van-row>
      </template>
    </van-skeleton>
    <div
      class="subscription-page-avatar-container d-flex mx-2 justify-content-start w-100"
      v-if="!subscriptions.loading && subscriptions.list.length > 0"
    >
      <div
        v-for="sub in subscriptions.list"
        :key="sub.id"
        class="d-flex flex-column align-items-center me-3"
      >
        <span
          @click="handleUserClick(sub.id)"
          class="subscription-page-avatar"
        >
          {{ sub.nickname.charAt(0) }}
        </span>
        <span class="text-center nick-name"> {{ sub.nickname }} </span>
      </div>
      <div v-if="subscriptions.list.length > 3">
        <span
          @click="handleUserSubscriptionClick"
          class="subscription-page-avatar avatar-more"
        >
          更多
        </span>
      </div>
      <van-divider class="my-0" />
    </div>
    <div v-if="subscriptions.list.length === 0 && suggestedSub.list.length > 0">
      <div class="d-flex align-items-center justify-content-center mt-2">
        <span class="text-secondary fs-l">推荐关注</span>
      </div>
      <UserList
        :media-users="suggestedSub.list"
        :loading="suggestedSub.loading"
        :has-more-pages="false"
      />
    </div>
    <van-pull-refresh
      v-else
      v-model="media.refreshing"
      @refresh="onRefreshMedias"
    >
      <MediaGrid
        :list="media.list"
        :loading="media.loading"
        :finished="media.finished"
        @load="onLoadMedias"
      />
    </van-pull-refresh>
  </van-space>
</template>
<style>
.skeleton-avatar-container .van-skeleton-avatar {
  width: 4rem;
  height: 4rem;
}

.subscription-page-avatar-container .nick-name {
  max-width: 5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.subscription-page-avatar-container .subscription-page-avatar {
  font-size: 1rem;
  height: 2.5rem;
  line-height: 2.5rem;
  width: 2.5rem;
  transform: translateY(2px);

  font-family: "Source Sans Pro", sans-serif;
  text-transform: uppercase;
  font-weight: 700;

  display: inline-block;
  text-align: center;
  transition: all 120ms ease;

  box-shadow: 0 8px 24px 0 rgba(0, 0, 0, 0.08) !important;
  border-radius: 4rem !important;
  background-color: var(--van-primary-color) !important;
  color: #FFF !important;
  box-sizing: border-box;
}

.subscription-page-avatar-container .avatar-more {
  font-size: .7rem;
  background-color: var(--van-primary-color) !important;
  color: #FFF !important;
}
</style>
