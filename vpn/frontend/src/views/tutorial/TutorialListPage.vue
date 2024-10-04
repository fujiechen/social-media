<script setup>
import {onMounted, ref, watch} from "vue";
import {useTutorialStore} from "@/stores/tutorial.store";
import {storeToRefs} from "pinia";
import {useRoute} from "vue-router";

const route = useRoute();
const os = ref('auto')
const tutorialStore = useTutorialStore();
const {tutorial} = storeToRefs(tutorialStore);
const operationSystems = ref([
  {'id': 'share', 'name': '合作'},
  {'id': 'ios', 'name': '苹果'},
  {'id': 'android', 'name': '安卓'},
  {'id': 'win', 'name': 'Windows'},
  {'id': 'mac', 'name': 'Mac'},
]);

const updateOs = () => {
  if (route.params.os === 'auto') {
    const userAgent = window.navigator.userAgent;
    if (userAgent.includes("Win")) {
      os.value = 'win';
      return;
    }
    if (userAgent.includes("Mac")) {
      os.value = 'mac';
      return;
    }

    if (/iPhone|iPad|iPod/.test(userAgent)) {
      os.value = 'ios';
      return;
    }

    if (/Android/.test(userAgent)) {
      os.value = 'android';
    }
  } else {
    os.value = route.params.os;
  }
}

onMounted(async () => {
  updateOs();
});

watch(os, async (newOs) => {
  if (newOs) {
    await tutorialStore.fetchTutorial(newOs);
  }
});

</script>

<template>
  <van-space direction="vertical" fill>
    <van-tabs v-model:active="os">
      <van-tab v-for="operatingSystem in operationSystems"
               :key="operatingSystem.id"
               :title="operatingSystem.name"
               :name="operatingSystem.id"
               :to="{name:'tutorial',params:{os:operatingSystem.id}}"
      >
        <template #title v-if="operatingSystem.id === 'share'">
          <strong>{{ operatingSystem.name }}</strong>
        </template>
        <van-cell-group inset class="file-panel">
          <van-cell v-if="operatingSystem.id !== 'share'" is-link title="我的VPN" :to="{name:'vpn'}">
            <template #value>
              查看
            </template>
          </van-cell>
          <van-cell
            v-for="file in tutorial.tutorial_files"
            :key="file.id"
            :title="file.name"
            is-link
            :url="file.file.url">
            <template #value>
              下载
            </template>
          </van-cell>
          <div style="margin: 10px">
            <h2>{{ tutorial.name }}</h2>
            <div v-html="tutorial.content "/>
          </div>
        </van-cell-group>
      </van-tab>
    </van-tabs>
  </van-space>
</template>
<style scoped>
.file-panel {
  margin-top: 5px;
}
</style>
