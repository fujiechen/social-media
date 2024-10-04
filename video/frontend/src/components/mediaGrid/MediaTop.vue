<script setup>
import {computed} from "vue";
import {getDateTimeDiff} from "@/utils";
import {useRouter} from "vue-router";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

const datetimeDiff = computed(() => {
  return getDateTimeDiff(props.data.created_at);
});

const handleUserClick = () => {
  router.push({
    name: "mediaUser",
    params: {
      id: props.data.user.id,
    },
  });
};
</script>
<template>
  <div class="d-flex mx-3 align-items-center">
    <span
      @click="handleUserClick"
      class="media-grid-page-avatar"
    >
      {{props.data?.user?.nickname ? props.data.user.nickname.charAt(0) : 'A'}}
    </span>
    <div @click="handleUserClick">
      <span class="d-block fs-m text-body">{{ props.data?.user?.nickname }}</span>
      <span class="d-block fs-m text-secondary">{{ datetimeDiff }}</span>
    </div>
  </div>
</template>

<style scoped>
.media-grid-page-avatar {
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
