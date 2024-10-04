<script setup>
import {computed} from "vue";
import { formatNumberToChineseDecimal, getDateTimeDiff } from "@/utils";

const props = defineProps({
  data: {
    type: Object,
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
});

const datetimeDiff = computed(() => {
  return getDateTimeDiff(props.data.created_at);
});
</script>

<template>
  <div class='w-50 mx-2'>
    <van-text-ellipsis
      rows="2"
      :content="data.name"
      class="media-list-1-side-media-name"
    />
    <span class="d-block fs-m text-body">{{ data.user.nickname }}</span>
    <div class="d-flex justify-content-between">
      <span class="d-block fs-m text-secondary">{{ datetimeDiff }}</span>
      <div class="d-flex align-items-center">
        <van-icon
          v-if="data?.meta?.user?.like"
          color="#f53636"
          name="like"
          size=".9em"
        />
        <van-icon
          v-else
          name="like-o"
          size=".9em"
        />
        <span class="fs-s ms-1">{{ formatNumberToChineseDecimal(data?.meta?.count?.likes) }}</span>
      </div>
    </div>
    <div
      class="preview-container d-flex justify-content-between align-items-center"
      v-if="props.isPreviewAble"
      @click="props.playPreview()"
    >
      <van-button
        :icon="props.isPlaying ? 'pause' : 'play'"
        color="#fc7488"
        size="mini"
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
  </div>
</template>
<style scoped>
.media-list-1-side-media-name {
  font-size: 1rem;
  line-height: 1rem;
  font-weight: bold;
}
.preview-container .van-button__text {
  margin-left: 0 !important;
}
</style>
