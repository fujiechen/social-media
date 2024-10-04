<script setup>
import { computed, onMounted, ref } from "vue";
import MediaList2 from "@/components/MediaList2";
import GridText from "@/components/GridText";
import { fetchMediaUserByUserId } from "@/services/mediaUser";
import { fetchMediaList } from "@/services/media";
import { showFailToast } from "vant";

const user = JSON.parse(localStorage.getItem('video-user'));
const active = ref('all');

const mediaUser = ref({});
const mediaUserLoading = ref(true);

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
  try {
    const response = await fetchMediaList({
      media_user_id: user.id,
      page: allMedia.value.currentPage++,
      per_page: 10,
    });
    allMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    allMedia.value.list = [
      ...allMedia.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取全部列表，请稍后重试！');
  }
  allMedia.value.loading = false;
};

const loadVideoMedaList = async () => {
  videoMedia.value.loading = true;
  try {
    const response = await fetchMediaList({
      media_user_id: user.id,
      page: videoMedia.value.currentPage++,
      per_page: 10,
      'types[]': 'Video',
    });
    videoMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    videoMedia.value.list = [
      ...videoMedia.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取视频媒体列表，请稍后重试！');
  }
  videoMedia.value.loading = false;
};

const loadAlbumMedaList = async () => {
  albumMedia.value.loading = true;
  try {
    const response = await fetchMediaList({
      media_user_id: user.id,
      page: albumMedia.value.currentPage++,
      per_page: 10,
      'types[]': 'Album',
    });
    albumMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    albumMedia.value.list = [
      ...albumMedia.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取图集媒体列表，请稍后重试！');
  }
  albumMedia.value.loading = false;
};

const loadSeriesMedaList = async () => {
  seriesMedia.value.loading = true;
  try {
    const response = await fetchMediaList({
      media_user_id: user.id,
      page: seriesMedia.value.currentPage++,
      per_page: 10,
      'types[]': 'Series',
    });
    seriesMedia.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    seriesMedia.value.list = [
      ...seriesMedia.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取合集媒体列表，请稍后重试！');
  }
  seriesMedia.value.loading = false;
};

onMounted(async () => {
  mediaUserLoading.value = true;
  mediaUser.value = await fetchMediaUserByUserId(user.id);
  mediaUserLoading.value = false;
  await loadAllMedaList();
});

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

const shoppingCartImagePath = computed(() => {
  return `${process.env.VUE_APP_SUB_PATH}img/shopping-cart.png`;
});
</script>

<template>
  <van-space direction="vertical" fill>
    <van-skeleton
      :loading="mediaUserLoading"
      title
      :row="5"
    />
    <template v-if="!mediaUserLoading">
      <van-card
        class="media-user-name-container"
        :title="mediaUser.nickname"
      />

      <van-grid :border="false" column-num="4">
        <van-grid-item
          :to= "{ name: 'userProducts', params: { id: mediaUser.id } }"
        >
          <van-image width="2rem" :src="shoppingCartImagePath" />
        </van-grid-item>
        <van-grid-item text="关注">
          <template #icon>
            <GridText :text="String(mediaUser.publisher.subscriptions_count)"/>
          </template>
        </van-grid-item>
        <van-grid-item text="粉丝">
          <template #icon>
            <GridText :text="String(mediaUser.publisher.subscribers_count )"/>
          </template>
        </van-grid-item>
        <van-grid-item text="获赞">
          <template #icon>
            <GridText :text="String(mediaUser.publisher.subscribers_count )"/>
          </template>
        </van-grid-item>
      </van-grid>

<!--    <van-cell-group inset>-->
<!--        <van-button block round type="success">-->
<!--          创建媒体-->
<!--        </van-button>-->
<!--    </van-cell-group>-->
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
