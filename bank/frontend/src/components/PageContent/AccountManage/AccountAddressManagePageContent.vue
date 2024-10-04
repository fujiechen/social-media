<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '../../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const addressList = computed(() => store.state.address.addresses);

const loading = ref(true);

onMounted(() => {
  store.dispatch('address/list')
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});

const handleCreate = () => {
  store.commit('address/openCreatePopup');
};
const handleEdit = (id) => {
  store.commit('address/openEditPopup', { id });
};
const handleDelete = (id) => {
  store.commit('address/openDeletePopup', { id });
};
</script>
<template>
  <PageTitle
    :name="t('account.address_manage.title')"
    :back-link="{name:'account'}"
  />
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('account.address_manage.subtitle') }}</h3>
      </div>
    </div>
  </div>
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
      <div
        v-else
        class="content mt-2"
      >
        <div
          class="list-group list-custom list-group-m list-group-flush rounded-xs overflow-visible">
          <template v-if="addressList.length === 0">
            <div class="text-center">
              <span>{{ t('account.address_manage.no_address') }}</span>
            </div>
          </template>
          <template v-else>
            <a
              v-for="address in addressList"
              v-bind:key="address.id"
              class="list-group-item"
            >
              <div
                @click="handleEdit(address.id)"
                data-bs-toggle="offcanvas"
                data-bs-target="#menu-address"
              >
                <p class="mb-0">{{ address.name }}</p>
                <span>{{ address.phone }}</span>
                <p class="mb-0">{{ address.address }}</p>
                <p v-if="address.comment">{{ address.comment }}</p>
              </div>
              <i
                @click="handleDelete(address.id)"
                class="bi bi-trash font-15 color-red-dark"
                data-bs-toggle="offcanvas"
                data-bs-target="#menu-delete-address"
              ></i>
            </a>
          </template>
        </div>
        <div
          @click="handleCreate"
          data-bs-toggle="offcanvas"
          data-bs-target="#menu-address"
          class="btn btn-full gradient-highlight shadow-bg shadow-bg-s mt-4"
        >
          {{ t('account.address_manage.create_new_address') }}
        </div>
      </div>
    </div>
  </div>
</template>
