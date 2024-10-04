<script setup>
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t } = useI18n({ useScope: 'global' });
const router = useRouter();

const logoPath = `${process.env.VUE_APP_SUB_PATH}logo.png`;
</script>

<template>
  <div class="d-flex align-items-center min-vh-100">
    <div class="container">
      <div class="content text-center">
        <img
          :src="logoPath"
          alt="logo"
          @click="router.push({name:'home'})"
          style="width: 150px"
          class="mb-5"
        />
        <h1 class="mb-4">{{ t('payment_callback.title') }}</h1>
        <p class="font-15 mb-0">{{ t('payment_callback.line1') }}</p>
        <p class="font-15">{{ t('payment_callback.line2') }}</p>

        <div class="row">
          <div class="mb-4 pb-1">
            <router-link
              class="btn-full btn border-blue-dark color-blue-dark"
              :to="{name:'home'}"
            >
              {{ t('payment_callback.back_home_button') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
