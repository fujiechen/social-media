<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import MediaList2 from "@/components/MediaList2";
import { fetchMediaList } from "@/services/media";
import { getActor } from "@/services/actor";
import { showFailToast } from "vant";

const route = useRoute();

const actor = ref({});
const actorLoading = ref(true);

const media = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
});
const sort = ref('default');
const sortOptions = [
  { text: '默认排序', value: 'default' },
  { text: '点赞排序', value: 'likes' },
  { text: '收藏排序', value: 'favorites' },
  { text: '评论排序', value: 'comments' },
];

const active = ref('all');
const tabs = [
  { title: '全部', value: 'all' },
  { title: '视频', value: 'Video' },
  { title: '图册', value: 'Album' },
  { title: '合集', value: 'Series' },
];

const loadMediaList = async () => {
  media.value.loading = true;

  try {
    const params = {
      page: media.value.currentPage++,
      per_page: 10,
      actor_id: route.params.id,
    };
    if (active.value !== 'all') {
      params['types[]'] = active.value;
    }
    if (sort.value !== 'default') {
      params[`order_bys[${sort.value}]`] = 'desc';
    }
    const response = await fetchMediaList(params);
    media.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    media.value.list = [
      ...media.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取媒体列表，请稍后重试！');
    media.value.finished = true;
  }

  media.value.loading = false;
};

onMounted(async () => {
  actor.value = await getActor(route.params.id);
  actorLoading.value = false;

  await loadMediaList();
});

const onClickTab = async () => {
  media.value.list = [];
  media.value.currentPage = 1;

  await loadMediaList();
};

const onSortChange = async () => {
  media.value.list = [];
  media.value.currentPage = 1;

  await loadMediaList();
};
</script>

<template>
  <van-space direction="vertical" fill>
    <van-skeleton
      :loading="actorLoading"
      title
      :row="5"
    />
    <van-card
      v-if="!actorLoading"
      class="actor-name-container"
      :title="actor.name"
      :thumb="actor?.avatar_file?.url"
      :desc="`${actor.country}演员`"
    />
    <van-dropdown-menu>
      <van-dropdown-item v-model="sort" :options="sortOptions" @change="onSortChange"  />
    </van-dropdown-menu>
    <van-tabs
      v-model:active="active"
      @click-tab="onClickTab"
    >
      <van-tab
        v-for="tab in tabs"
        :key="tab.value"
        :title="tab.title"
        :name="tab.value"
      >
        <MediaList2
          :medias="media.list"
          :loading="media.loading"
          :has-more-pages="!media.finished"
          :load-next-page="loadMediaList"
        />
      </van-tab>
    </van-tabs>
  </van-space>
</template>
<style>
.actor-name-container .van-card__content {
  justify-content: center;
  margin-left: 1.5rem;
}

.actor-name-container .van-card__desc {
  font-size: 1rem;
  line-height: 1rem;
  margin-top: .5rem;
}

.actor-name-container .van-card__title {
  font-size: 1.5rem;
  line-height: 1.5rem;
}
</style>
