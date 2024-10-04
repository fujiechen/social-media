<script setup>

import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { showConfirmDialog, showToast } from "vant";
import {storeToRefs} from "pinia";
import MediaList2 from "@/components/MediaList2";
import ProductPopup from "@/components/ProductPopup";
import GridText from "@/components/GridText";
import {useProductStore} from "@/stores/product.store";
import { useAuthStore } from "@/stores/auth.store";
import { fetchMediaList } from "@/services/media";
import { fetchMediaUserByUserId } from "@/services/mediaUser";
import { subscribeUserByUserId, unsubscribeUserByUserId } from "@/services/follow";
import { formatNumberToChineseDecimal } from "@/utils";

const route = useRoute();
const router = useRouter();

const {user} = useAuthStore();
const productStore = useProductStore();
const {showProductPopup, productPopupTopic} = storeToRefs(productStore);

const active = ref('all');
const mediaUserStat = ref({});
const mediaUserStatLoading = ref(true);

const allMedia = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: true,
});

const videoMedia = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: true,
});

const albumMedia = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: true,
});

const seriesMedia = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: true,
});

const loadAllMedaList = async () => {
  allMedia.value.loading = true;
  const response = await fetchMediaList({
    media_user_id: route.params.id,
    page: allMedia.value.currentPage++,
    per_page: 10,
  });
  allMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
  allMedia.value.list = [
    ...allMedia.value.list,
    ...response.data,
  ];
  allMedia.value.loading = false;
};

const loadVideoMedaList = async () => {
  videoMedia.value.loading = true;
  const response = await fetchMediaList({
    media_user_id: route.params.id,
    page: videoMedia.value.currentPage++,
    per_page: 10,
    'types[]': 'Video',
  });
  videoMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
  videoMedia.value.list = [
    ...videoMedia.value.list,
    ...response.data,
  ];
  videoMedia.value.loading = false;
};

const loadAlbumMedaList = async () => {
  albumMedia.value.loading = true;
  const response = await fetchMediaList({
    media_user_id: route.params.id,
    page: albumMedia.value.currentPage++,
    per_page: 10,
    'types[]': 'Album',
  });
  albumMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
  albumMedia.value.list = [
    ...albumMedia.value.list,
    ...response.data,
  ];
  albumMedia.value.loading = false;
};

const loadSeriesMedaList = async () => {
  seriesMedia.value.loading = true;
  const response = await fetchMediaList({
    media_user_id: route.params.id,
    page: seriesMedia.value.currentPage++,
    per_page: 10,
    'types[]': 'Series',
  });
  seriesMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
  seriesMedia.value.list = [
    ...seriesMedia.value.list,
    ...response.data,
  ];
  seriesMedia.value.loading = false;
};

onMounted(async () => {
  try {
    mediaUserStat.value = await fetchMediaUserByUserId(route.params.id);
    mediaUserStatLoading.value = false;
    await loadAllMedaList();
  } catch {
    showToast('该用户暂时不可访问');
    await router.push({
      name: "home",
    });
  }
});

const handleFollow = async () => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  if (mediaUserStat.value.publisher.follows.product_redirect) {
    await productStore.fetchAll({
      type: 'subscription',
      user_id: route.params.id,
      page: 1,
      per_page: 10,
    });
    productPopupTopic.value = '关注作者';
    showProductPopup.value = true;
  } else {
    await subscribeUserByUserId(route.params.id);

    mediaUserStatLoading.value = true;
    mediaUserStat.value = await fetchMediaUserByUserId(route.params.id);
    mediaUserStatLoading.value = false;

    showToast('已关注');
  }
};
const handleUnfollow = async() => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  showConfirmDialog({
    title: '请确认取消关注',
  })
    .then(async () => {
      await unsubscribeUserByUserId(route.params.id);

      mediaUserStatLoading.value = true;
      mediaUserStat.value = await fetchMediaUserByUserId(route.params.id);
      mediaUserStatLoading.value = false;

      showToast('已取消关注');
    })
    .catch(() => {});
};

const onClickTab = async ({ name }) => {
  if (name === 'all' && allMedia.value.list.length === 0 && allMedia.value.hasMorePages) {
    await loadAllMedaList();
  } else if (name === 'video' && videoMedia.value.list.length === 0 && videoMedia.value.hasMorePages) {
    await loadVideoMedaList();
  } else if (name === 'series' && seriesMedia.value.list.length === 0 && seriesMedia.value.hasMorePages) {
    await loadSeriesMedaList();
  } else if (name === 'album' && albumMedia.value.list.length === 0 && albumMedia.value.hasMorePages) {
    await loadAlbumMedaList();
  }
};

const handleRenewClick = async () => {
  await productStore.fetchAll({
    type: 'subscription',
    user_id: route.params.id,
    page: 1,
    per_page: 10,
  });
  showProductPopup.value = true;
};

const shoppingCartImagePath = computed(() => {
  return `${process.env.VUE_APP_SUB_PATH}img/shopping-cart.png`;
});
</script>

<template>
  <van-space direction="vertical" fill>
    <van-skeleton
      :loading="mediaUserStatLoading"
      title
      :row="5"
    />
    <template v-if="!mediaUserStatLoading">
      <van-card
        class="media-user-name-container"
        :title="mediaUserStat?.nickname"
        desc="用户"
      />
      <van-grid :border="false" column-num="4">
        <van-grid-item
          :to= "{ name: 'userProducts', params: { id: route.params.id } }"
        >
          <van-image width="2rem" :src="shoppingCartImagePath" />
        </van-grid-item>
        <van-grid-item text="发布">
          <template #icon>
            <GridText :text="String(formatNumberToChineseDecimal(mediaUserStat?.medias?.medias_count))"/>
          </template>
        </van-grid-item>
        <van-grid-item text="关注">
          <template #icon>
            <GridText :text="String(formatNumberToChineseDecimal(mediaUserStat?.publisher?.subscriptions_count))"/>
          </template>
        </van-grid-item>
        <van-grid-item text="粉丝">
          <template #icon>
            <GridText :text="String(formatNumberToChineseDecimal(mediaUserStat?.publisher?.subscribers_count))"/>
          </template>
        </van-grid-item>
      </van-grid>
      <div v-if="mediaUserStat.publisher.valid_until_at_formatted" class="d-flex align-items-center justify-content-center">
        <span class="fs-m">{{`${mediaUserStat.publisher.valid_until_at_days}天关注到期`}}</span>
        <van-button
          class="ms-2"
          hairline
          plain
          type="success"
          size="small"
          @click="handleRenewClick"
        >
          点击续费
        </van-button>
      </div>
      <van-cell-group inset>
      <span v-if="mediaUserStat?.publisher?.is_followed">
        <van-button block round type="success" @click="handleUnfollow">
          已关注
        </van-button>
      </span>
      <span v-else>
        <van-button hairline plain block round type="success" @click="handleFollow">
          关注
        </van-button>
      </span>
      </van-cell-group>
    </template>
    <van-tabs v-model:active="active" @click-tab="onClickTab">
      <van-tab title="全部" name="all">
        <MediaList2
          :medias="allMedia.list"
          :loading="allMedia.loading"
          :has-more-pages="allMedia.hasMorePages"
          :load-next-page="loadAllMedaList"
        />
      </van-tab>
      <van-tab title="视频" name="video">
        <MediaList2
          :medias="videoMedia.list"
          :loading="videoMedia.loading"
          :has-more-pages="videoMedia.hasMorePages"
          :load-next-page="loadVideoMedaList"
        />
      </van-tab>
      <van-tab title="合集" name="series">
        <MediaList2
          :medias="seriesMedia.list"
          :loading="seriesMedia.loading"
          :has-more-pages="seriesMedia.hasMorePages"
          :load-next-page="loadSeriesMedaList"
        />
      </van-tab>
      <van-tab title="图册" name="album">
        <MediaList2
          :medias="albumMedia.list"
          :loading="albumMedia.loading"
          :has-more-pages="albumMedia.hasMorePages"
          :load-next-page="loadAlbumMedaList"
        />
      </van-tab>
    </van-tabs>
  </van-space>
  <ProductPopup />
</template>
<style>
.media-user-name-container .van-card__content {
  justify-content: center;
}

.media-user-name-container .van-card__desc {
  font-size: 1rem;
  line-height: 1rem;
  text-align: center;
  margin-top: .5rem;
}

.media-user-name-container .van-card__title {
  font-size: 1.5rem;
  line-height: 2rem;
  text-align: center;
}
</style>
