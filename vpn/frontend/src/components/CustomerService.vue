<script setup>
import {storeToRefs} from "pinia";
import {useMetaStore} from "@/stores/meta.store";
import {useGlobalStore} from "@/stores/global.store";

const globalStore = useGlobalStore();
const metaStore = useMetaStore();

const {showCustomerServicePopup} = storeToRefs(globalStore);
const {metas} = storeToRefs(metaStore);
</script>

<template>
  <van-popup
    v-model:show="showCustomerServicePopup"
    position="bottom"
    closeable
    round
  >
    <van-row justify="center">
      <van-image
        :src="metas.find(item => item.meta_key === 'CUSTOMER_SERVICE_QR_HTML').meta_value"
      />
    </van-row>
    <van-row justify="center">
      <p>请添加微信客服</p>
    </van-row>
  </van-popup>
</template>
