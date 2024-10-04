<script setup>
import { computed } from "vue";
import {useRouter} from "vue-router";
import { formatNumberToChineseDecimal } from "@/utils";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  isPreviewAble: {
    type: Boolean,
    default: false,
  },
  playPreview: {
    type: Function,
    default: () => {},
  },
  loadingPreview: {
    type: Boolean,
    default: false,
  },
  isPlaying: {
    type: Boolean,
    default: false,
  },
  goToComment: {
    type: Function,
    default: () => {},
  },
  goToItem: {
    type: Function,
    default: () => {},
  },
});

const info = computed(() => {
  return {
    commentBadge: formatNumberToChineseDecimal(props.data.meta.count.comments),
    likeBadge: formatNumberToChineseDecimal(props.data.meta.count.likes),
    favoriteBadge: formatNumberToChineseDecimal(props.data.meta.count.favorites),
    isLiked: props.data.meta?.user?.like,
    isFavorite: props.data.meta?.user?.favorite,
  };
});

const openItem = () => {
  router.push({
    name: "media",
    params: {
      id: props.data.id,
    },
  });
};

const openItemComment = () => {
  router.push({
    name: "media",
    params: {
      id: props.data.id,
    },
  });
};
</script>
<template>
  <van-row justify="space-around" class="mt-1">
    <div class="d-flex align-items-center" @click="openItemComment">
      <van-icon
        name="comment-o"
        :badge="info.commentBadge"
        color="#adadac"
        size="1.3em"
      />
      <span class="fs-m ms-2">
        评论
      </span>
    </div>
    <div
      class="d-flex align-items-center"
      @click="openItem"
    >
      <van-icon
        v-if="info.isLiked"
        name="like"
        :badge="info.likeBadge"
        color="#f53636"
        size="1.3em"
      />
      <van-icon
        v-else
        name="like-o"
        :badge="info.likeBadge"
        color="#adadac"
        size="1.3em"
      />
      <span class="fs-m ms-2">
        赞
      </span>
    </div>
    <div
      class="d-flex align-items-center"
      @click="openItem"
    >
      <van-icon
        v-if="info.isFavorite"
        :badge="info.favoriteBadge"
        name="star"
        color="#f7b602"
        size="1.3em"
      />
      <van-icon
        v-else
        :badge="info.favoriteBadge"
        name="star-o"
        color="#adadac"
        size="1.3em"
      />
      <span class="fs-m ms-2">
        收藏
      </span>
    </div>
    <div
      class="d-flex align-items-center"
      v-if="props.isPreviewAble"
      @click="props.playPreview()"
    >
      <van-button
        :icon="props.isPlaying ? 'pause' : 'play'"
        color="#fc7488"
        size="small"
        type="default"
        hairline
        round
        :loading="props.loadingPreview"
        loading-text="加载中"
        loading-type="spinner"
      >
        预览
      </van-button>
    </div>
  </van-row>
</template>
