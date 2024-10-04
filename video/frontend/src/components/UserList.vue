<script setup>
import {useRouter} from "vue-router";
import { formatNumberToChineseDecimal } from "@/utils";

const router = useRouter();
defineProps({
  mediaUsers: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
    required: false,
  },
  hasMorePages: {
    type: Boolean,
    default: false,
    required: false,
  },
  loadNextPage: {
    type: Function,
    default: () => {},
    required: false,
  },
  unfollowUser: {
    type: Function,
    default: () => {},
    require: false,
  }
});

const handleUserClick = (userId) => {
  router.push({
    name: "mediaUser",
    params: {
      id: userId,
    },
  });
};

</script>
<template>
  <div class="user-cards-container">
    <div
      v-for="mediaUser in mediaUsers"
      :key="mediaUser.id"
      class="d-flex mx-3 align-items-center py-3"
    >
      <span
        @click="handleUserClick(mediaUser.id)"
        class="user-list-avatar"
      >
        {{mediaUser.nickname.charAt(0)}}
      </span>
      <div @click="handleUserClick(mediaUser.id)">
        <span class="d-block fs-l text-body font-weight-bold">{{ mediaUser.nickname }}</span>
        <span class="d-block fs-m text-secondary">粉丝数: {{ formatNumberToChineseDecimal(mediaUser.publisher.subscribers_count) }}</span>
        <div>
          <van-tag plain type="primary" class="me-1">视频: {{ mediaUser.medias.videos_count }}</van-tag>
          <van-tag plain type="primary" class="me-1">图册: {{ mediaUser.medias.albums_count }}</van-tag>
          <van-tag plain type="primary" class="me-1">合集: {{ mediaUser.medias.series_count }}</van-tag>
        </div>
      </div>
      <van-button
        v-if="mediaUser.publisher.is_followed"
        class="follow-button ms-auto opacity-50"
        round
        size="small"
        type="success"
        @click="unfollowUser(mediaUser.id)"
      >
        已关注
      </van-button>
      <van-button
        v-else
        class="follow-button ms-auto"
        round
        size="small"
        hairline
        plain
        type="success"
        @click="handleUserClick(mediaUser.id)"
      >
        关注
      </van-button>
    </div>
  </div>
  <div v-if="loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="hasMorePages" @click="loadNextPage">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="mediaUsers.length === 0"
    image="search"
    description="没有了"
  />
</template>
<style>
.follow-button {
  min-width: 5rem;
}

.user-list-avatar {
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
  border-radius: 20px !important;
  background-color: var(--van-primary-color) !important;
  color: #FFF !important;
  box-sizing: border-box;

  margin-right: .5rem;
}
</style>
