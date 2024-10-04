<script setup>
import {
  computed, onMounted, ref, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { convertProductToListItem } from '@/helpers/productList';
import PageTitle from '@/components/PageTitle/PageTitle.vue';
import ProductListItem from '@/components/ProductListItem/ProductListItem.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
const user = computed(() => store.state.auth.user);
const productListsGroupByCategories = computed(() => store.state.product.productListsGroupByCategories);

const loading = ref(true);
const categories = ref([]); // {id, name}
const listItemsByCategories = ref([]);
const selectTabId = ref('');

onMounted(() => {
  store.dispatch('product/listProductsForMarketPage')
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});

watch(productListsGroupByCategories, (newValue) => {
  const newListItemsByCategories = [];

  categories.value = newValue.map((category, index) => {
    if (index === 0) {
      selectTabId.value = category.id;
    }

    const { products } = category;
    newListItemsByCategories[index] = products.map((product) => convertProductToListItem(product));

    return {
      id: category.id,
      name: category.name,
    };
  });

  listItemsByCategories.value = newListItemsByCategories;
});

const handleTabClick = (id) => {
  selectTabId.value = id;
};
</script>
<template>
  <PageTitle
    :highlight-sign-in="!loggedIn"
    :highlight="loggedIn ? t('hello') + user.nickname : ''"
    :name="t('market.name')"
  />
  <div class="card card-style">
    <div class="content">
      <div
        v-if="loading"
        class="content mt-4 text-center"
      >
        <span
          class="spinner-border spinner-border-sm"
        ></span>
      </div>
      <div v-else>
        <div class="tabs tabs-pill" id="tab-market-tab">
          <!-- Tabs -->
          <div class="tab-controls rounded-m p-1 overflow-visible">
            <a
              v-for="(category) in categories"
              v-bind:key="category.id"
              class="font-13 rounded-m shadow-bg shadow-bg-s"
              data-bs-toggle="collapse"
              :href="`#market-${category.id}-tab`"
              :aria-expanded="category.id === selectTabId"
              @click="handleTabClick(category.id)"
            >
              {{ category.name }}
            </a>
          </div>
          <div class="mt-3"></div>
          <!-- Tab Groups -->
          <div
            v-for="(listItems, index) in listItemsByCategories"
            v-bind:key="categories[index].id"
            :class="selectTabId===categories[index].id?'show ':''+' collapse'"
            :id="`market-${categories[index].id}-tab`"
            data-bs-parent="#tab-market-tab"
          >
            <ProductListItem
              :list-items="listItems"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
