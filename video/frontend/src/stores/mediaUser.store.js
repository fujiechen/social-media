import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/medias/users`;

export const useMediaUserStore = defineStore({
  id: 'mediaUsers',
  state: () => ({
    mediaUsers: [],
    mediaUser: {
      avatar_file: {
        url: '',
      }
    },
    mediaUserStatistics: {
      publisher: {
        is_followed: false,
        subscriptions_count: 0,
        subscribers_count: 0,
      },
      follows: {
        registration_redirect: false,
        product_redirect: false,
      },
      medias: {
        medias_count: 0,
        series_count: 0,
        videos_count: 0,
        albums_count: 0,
      }
    },
  }),
  actions: {
    async fetchAll() {
      await fetchWrapper.get(baseUrl, null)
        .then(response => {
        this.mediaUsers = response.data;
      })
    },

    async fetchOne(mediaUserId) {
      await fetchWrapper.get(`${baseUrl}/${mediaUserId}`, null)
        .then(response => {
          this.mediaUser = response.data;
        })
    },

    async fetchStatistics(mediaUserId) {
      await fetchWrapper.get(`${apiBaseUrl}/user/statistics?user_id=${mediaUserId}`, null)
        .then(response => {
          this.mediaUserStatistics = response.data;
        })
    },

    async search(mediaUserNickname) {
      await fetchWrapper.get(`${baseUrl}?name=${mediaUserNickname}`, null)
        .then(response => {
          this.mediaUsers = response.data;
        })
    },
  }
});
