<script setup>
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';
import { computed } from 'vue';
import LanguageConstants from '@/constants/Language';
import { updateGuestLocaleSetting } from '@/helpers/language';

const { locale } = useI18n({ useScope: 'global' });
const store = useStore();
const loggedIn = computed(() => store.state.auth.status.loggedIn);

const handleSwitch = (newLanguage) => {
  if (locale.value === newLanguage) {
    return;
  }

  if (loggedIn.value) {
    store.dispatch('userProfile/update', {
      language: newLanguage,
    }).then(() => {
      window.location.reload();
    });
  } else {
    updateGuestLocaleSetting(newLanguage);
    window.location.reload();
  }
};
</script>
<template>
  <a
    data-bs-toggle="dropdown"
    class="icon bg-white shadow-bg shadow-bg-s rounded-m">
    <i class="bi bi-globe"></i>
  </a>
  <div class="dropdown-menu">
    <div class="card card-style shadow-m mt-1 me-1">
      <div class="list-group list-custom list-group-s list-group-flush rounded-xs px-3 py-1">
        <div
          v-for="language in LanguageConstants.fe"
          :key="language"
          class="list-group-item"
          @click="handleSwitch(language)"
        >
          <strong class="font-13">{{ LanguageConstants.feName[language] }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>
