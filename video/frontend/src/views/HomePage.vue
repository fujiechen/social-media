<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { fetchMediaSuggestList } from "@/services/media";
import { fetchCategoryList } from "@/services/category";
import { getActorList } from "@/services/actor";
import { fetchTagList } from "@/services/tag";
import MediaGrid from "@/components/MediaGrid";
import ActorList from "@/components/ActorList";
import TagList from "@/components/TagList";
import CategoryList from "@/components/CategoryList.vue";
import { showFailToast } from "vant";

const route = useRoute();
const router = useRouter();
const active = ref('');

const media = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
  refreshing: false,
});

const category = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});

const actor = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});

const tag = ref({
  list: [],
  currentPage: 1,
  loading: false,
  hasMorePages: false,
});

const onLoadMedias = async () => {
  if (media.value.refreshing) {
    media.value.list = [];
    media.value.refreshing = false;
    media.value.currentPage = 1;
  }

  media.value.loading = true;
  try {
    const response = await fetchMediaSuggestList({
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
  media.value.loading = false;
};

const onRefreshMedias = async () => {
  media.value.finished = false;
  await onLoadMedias();
};

const loadCategoryList = async () => {
  category.value.loading = true;
  try {
    const response = await fetchCategoryList({
      page: category.value.currentPage++,
      per_page: 9,
    });
    category.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    category.value.list = [
      ...category.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取分类列表，请稍后重试！');
  }
  category.value.loading = false;
};

const loadActorList = async () => {
  actor.value.loading = true;
  try {
    const response = await getActorList({
      page: actor.value.currentPage++,
      per_page: 10,
    });
    actor.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    actor.value.list = [
      ...actor.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取演员列表，请稍后重试！');
  }
  actor.value.loading = false;
};

const loadTagList = async () => {
  tag.value.loading = true;
  try {
    const response = await fetchTagList({
      page: tag.value.currentPage++,
      per_page: 21,
    });
    tag.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    tag.value.list = [
      ...tag.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取标签列表，请稍后重试！');
  }
  tag.value.loading = false;
};

const switchTabs = async () => {
  const query = { ...route.query };
  delete query.tab;
  await router.replace({ query })

  if (active.value === 'category') {
    if (category.value.list.length === 0) {
      await loadCategoryList();
    }
  } else if (active.value === 'actor') {
    if (actor.value.list.length === 0) {
      await loadActorList();
    }
  } else if (active.value === 'tag') {
    if (tag.value.list.length === 0) {
      await loadTagList();
    }
  } else if (active.value === 'suggestion'){
      await onLoadMedias();
  }
};

onMounted(async () => {
  active.value = route.query?.tab || 'suggestion';
});

watch(() => active.value, async () => {
  await switchTabs();
});
</script>

<template>
  <van-tabs
    v-model:active="active"
    class="home-page-tabs"
  >
    <van-tab title="推荐" name="suggestion">
      <van-pull-refresh
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
    </van-tab>
    <van-tab title="分类" name="category">
      <CategoryList
        :categories="category.list"
        :loading="category.loading"
        :has-more-pages="category.hasMorePages"
        :load-next-page="loadCategoryList"
      />
    </van-tab>
    <van-tab title="演员" name="actor">
      <ActorList
        :actors="actor.list"
        :loading="actor.loading"
        :has-more-pages="actor.hasMorePages"
        :load-next-page="loadActorList"
      />
    </van-tab>
    <van-tab title="标签" name="tag">
      <TagList
        :tags="tag.list"
        :loading="tag.loading"
        :has-more-pages="tag.hasMorePages"
        :load-next-page="loadTagList"
      />
    </van-tab>
  </van-tabs>
</template>
<style>
.home-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
