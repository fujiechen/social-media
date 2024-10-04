<script setup>
import { onMounted, ref } from "vue";
import { fetchFavoriteList } from "@/services/media";
import MediaList1 from "@/components/MediaList1";
import { showFailToast } from "vant";

const media = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});

const loadMedias = async () => {
  media.value.loading = true;
  try {
    const response = await fetchFavoriteList({
      page: media.value.currentPage++,
      per_page: 10,
    });
    media.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    media.value.list = [
      ...media.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取收藏列表，请稍后重试！');
  }
  media.value.loading = false;
};

onMounted(async () => {
  await loadMedias();
});
</script>

<template>
  <van-space direction="vertical" fill>
    <MediaList1
      :medias="media.list"
      :loading="media.loading"
      :has-more-pages="media.hasMorePages"
      :load-next-page="loadMedias"
    />
  </van-space>
</template>
