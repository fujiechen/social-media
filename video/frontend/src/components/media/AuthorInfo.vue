<script setup>
import {computed} from "vue";
import {useRouter} from "vue-router";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
  },
  clickFollowAuthor: {
    type: Function,
    required: false,
    default: () => {},
  },
});

const info = computed(() => {
  return {
    authorId: props.data.user.id,
    authorName: props.data.user.nickname,
    isFollowed: props.data.meta.user ? props.data.meta.user.subscribe : false,
  };
});

const onClickCreator = () => {
  router.push({
    name: "mediaUser",
    params: {
      id: info.value.authorId,
    },
  });
};
</script>

<template>
  <div class="d-flex mx-3 align-items-center">
    <span
      @click="onClickCreator"
      class="media-page-avatar"
    >
      {{info.authorName ? info.authorName.charAt(0) : 'A'}}
    </span>
    <div @click="onClickCreator">
      <span class="d-block fs-m text-body">{{ info.authorName }}</span>
    </div>
    <template v-if="props.data.meta.user">
      <van-button
        v-if="info.isFollowed"
        class="follow-button ms-auto"
        round
        size="small"
        type="success"
        disabled
      >
        已关注
      </van-button>
      <van-button
        v-else
        class="follow-button ms-auto"
        round
        size="small"
        hairline
        plain
        type="success"
        @click="props.clickFollowAuthor"
      >
        关注
      </van-button>
    </template>
  </div>
</template>

<style lang="scss" scoped>
.follow-button {
  min-width: 5rem;
}

.media-page-avatar {
  font-size: 1rem;
  height: 2.5rem;
  line-height: 2.5rem;
  width: 2.5rem;
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
