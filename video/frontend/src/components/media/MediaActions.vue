<script setup>
import { computed, ref } from "vue";
import { showToast } from "vant";
import { useAuthStore } from "@/stores/auth.store";
import { useRoute, useRouter } from "vue-router";
import { formatNumberToChineseDecimal } from "@/utils";
import SharePopup from "@/components/SharePopup.vue";
import { useUserShareStore } from "@/stores/userShare.store";
import { storeToRefs } from "pinia";
import { useGlobalStore } from "@/stores/global.store";

const globalStore = useGlobalStore();
const userShareStore = useUserShareStore();
const {sharePopup} = storeToRefs(userShareStore);
const router = useRouter();
const route = useRoute();
const {user} = useAuthStore();

const props = defineProps({
  data: Object,
  clickComment: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickLike: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickFavorite: {
    type: Function,
    required: false,
    default: () => {},
  },
});

const info = computed(() => {
  return {
    commentCount: props.data.meta.count.comments,
    isLiked: props.data.meta?.user?.like,
    likeCount: formatNumberToChineseDecimal(props.data.meta.count.likes),
    isFavorite: props.data.meta?.user?.favorite,
    favoriteCount: formatNumberToChineseDecimal(props.data.meta.count.favorites),
  };
});

const showShare = ref(false);
const options = [
  {id: 'link', name: '复制链接', icon: 'link'},
  {id: 'poster', name: '分享海报', icon: 'poster'},
];

const onSelect = async (option) => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  globalStore.loading = true;
  await userShareStore.createUserShare(
    'media',
    route.params.id,
    window.location.href
  );
  globalStore.loading = false;

  if (option.id === 'link') {
    if (sharePopup.value.userShare) {
      if (window.isSecureContext) {
        await navigator.clipboard.writeText(sharePopup.value.userShare.share_url);
      }
      showToast('链接已复制');
    } else {
      showToast('分享链接生成失败，请稍后再试')
    }
  } else if (option.id === 'poster') {
    userShareStore.sharePopup.show = true;
  }

  showShare.value = false;
};
</script>
<template>
  <van-row justify="space-around" class="mt-1">
    <div
      class="d-flex flex-column align-items-center"
      @click="showShare = true"
    >
      <van-icon
        name="share-o"
        size="1.3em"
      />
      <span class="fs-m action-inactive">
        分享
      </span>
    </div>
    <div
      class="d-flex flex-column align-items-center"
      @click="props.clickComment"
    >
      <van-icon
        name="comment-o"
        size="1.3em"
      />
      <span class="fs-m action-inactive">
        {{ info.commentCount }}
      </span>
    </div>
    <div
      class="d-flex flex-column align-items-center"
      @click="props.clickLike"
    >
      <van-icon
        v-if="info.isLiked"
        name="like"
        color="#f53636"
        size="1.3em"
      />
      <van-icon
        v-else
        name="like-o"
        size="1.3em"
      />
      <span class="fs-m action-inactive">
        {{ info.likeCount }}
      </span>
    </div>
    <div
      class="d-flex flex-column align-items-center"
      @click="props.clickFavorite"
    >
      <van-icon
        v-if="info.isFavorite"
        name="star"
        color="#f7b602"
        size="1.3em"
      />
      <van-icon
        v-else
        name="star-o"
        size="1.3em"
      />
      <span class="fs-m action-inactive">
        {{ info.favoriteCount }}
      </span>
    </div>
  </van-row>
  <van-share-sheet
    v-model:show="showShare"
    title="立即分享给好友"
    :options="options"
    @select="onSelect"
  />
  <SharePopup />
</template>
<style scoped>
.action-inactive {
  color: var(--bs-body-color);
}
</style>
