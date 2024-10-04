<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { showToast } from "vant";
import { storeToRefs } from "pinia";
import { fetchMediaById, fetchSimilarListByMediaId, toggleMediaFavorite, toggleMediaLike } from "@/services/media";
import { subscribeUserByUserId } from "@/services/follow";
import {useProductStore} from "@/stores/product.store";
import {useAuthStore} from '@/stores/auth.store';
import VideoItem from "@/components/media/VideoItem";
import AlbumItem from "@/components/media/AlbumItem";
import SeriesItem from "@/components/media/SeriesItem";
import MediaList2 from "@/components/MediaList2";
import ProductPopup from "@/components/ProductPopup";
import MediaComment from "@/components/media/MediaComment";
import MediaList1SeriesItem from "@/components/mediaList1/SeriesItem";
import MediaList1VideoItem from "@/components/mediaList1/VideoItem";
import MediaList1AlbumItem from "@/components/mediaList1/AlbumItem.vue";

const route = useRoute();
const router = useRouter();
const productStore = useProductStore();
const {user} = useAuthStore();
const {showProductPopup, productPopupTopic} = storeToRefs(productStore);

const media = ref({});
const loadingMedia = ref(true);

const medias = ref([]);
const loadingMedias = ref(true);

const parentSeries = computed(() => {
  if (!media.value.parent_series) {
    return null;
  }

  const series = media.value.parent_series;
  return {
    id: series.media_id,
    thumbnail_file: {
      ...series.thumbnail_file,
    },
    meta: {
      count: {
        children_medias: series.total_children_videos,
        likes: series.media_total_like,
      },
    },
    user: {
      nickname: media.value.user.nickname,
    },
    created_at: series.created_at,
  };
});

const nextMedia = computed(() => {
  if (!media.value.next_media) {
    return null;
  }

  const nextMedia = media.value.next_media;
  if (!nextMedia.media_id) {
    return null;
  }

  return {
    id: nextMedia.media_id,
    thumbnail_file: {
      ...nextMedia.thumbnail_file,
    },
    duration_in_seconds: nextMedia.duration_in_seconds,
    meta: {
      count: {
        likes: nextMedia.media_total_like,
      },
    },
    user: {
      nickname: media.value.user.nickname,
    },
    created_at: nextMedia.created_at,
    type: nextMedia.type,
  };
});

const fetchData = async (id) => {
  loadingMedia.value = true;
  loadingMedias.value = true;

  try {
    media.value = await fetchMediaById(id);
  } catch (e) {
    showToast('该媒体暂时无法访问');
    await router.push({
      name: "home",
    });
  }
  loadingMedia.value = false;

  medias.value = await fetchSimilarListByMediaId(id);
  loadingMedias.value = false;
};

onMounted(async () => {
  if (route.params.id) {
    await fetchData(route.params.id);
  }
});

watch(() => route.params.id, async (newId) => {
  if (newId) {
    media.value = {};
    medias.value = [];
    window.scrollTo(0,0);
    await fetchData(newId);
  }
});

const handleLike = async () => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }
  const updatedLike = await toggleMediaLike(route.params.id);
  media.value.meta.user.like = updatedLike;
  media.value.meta.count.likes += updatedLike ? 1 : -1;
  showToast(updatedLike ? '已点赞' : '已取消点赞');
};

const handleFavorite = async () => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }
  const updatedFavorite = await toggleMediaFavorite(route.params.id);
  media.value.meta.user.favorite = updatedFavorite;
  media.value.meta.count.favorites += updatedFavorite ? 1 : -1;
  showToast(updatedFavorite ? '已收藏' : '已取消收藏');
};

const handleFollowAuthorClick = async () => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  const response = await subscribeUserByUserId(media.value.user.id);
  if (response.subscribed) {
    media.value.meta.user.subscribe = true;
    showToast('已关注作者');
  } else {
    await productStore.fetchAll({
      type: 'subscription',
      user_id: media.value.user.id,
      page: 1,
      per_page: 10,
    });
    productPopupTopic.value = media.value?.name || '关注作者';
    showProductPopup.value = true;
  }
};

const handleFollowAuthor = async () => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  const response = await subscribeUserByUserId(media.value.user.id);
  if (response.subscribed) {
    media.value.meta.user.subscribe = true;
    showToast('已关注作者');
    return true;
  }
  return false;
};

const isMediaLocked = computed(() => {
  if (media.value?.meta?.user?.redirects?.is_available) {
    return !media.value.meta.user.redirects.is_available;
  }
  return true;
});

const mediaLockDescription = computed(() => {
  if (media.value?.meta.user.redirects.registration) {
    return '注册专享'
  }

  let description = [];
  if (media.value?.meta.user.redirects.product) {
    description.push('产品专享');
  }

  if (media.value?.meta.user.redirects.membership) {
    description.push('VIP专享');
  }

  if (media.value?.meta.user.redirects.subscription) {
    description.push('订阅专享');
  }

  return description.join(' 或 ');
});

const handlePermission = async () => {
  if (media.value?.meta.user.redirects.registration) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({
      name: "registration",
    });
  }

  if (media.value?.meta.user.redirects.subscription && handleFollowAuthor()) {
    return;
  }

  await productStore.fetchMediaProducts(media.value.id, {
    page: 1,
    per_page: 10,
  });
  productPopupTopic.value = media.value.name;
  showProductPopup.value = true;
};

const comment = ref(null);
const handleOpenComment = () => {
  if (comment.value) {
    comment.value.commentPanelShow = true;
  }
};

const handlePurchaseSuccess = async () => {
  await fetchData(route.params.id);
  showProductPopup.value = false;
  productPopupTopic.value = '购买产品';
};

</script>

<template>
  <van-space direction="vertical" fill>
    <van-skeleton v-if="loadingMedia">
      <template #template>
        <div :style="{ display: 'flex', width: '100%' }">
          <van-skeleton-image />
          <div :style="{ flex: 1, marginLeft: '16px' }">
            <van-skeleton-paragraph row-width="60%" />
            <van-skeleton-paragraph />
            <van-skeleton-paragraph />
            <van-skeleton-paragraph />
          </div>
        </div>
      </template>
    </van-skeleton>
    <VideoItem
      v-if="media.type === 'Video'"
      :data="media"
      :is-media-locked="isMediaLocked"
      :media-lock-description="mediaLockDescription"
      :click-comment="handleOpenComment"
      :click-like="handleLike"
      :click-favorite="handleFavorite"
      :click-follow-author="handleFollowAuthorClick"
      :click-purchase="handlePermission"
    />
    <AlbumItem
      v-else-if="media.type === 'Album'"
      :data="media"
      :is-media-locked="isMediaLocked"
      :media-lock-description="mediaLockDescription"
      :click-comment="handleOpenComment"
      :click-like="handleLike"
      :click-favorite="handleFavorite"
      :click-follow-author="handleFollowAuthorClick"
      :click-purchase="handlePermission"
    />
    <SeriesItem
      v-else-if="media.type === 'Series'"
      :data="media"
      :is-media-locked="isMediaLocked"
      :media-lock-description="mediaLockDescription"
      :click-comment="handleOpenComment"
      :click-like="handleLike"
      :click-favorite="handleFavorite"
      :click-follow-author="handleFollowAuthorClick"
      :click-purchase="handlePermission"
    />
    <MediaComment ref="comment" :mediaId="parseInt(route.params.id)" />
    <template v-if="nextMedia">
      <van-divider class="my-1">观看下一个</van-divider>
      <MediaList1VideoItem v-if="nextMedia.type === 'Video'" :data="nextMedia" />
      <MediaList1AlbumItem v-else-if="nextMedia.type === 'Album'" :data="nextMedia" />
    </template>
    <template v-if="parentSeries">
      <van-divider class="my-1">返回合集</van-divider>
      <MediaList1SeriesItem :data="parentSeries" />
    </template>
    <van-divider class="my-1">推荐</van-divider>
    <MediaList2 :medias="medias" :loading="loadingMedias" />
  </van-space>
  <ProductPopup :purchase-success="handlePurchaseSuccess"/>
</template>
