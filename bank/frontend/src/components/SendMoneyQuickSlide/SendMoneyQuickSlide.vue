<script setup>
import {
  computed, ref, onMounted, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { convertFriendToButton, convertFriendToName } from '@/helpers/friendList';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const friendsList = computed(() => store.state.userTransfer.friendsList);
const friendListReloadTrigger = computed(() => store.state.userTransfer.friendListReloadTrigger);

const loadingFriendList = ref(true);

const initAction = () => {
  loadingFriendList.value = true;
  store.dispatch('userTransfer/getFriendsList')
    .then(
      () => {
        loadingFriendList.value = false;
      },
      () => {
        loadingFriendList.value = false;
      },
    );
};

watch(friendListReloadTrigger, () => {
  initAction();
});

onMounted(() => {
  initAction();
});

const handleTransferPopupOpen = (index) => {
  if (index !== undefined && friendsList.value[index] !== undefined) {
    store.commit('userTransfer/openPopup', {
      name: friendsList.value[index].name,
      email: friendsList.value[index].email,
    });
  } else {
    store.commit('userTransfer/openPopup', {});
  }
};

const transferMoneyColorClassList = [
  'bg-yellow-dark',
  'bg-blue-dark',
  'bg-brown-dark',
  'bg-green-dark',
];
</script>

<template>
  <!-- Send Money Title-->
  <div class="content mb-0">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('asset.transfer.quick_action_text') }}</h3>
      </div>
    </div>
  </div>

  <!-- Send Money Slider-->
  <template
    v-if="loadingFriendList"
  >
    <p
      class="font-12 mt-0 mb-0 text-center"
      style="margin-left:.25rem"
    >
      <span
        class="spinner-border spinner-border-sm"
      ></span>
    </p>
    <div class="pb-3"></div>
  </template>
  <div v-else class="d-flex text-center">
    <div
      v-for="index in [0,1,2,3]"
      v-bind:key="index"
      class="m-auto"
    >
      <a
        @click="handleTransferPopupOpen(index)"
        href="#"
        data-card-height="60"
        data-bs-toggle="offcanvas"
        data-bs-target="#menu-transfer"
        :class="'icon icon-xxl rounded-m shadow-m ' + transferMoneyColorClassList[index]"
      >
        {{
          convertFriendToButton(friendsList[index], t('asset.transfer.add_button'))
        }}
      </a>
      <h6 class="pt-2">{{
          convertFriendToName(friendsList[index], t('asset.transfer.add_label'))
        }}</h6>
    </div>
  </div>
</template>
