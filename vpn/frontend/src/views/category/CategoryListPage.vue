<script setup>

import {storeToRefs} from "pinia";
import {useCategoriesStore} from "@/stores/category.store";
import {onMounted, ref} from "vue";
import {useMetaStore} from "@/stores/meta.store";
import {useGlobalStore} from "@/stores/global.store";
import {useRouter} from "vue-router";

const router = useRouter();
const categoriesStore = useCategoriesStore();
const {categories} = storeToRefs(categoriesStore);

const metaStore = useMetaStore();
const {metas} = storeToRefs(metaStore);
const bannerUrl = ref('');

const themeVars = {
  cardPadding: '10px 30px',
  cardFontSize: 'var(--van-font-size-lg)',
  cardDescColor: 'var(--van-text-color-3)',
};

const globalStore = useGlobalStore();

onMounted(async () => {
  globalStore.loading = true;
  await categoriesStore.fetchAll();
  bannerUrl.value = metas.value.find(m => {return m.meta_key === 'BANNER_HOME_URL'}).meta_value;
  globalStore.loading = false;
})

</script>

<template>
  <van-space direction="vertical" fill>

    <van-grid :border="false" :column-num="1">
      <van-grid-item>
        <van-image
            :src="bannerUrl"
        />
      </van-grid-item>
    </van-grid>

    <van-grid column-num="4" clickable>
      <van-grid-item icon="cart" text="购买服务" :to="{name:'home'}" class="cart-grid-item"/>
      <van-grid-item icon="notes-o" text="查阅教程" :to="{name:'tutorial',params:{os:'auto'}}"/>
      <van-grid-item icon="upgrade" text="配置VPN" :to="{name:'vpn'}"/>
      <van-grid-item icon="certificate" text="连接成功" :to="{name:'app'}"/>
    </van-grid>

    <van-config-provider :theme-vars="themeVars">
      <van-card
        v-for="category in categories"
        :key="category.id"
        :thumb="category.thumbnail_file.url"
        @click="router.push({name:'category',params:{categoryId:category.id}})"
        :centered="true"
      >
        <template #title>
          <span class="category-title">
            {{ category.name }}
          </span>
        </template>
        <template #desc>
          <span class="category-description">
            {{ category.description }}
          </span>
        </template>
        <template #tags>
          <div class="category-tag-list">
            <van-tag
              v-for="tag in category.tags"
              :key="tag.id"
              plain
              round
              size="medium"
              type="primary"
              class="category-tag"
            >
              {{tag.name}}
            </van-tag>
          </div>
        </template>
        <template #footer>
          <van-button
            round
            type="primary"
            icon="shopping-cart-o"
            size="small"
            :to="'category/' + category.id">
            立即购买
          </van-button>
        </template>
      </van-card>
    </van-config-provider>
  </van-space>
</template>
<style scoped>
.category-tag-list {
  margin-top: 10px;
  margin-left: 15px;
  margin-bottom: 10px;
}

.category-tag {
  margin-right: 3px;
}

.category-description {
  display: block;
  margin-top: 10px;
  color: var(--van-text-color-2);
  margin-left: 15px;
}

.category-title {
  margin-top: 10px;
  font-weight: bold;
  margin-left: 15px;
}

</style>
