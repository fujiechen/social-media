<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { fetchCommentList, fetchHistoryList, fetchLikeList } from "@/services/media";
import MediaList1 from "@/components/MediaList1";
import { showFailToast } from "vant";

const route = useRoute();
const router = useRouter();

const history = ref({
  list: [],
  currentPage: 1,
  loading: true,
  hasMorePages: true,
});

const like = ref({
  list: [],
  currentPage: 1,
  loading: true,
  hasMorePages: true,
});

const comment = ref({
  list: [],
  currentPage: 1,
  loading: true,
  hasMorePages: true,
});

const active = ref('');

const loadHistory = async () => {
  history.value.loading = true;
  try {
    const response = await fetchHistoryList({
      page: history.value.currentPage++,
      per_page: 10,
    });
    history.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    history.value.list = [
      ...history.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取历史列表，请稍后重试！');
  }
  history.value.loading = false;
};

const loadLike = async () => {
  like.value.loading = true;
  try {
    const response = await fetchLikeList({
      page: like.value.currentPage++,
      per_page: 10,
    });
    like.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    like.value.list = [
      ...like.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取喜欢列表，请稍后重试！');
  }
  like.value.loading = false;
};

const loadComment = async () => {
  comment.value.loading = true;
  try {
    const response = await fetchCommentList({
      page: comment.value.currentPage++,
      per_page: 10,
    });
    comment.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
    comment.value.list = [
      ...comment.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取评论列表，请稍后重试！');
  }
  comment.value.loading = false;
};
const updateContent = async () => {
  const query = { ...route.query };
  delete query.type;
  await router.replace({ query })

  if (active.value === 'history' && history.value.hasMorePages) {
    await loadHistory();
  } else if (active.value === 'like' && like.value.hasMorePages) {
    await loadLike();
  } else if (active.value === 'comment' && comment.value.hasMorePages) {
    await loadComment();
  }
};

onMounted(async () => {
  active.value = route.query?.type || 'history';
});

watch(async () => active.value, updateContent);

</script>

<template>
  <van-tabs v-model:active="active" class="account-activity-page-tabs">
    <van-tab title="历史" name="history">
      <MediaList1
        :medias="history.list"
        :loading="history.loading"
        :has-more-pages="history.hasMorePages"
        :load-next-page="loadHistory"
      />
    </van-tab>
    <van-tab title="喜欢" name="like">
      <MediaList1
        :medias="like.list"
        :loading="like.loading"
        :has-more-pages="like.hasMorePages"
        :load-next-page="loadLike"
      />
    </van-tab>
    <van-tab title="评论" name="comment">
      <MediaList1
        :medias="comment.list"
        :loading="comment.loading"
        :has-more-pages="comment.hasMorePages"
        :load-next-page="loadComment"
      />
    </van-tab>
  </van-tabs>
</template>
<style>
.account-activity-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
