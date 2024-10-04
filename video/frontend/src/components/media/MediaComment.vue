<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { createCommentByMediaId, fetchCommentListByMediaId } from "@/services/comment";
import { useAuthStore } from "@/stores/auth.store";
import { showToast } from "vant";
import { formatNumberToChineseDecimal, getDateTimeDiff } from "@/utils";

const router = useRouter();

const authStore = useAuthStore();

const loggedIn = computed(() => {
  return !!authStore.user;
});

const userNickname = computed(() => {
  return authStore.user?.nickname ?? 'A';
});

const props = defineProps({
  mediaId: {
    type: Number,
    default: 0,
  },
});

const comment = ref({
  list: [],
  currentPage: 1,
  loading: true,
  hasMorePages: false,
  totalCount: 0,
  error: null,
});

const commentPanelAnchors = ref([
  window.innerHeight,
]);
const commentPanelHeight = ref(commentPanelAnchors.value[0]);
const commentPanelShow = ref(false);

const loadCommentList = async () => {
  comment.value.loading = true;
  let response;
  try {
    response = await fetchCommentListByMediaId(props.mediaId, {
      page: comment.value.currentPage++,
      per_page: 10,
    });
  } catch {
    comment.value.error = '评论区暂不可用';
    comment.value.loading = false;
    return;
  }

  comment.value.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
  comment.value.totalCount = response.meta.pagination.total;
  comment.value.list = [
    ...comment.value.list,
    ...response.data,
  ];
  comment.value.loading = false;
};

const initLoadCommentList = async () => {
  comment.value = {
    list: [],
    currentPage: 1,
    loading: true,
    hasMorePages: false,
    totalCount: 0,
    error: null,
  };
  await loadCommentList();
};

onMounted(async () => {
  if (props.mediaId > 0) {
    await initLoadCommentList();
  }
});

watch(() => props.mediaId, async () => {
  if (props.mediaId > 0) {
    await initLoadCommentList();
  }
});

const handleLogin = () => {
  localStorage.setItem('video-ref', window.location.href);
  router.push({ name: "login" });
};

const loadingSubmitComment = ref(false);
const newComment = ref('');
const showNewCommentButtons = ref(false);

const handleCommentTextAreaClick = () => {
  showNewCommentButtons.value = true;
};

const handleSubmitComment = async () => {
  if (newComment.value.length < 5) {
    showToast('评论至少5个字');
    return;
  }

  loadingSubmitComment.value = true;
  try {
    await createCommentByMediaId(props.mediaId, {
      comment: newComment.value,
    });
    showToast('评论提交成功！');
  } catch (e) {
    showToast('评论提交失败，请稍后再试！');
    loadingSubmitComment.value = false;
    return;
  }

  loadingSubmitComment.value = false;
  newComment.value = '';
  showNewCommentButtons.value = false;
  await initLoadCommentList();
};

const handleCancelSubmitComment = () => {
  newComment.value = '';
  showNewCommentButtons.value = false;
};

defineExpose({
  commentPanelShow,
});
</script>
<template>
<div class="w-100">
  <div class="mx-3 my-2 px-2 py-2 rounded comment-area">
    <div v-if="comment.error" class="w-100">
      <span class="text-center fs-m">{{comment.error}}</span>
    </div>
    <div v-else-if="comment.loading" class="w-100">
      <span class="text-center fs-m">评论区加载中...</span>
    </div>
    <div v-else>
      <span class="comment-title">
        评论 {{comment.totalCount > 0 ? formatNumberToChineseDecimal(comment.totalCount) : ''}}
      </span>
      <div v-if="comment.list.length > 0" class="d-flex align-items-center"  @click="commentPanelShow=true">
        <span class="comment-avatar">{{ comment.list[0].user.nickname.charAt(0) }}</span>
        <div class="comments-simplebox-comment rounded m-1 fs-m">
          <van-text-ellipsis
            rows="2"
            :content="comment.list[0].comment"
          />
        </div>
      </div>
      <div v-else-if="!loggedIn" class="d-flex align-items-center"  @click="handleLogin">
        <div class="comments-simplebox-placeholder rounded m-1 p-2 fs-m">
          <span>登录添加评论</span>
        </div>
      </div>
      <div v-else class="d-flex align-items-center"  @click="commentPanelShow=true">
        <span class="comment-avatar">{{ userNickname.charAt(0) }}</span>
        <div class="comments-simplebox-placeholder rounded m-1 p-2 fs-m">
          <span>添加评论...</span>
        </div>
      </div>
    </div>
  </div>
</div>
<van-floating-panel
  v-if="commentPanelShow"
  v-model:height="commentPanelHeight"
  :anchors="commentPanelAnchors"
  :content-draggable="false"
  :lock-scroll="true"
>
  <van-sticky offset-top="30px">
    <div class="d-flex align-items-center px-3 pb-2 mb-2 border-bottom">
      <span class="comment-panel-title">
        评论 {{comment.totalCount > 0 ? formatNumberToChineseDecimal(comment.totalCount) : ''}}
      </span>
      <van-icon class="comment-panel-close" name="cross" @click="commentPanelShow=false"/>
    </div>
  </van-sticky>
  <div v-if="!loggedIn" @click="handleLogin" class="w-100 text-center">
    <van-button round size="small" type="primary" class="m-1 p-2">
      登录/注册添加评论
    </van-button>
  </div>
  <div v-else>
    <div class="mx-2 my-3 d-flex align-items-start">
      <div>
        <span class="comment-panel-avatar mx-3">{{ userNickname.charAt(0) }}</span>
      </div>
      <van-field
        v-model="newComment"
        rows="1"
        autosize
        type="textarea"
        placeholder="添加评论..."
        @click="handleCommentTextAreaClick"
      />
    </div>
    <div v-if="showNewCommentButtons" class="mx-2 my-3 d-flex justify-content-end">
      <van-button
        type="default"
        round
        size="small"
        class="me-2"
        @click="handleCancelSubmitComment"
      >
        取消
      </van-button>
      <van-button
        type="primary"
        round
        size="small"
        :loading="loadingSubmitComment"
        @click="handleSubmitComment"
      >
        评论
      </van-button>
    </div>
  </div>
  <van-divider />
  <div v-if="comment.error" class="text-center">
    <span class=" fs-m">{{comment.error}}</span>
  </div>
  <template v-else>
    <div
      v-for="commentItem in comment.list"
      :key="commentItem.id"
      class="w-100"
    >
      <div class="mx-2 my-3 d-flex align-items-start">
        <div>
          <span class="comment-panel-avatar mx-3">{{ commentItem.user.nickname.charAt(0) }}</span>
        </div>
        <div class="w-100 me-2">
          <div class="d-flex justify-content-between me-2 text-secondary">
            <span class="fs-s">{{ commentItem.user.nickname }}</span>
            <span class="fs-s">{{ getDateTimeDiff(commentItem.created_at )}}</span>
          </div>
          <van-text-ellipsis
            rows="3"
            :content="commentItem.comment"
            expand-text="展开"
            collapse-text="收起"
            class="me-3 w-100"
          />
        </div>
      </div>
    </div>
    <div v-if="comment.loading" class="mt-3 text-center">
      <van-loading class="mb-5" vertical>加载中...</van-loading>
    </div>
    <div v-else-if="comment.hasMorePages" @click="loadCommentList">
      <van-divider class="mb-5" dashed>加载更多</van-divider>
    </div>
    <van-empty
      v-else-if="comment.list.length === 0"
      image="search"
      description="没有了"
    />
  </template>
</van-floating-panel>
</template>
<style>
.comment-area {
  background-color: rgba(0,0,0,0.05);
}

.comment-title {
  font-size: .9rem;
}

.comments-simplebox-comment {
  flex-grow: 1;
}

.comments-simplebox-placeholder {
  background-color: var(--van-background-2);
  flex-grow: 1;
}

.comment-panel-title {
  font-size: 1.1rem;
  flex-grow: 1;
}

.comment-panel-close {
  font-size: 1.1rem;
}

.comment-panel-avatar {
  font-size: 1rem;
  height: 2rem;
  line-height: 2rem;
  width: 2rem;
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

.comment-avatar {
  font-size: .9rem;
  height: 2rem;
  line-height: 2rem;
  width: 2rem;
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
