<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const isForCreate = computed(() => store.state.address.isCreated);
const popupOpenTrigger = computed(() => store.state.address.popupOpenTrigger);
const editAddress = computed(() => store.getters['address/editAddress']);

const loading = ref(false);
const message = ref('');

const name = ref('');
const address = ref('');
const phone = ref('');
const country = ref('');
const province = ref('');
const city = ref('');
const zip = ref('');
const comment = ref('');

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-address');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loading.value = false;
  message.value = '';

  if (!isForCreate.value && editAddress.value) {
    name.value = editAddress.value.name;
    address.value = editAddress.value.address;
    phone.value = editAddress.value.phone;
    country.value = editAddress.value.country;
    province.value = editAddress.value.province;
    city.value = editAddress.value.city;
    zip.value = editAddress.value.zip;
    comment.value = editAddress.value.comment;
  } else {
    name.value = '';
    address.value = '';
    phone.value = '';
    country.value = '';
    province.value = '';
    city.value = '';
    zip.value = '';
    comment.value = '';
  }

  adjustOffCanvasTop();
});

const handleCreate = () => {
  loading.value = true;
  message.value = '';

  if (name.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_name');
    loading.value = false;
  } else if (address.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_address');
    loading.value = false;
  } else if (phone.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_phone');
    loading.value = false;
  } else if (country.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_country');
    loading.value = false;
  } else if (province.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_province');
    loading.value = false;
  } else if (city.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_city');
    loading.value = false;
  } else if (zip.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_zip');
    loading.value = false;
  } else {
    store.dispatch('address/create', {
      name: name.value,
      address: address.value,
      phone: phone.value,
      country: country.value,
      province: province.value,
      city: city.value,
      zip: zip.value,
      comment: comment.value,
    })
      .then(() => {
        name.value = '';
        address.value = '';
        phone.value = '';
        country.value = '';
        province.value = '';
        city.value = '';
        zip.value = '';
        comment.value = '';
        loading.value = false;
        document.getElementById('close-address-menu').click();
      })
      .catch((error) => {
        if (error?.response?.data?.errors) {
          try {
            const { errors } = error.response.data;
            [message.value] = errors[Object.keys(errors)[0]];
          } catch {
            message.value = t('account.address_manage.errors.general_create');
          }
        } else {
          message.value = t('account.address_manage.errors.general_create');
        }
        loading.value = false;
      });
  }
};

const handleEdit = () => {
  loading.value = true;
  message.value = '';

  if (name.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_name');
    loading.value = false;
  } else if (address.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_address');
    loading.value = false;
  } else if (phone.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_phone');
    loading.value = false;
  } else if (country.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_country');
    loading.value = false;
  } else if (province.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_province');
    loading.value = false;
  } else if (city.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_city');
    loading.value = false;
  } else if (zip.value.length === 0) {
    message.value = t('account.address_manage.errors.missing_zip');
    loading.value = false;
  } else {
    store.dispatch('address/edit', {
      address: {
        name: name.value,
        address: address.value,
        phone: phone.value,
        country: country.value,
        province: province.value,
        city: city.value,
        zip: zip.value,
        comment: comment.value,
      },
    })
      .then(() => {
        name.value = '';
        address.value = '';
        phone.value = '';
        country.value = '';
        province.value = '';
        city.value = '';
        zip.value = '';
        comment.value = '';
        loading.value = false;
        document.getElementById('close-address-menu').click();
      })
      .catch((error) => {
        if (error?.response?.data?.errors) {
          try {
            const { errors } = error.response.data;
            [message.value] = errors[Object.keys(errors)[0]];
          } catch {
            message.value = t('account.address_manage.errors.general_edit');
          }
        } else {
          message.value = t('account.address_manage.errors.general_edit');
        }
        loading.value = false;
      });
  }
};
</script>
<template>
  <div id="menu-address" class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <div class="menu-size" style="min-height:600px;">
      <div class="d-flex mx-3 mt-3 py-1">
        <div class="align-self-center">
          <h1 v-if="isForCreate" class="mb-0">
            {{ t('account.address_manage.create_new_address') }}
          </h1>
          <h1 v-else class="mb-0">
            {{ t('account.address_manage.edit_address') }}
          </h1>
        </div>
        <div class="align-self-center ms-auto">
          <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
            <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
          </a>
        </div>
      </div>
      <div class="divider divider-margins mt-3"></div>
      <div class="content mt-0">
        <div
          v-if="message"
          class="alert bg-fade-red color-red-dark alert-dismissible rounded-s fade show"
          role="alert"
        >
          <i class="bi bi-exclamation-triangle pe-2"></i>
          {{ message }}
          <button
            type="button"
            class="btn-close opacity-20 font-11 pt-3 mt-1"
            data-bs-dismiss="alert"
            aria-label="Close"></button>
        </div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="name"
            type="text"
            id="address_name"
            class="form-control rounded-xs"
            :placeholder="t('account.address_manage.name_label')"
          />
          <label
            for="address_name"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.name_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="phone"
            id="address_phone"
            type="text"
            class="form-control rounded-xs"
            :placeholder="t('account.address_manage.phone_label')"
          />
          <label
            for="address_phone"
            class="form-label-always-active color-highlight font-11">
            {{ t('account.address_manage.phone_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="address"
            type="text"
            class="form-control rounded-xs"
            id="address_address"
            :placeholder="t('account.address_manage.address_label')"
          />
          <label
            for="address_address"
            class="form-label-always-active color-highlight font-11">
            {{ t('account.address_manage.address_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="city"
            type="text"
            class="form-control rounded-xs"
            id="address_city"
            :placeholder="t('account.address_manage.city_label')"
          />
          <label
            for="address_city"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.city_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="province"
            type="text"
            class="form-control rounded-xs"
            id="address_province"
            :placeholder="t('account.address_manage.province_label')"
          />
          <label
            for="address_province"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.province_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="country"
            id="address_country"
            type="text"
            class="form-control rounded-xs"
            :placeholder="t('account.address_manage.country_label')"
          />
          <label
            for="address_country"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.country_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="zip"
            type="text"
            class="form-control rounded-xs"
            id="address_zip"
            :placeholder="t('account.address_manage.zip_label')"
          />
          <label
            for="address_zip"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.zip_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="comment"
            type="text"
            class="form-control rounded-xs"
            id="address_comment"
            :placeholder="t('account.address_manage.comment')"
          />
          <label
            for="address_comment"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.address_manage.comment') }}
          </label>
          <span class="font-10">{{ t('account.optional') }}</span>
        </div>
      </div>
      <a
        v-if="loading"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else-if="isForCreate"
        @click="handleCreate"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('account.address_manage.create') }}
      </a>
      <a
        v-else
        @click="handleEdit"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('account.address_manage.edit') }}
      </a>
      <a
        id="close-address-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </div>
  </div>
</template>
