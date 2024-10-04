<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import MediaList2 from "@/components/MediaList2";
import ActorList from "@/components/ActorList";
import TagList from "@/components/TagList";
import CategoryList from "@/components/CategoryList.vue";
import UserList from "@/components/UserList.vue";
import { fetchMediaList } from "@/services/media";
import { fetchCategoryList } from "@/services/category";
import { getActorList } from "@/services/actor";
import { fetchTagList } from "@/services/tag";
import { fetchMediaUserList } from "@/services/mediaUser";
import { useAuthStore } from "@/stores/auth.store";
import { fetchSearchHistory, fetchSearchHot } from "@/services/search";
import { showConfirmDialog, showFailToast, showToast } from "vant";
import { unsubscribeUserByUserId } from "@/services/follow";

const router = useRouter();
const route = useRoute();

const search = ref(route.query.s);
const active = ref('media');

const authStore = useAuthStore();

const loggedIn = computed(() => {
  return !!authStore.user;
});

watch(() => route.query.s, async (value) => {
  if (value) {
    step2.value = true;
    search.value = value;
  } else {
    step2.value = false;
    search.value = '';
  }

  media.value.list = [];
  media.value.finished = false;
  category.value.list = [];
  category.value.finished = false;
  actor.value.list = [];
  actor.value.finished = false;
  tag.value.list = [];
  tag.value.finished = false;
  mediaUser.value.list = [];
  mediaUser.value.finished = false;

  await doSearch();
});

const step2 = ref(false);

const searchHot = ref({
  list: [],
  loading: false,
});

const searchHistory = ref({
  list: [],
  loading: false,
});

const media = ref({
  list: [],
  params: null,
  loading: false,
  finished: false,
});

const category = ref({
  list: [],
  params: null,
  loading: false,
  finished: false,
});

const actor = ref({
  list: [],
  params: null,
  loading: false,
  finished: false,
});

const tag = ref({
  list: [],
  params: null,
  loading: false,
  finished: false,
});

const mediaUser = ref({
  list: [],
  params: null,
  loading: false,
  finished: false,
});

const hotCategory = ref({
  list: [],
  currentPage: 1,
  loading: false,
});

const hotActor = ref({
  list: [],
  currentPage: 1,
  loading: false,
});

const hotTag = ref({
  list: [],
  currentPage: 1,
  loading: false,
});

const loadSearchHot = async () => {
  searchHot.value.loading = true;
  const response = await fetchSearchHot({
    page: 1,
    per_page: 10,
  });
  searchHot.value.list = [
    ...response.data,
  ];
  searchHot.value.loading = false;
};

const loadHotCategoryList = async () => {
  hotCategory.value.loading = true;
  const response = await fetchCategoryList({
    page: 1,
    per_page: 6,
  });
  hotCategory.value.list = [
    ...response.data,
  ];
  hotCategory.value.loading = false;
};

const loadHotActorList = async () => {
  hotActor.value.loading = true;
  const response = await getActorList({
    page: 1,
    per_page: 3,
  });
  hotActor.value.list = [
    ...response.data,
  ];
  hotActor.value.loading = false;
};

const loadHotTagList = async () => {
  hotTag.value.loading = true;
  const response = await fetchTagList({
    page: 1,
    per_page: 15,
  });
  hotTag.value.list = [
    ...response.data,
  ];
  hotTag.value.loading = false;
};

const searchHotList = computed(() => {
  const chunks = [];
  for (let i=0; i < searchHot.value.list.length; i+=2) {
    chunks.push(searchHot.value.list.slice(i, i+2));
  }
  return chunks;
});

const topHotSearch = computed(() => {
  if (searchHot.value.list.length > 0) {
    return searchHot.value.list[0].search;
  }
  return '';
});

const loadSearchHistory = async () => {
  searchHistory.value.loading = true;
  const response = await fetchSearchHistory({
    page: 1,
    per_page: 10,
  });
  searchHistory.value.list = [
    ...response.data,
  ];
  searchHistory.value.loading = false;
};

const searchHistoryList = computed(() => {
  const chunks = [];
  for (let i=0; i < searchHistory.value.list.length; i+=2) {
    chunks.push(searchHistory.value.list.slice(i, i+2));
  }
  return chunks;
});

const loadMediaList = async () => {
  media.value.loading = true;

  try {
    const response = await fetchMediaList(media.value.params);
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

const loadCategoryList = async () => {
  category.value.loading = true;

  try {
    const response = await fetchCategoryList(category.value.params);
    category.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    category.value.list = [
      ...category.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取分类列表，请稍后重试！');
    category.value.finished = true;
  }

  category.value.loading = false;
};

const loadActorList = async () => {
  actor.value.loading = true;

  try {
    const response = await getActorList(actor.value.params);
    actor.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    actor.value.list = [
      ...actor.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取演员列表，请稍后重试！');
    actor.value.finished = true;
  }

  actor.value.loading = false;
};

const loadTagList = async () => {
  tag.value.loading = true;

  try {
    const response = await fetchTagList(tag.value.params);
    tag.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    tag.value.list = [
      ...tag.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取标签列表，请稍后重试！');
    tag.value.finished = true;
  }

  tag.value.loading = false;
};

const loadMediaUserList = async () => {
  mediaUser.value.loading = true;

  try {
    const response = await fetchMediaUserList(mediaUser.value.params);
    mediaUser.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    mediaUser.value.list = [
      ...mediaUser.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取用户列表，请稍后重试！');
    mediaUser.value.finished = true;
  }

  mediaUser.value.loading = false;
};

const onLoadNextPage = async () => {
  if (active.value === 'media') {
    media.value.params = {
      ...media.value.params,
      page: media.value.params.page ? media.value.params.page + 1 : 1,
    };
  } else if (active.value === 'category') {
    category.value.params = {
      ...category.value.params,
      page: category.value.params.page ? category.value.params.page + 1 : 1,
    };
  } else if (active.value === 'actor') {
    actor.value.params = {
      ...actor.value.params,
      page: actor.value.params.page ? actor.value.params.page + 1 : 1,
    };
  } else if (active.value === 'tag') {
    tag.value.params = {
      ...tag.value.params,
      page: tag.value.params.page ? tag.value.params.page + 1 : 1,
    };
  } else if (active.value === 'mediaUser') {
    mediaUser.value.params = {
      ...mediaUser.value.params,
      page: mediaUser.value.params.page ? mediaUser.value.params.page + 1 : 1,
    };
  }

  await doSearch();
};

const doSearch = async () => {
  if (!step2.value) {
    await loadSearchHot();
    await loadHotCategoryList();
    await loadHotActorList();
    await loadHotTagList();

    if (loggedIn.value) {
      await loadSearchHistory();
    }

    return;
  }

  if (active.value === 'media') {
    if (media.value.list.length === 0) {
      media.value.params = {
        media_search_text: search.value,
        page: 1,
        per_page: 10,
      };
    }
    if (!media.value.finished) {
      await loadMediaList();
    }
  } else if (active.value === 'category') {
    if (category.value.list.length === 0) {
      category.value.params = {
        name: search.value,
        page: 1,
        per_page: 9,
      };
    }
    if (!category.value.finished) {
      await loadCategoryList();
    }
  } else if (active.value === 'actor') {
    if (actor.value.list.length === 0) {
      actor.value.params = {
        name: search.value,
        page: 1,
        per_page: 10,
      };
    }
    if (!actor.value.finished) {
      await loadActorList();
    }
  } else if (active.value === 'tag') {
    if (tag.value.list.length === 0) {
      tag.value.params = {
        name: search.value,
        page: 1,
        per_page: 21,
      };
    }
    if (!tag.value.finished) {
      await loadTagList();
    }
  } else if (active.value === 'mediaUser') {
    if (mediaUser.value.list.length === 0) {
      mediaUser.value.params = {
        name: search.value,
        page: 1,
        per_page: 10,
      };
    }
    if (!mediaUser.value.finished) {
      await loadMediaUserList();
    }
  }

  if (loggedIn.value) {
    await loadSearchHistory();
  }
};

onMounted(async () => {
  step2.value = !!search.value
  await doSearch();
});

const onClickTab = async ({ name }) => {
  if (
    (name === 'media' && media.value.list.length === 0) ||
    (name === 'category' && category.value.list.length === 0) ||
    (name === 'actor' && actor.value.list.length === 0) ||
    (name === 'tag' && tag.value.list.length === 0) ||
    (name === 'mediaUser' && mediaUser.value.list.length === 0)
  ) {
    await doSearch();
  }
};

const onSearch = async (searchValue) => {
  if (searchValue) {
    await router.push({
      name: 'mediaSearch',
      query: {
        s: searchValue,
      }
    });
  } else if (!step2.value) {
    await router.push({
      name: 'mediaSearch',
      query: {
        s: topHotSearch.value,
      }
    });
  } else {
    await router.push({ name: 'mediaSearch'});
  }
};

const onCancel = async () => {
  await router.push({name: 'mediaSearch'});
};

const onClickItem = async (searchValue) => {
  await router.push({
    name: 'mediaSearch',
    query: {
      s: searchValue,
    }
  });
};


const handleUnfollow = async (userId) => {
  if (!loggedIn.value) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  showConfirmDialog({
    title: '请确认取消关注',
  })
    .then(async () => {
      await unsubscribeUserByUserId(userId);
      mediaUser.value.list = mediaUser.value.list.map(mediaUser => {
        if (mediaUser.id ===  userId) {
          return {
            ...mediaUser,
            publisher: {
              ...mediaUser.publisher,
              is_followed: false,
            },
          };
        }
        return mediaUser;
      });
      showToast('已取消关注');
    })
    .catch(() => {});
};

</script>

<template>
  <van-space direction="vertical" fill>
    <van-search v-model="search" :placeholder="topHotSearch" @search="onSearch" @cancel="onCancel"/>
    <template v-if="!step2">
      <div v-if="loggedIn">
        <van-divider>搜索历史</van-divider>
        <div v-if="searchHistory.loading" class="mt-3 text-center">
          <div v-for="n in 5" :key="n" class="d-flex justify-content-around m-2">
            <van-skeleton-paragraph row-width="40%" class="mt-0"/>
            <van-divider vertical />
            <van-skeleton-paragraph row-width="40%" class="mt-0"/>
          </div>
        </div>
        <div v-else>
          <van-row v-for="(row, rowIndex) in searchHistoryList" :key="rowIndex" class="mx-3 my-2">
            <template v-for="(item, colIndex) in row" :key="colIndex">
              <van-col span="11" @click="onClickItem(item.search)">{{item.search}}</van-col>
              <van-col v-if="colIndex === 0" span="2">
                <van-divider vertical />
              </van-col>
            </template>
          </van-row>
        </div>
      </div>
      <van-divider>热搜排行</van-divider>
      <div v-if="searchHot.loading" class="mt-3 text-center">
        <div v-for="n in 10" :key="n" class="d-flex justify-content-around m-2">
          <van-skeleton-paragraph row-width="40%" class="mt-0"/>
          <van-divider vertical />
          <van-skeleton-paragraph row-width="40%" class="mt-0"/>
        </div>
      </div>
      <div v-else>
        <van-row v-for="(row, rowIndex) in searchHotList" :key="rowIndex" class="mx-3 my-2">
          <template v-for="(item, colIndex) in row" :key="colIndex">
            <van-col span="11" @click="onClickItem(item.search)">{{item.search}}</van-col>
            <van-col v-if="colIndex === 0" span="2">
              <van-divider vertical />
            </van-col>
          </template>
        </van-row>
      </div>
      <van-divider>热门分类</van-divider>
      <CategoryList
        :categories="hotCategory.list"
        :loading="hotCategory.loading"
        :has-more-pages="false"
        :load-next-page="loadHotCategoryList"
      />
      <div class="d-flex align-items-center justify-content-center">
        <span class="text-secondary fs-s" @click="router.push({name: 'home', query: {tab: 'category'}})">查看更多</span>
      </div>
      <van-divider>热门演员</van-divider>
      <ActorList
        :actors="hotActor.list"
        :loading="hotActor.loading"
        :has-more-pages="false"
        :load-next-page="loadHotActorList"
      />
      <div class="d-flex align-items-center justify-content-center">
        <span class="text-secondary fs-s" @click="router.push({name: 'home', query: {tab: 'actor'}})">查看更多</span>
      </div>
      <van-divider>热门标签</van-divider>
      <TagList
        :tags="hotTag.list"
        :loading="hotTag.loading"
        :has-more-pages="false"
        :load-next-page="loadHotTagList"
      />
      <div class="d-flex align-items-center justify-content-center">
        <span class="text-secondary fs-s" @click="router.push({name: 'home', query: {tab: 'tag'}})">查看更多</span>
      </div>
    </template>
    <van-tabs
      v-else
      class="search-page-tabs"
      v-model:active="active"
      @click-tab="onClickTab"
    >
      <van-tab title="全部" name="media">
        <MediaList2
          v-if="route.query.s"
          :medias="media.list"
          :loading="media.loading"
          :has-more-pages="!media.finished"
          :load-next-page="onLoadNextPage"
        />
      </van-tab>
      <van-tab title="分类" name="category">
        <CategoryList
          v-if="route.query.s"
          :categories="category.list"
          :loading="category.loading"
          :has-more-pages="!category.finished"
          :load-next-page="onLoadNextPage"
        />
      </van-tab>
      <van-tab title="演员" name="actor">
        <ActorList
          v-if="route.query.s"
          :actors="actor.list"
          :loading="actor.loading"
          :has-more-pages="!actor.finished"
          :load-next-page="onLoadNextPage"
        />
      </van-tab>
      <van-tab title="标签" name="tag">
        <TagList
          v-if="route.query.s"
          :tags="tag.list"
          :loading="tag.loading"
          :has-more-pages="!tag.finished"
          :load-next-page="onLoadNextPage"
        />
      </van-tab>
      <van-tab title="用户" name="mediaUser">
        <UserList
          v-if="route.query.s"
          :media-users="mediaUser.list"
          :loading="mediaUser.loading"
          :has-more-pages="!mediaUser.finished"
          :load-next-page="onLoadNextPage"
          :unfollow-user="handleUnfollow"
        />
      </van-tab>
    </van-tabs>
  </van-space>
</template>
<style>
.search-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
