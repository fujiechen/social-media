import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/user/subscriptions`;

export const useSubscriptionUserStore = defineStore({
  id: 'subscriptionUser',
  state: () => ({
    fanUsers: [],
    subscribedUsers: [],
    friends: [],
    subscribed: false,
    medias: [],
  }),
  actions: {
    /**
     * 获取用户关注列表
     * @returns {Promise<void>}
     */
    async fetchSubscribedUsers() {
      await fetchWrapper.get(baseUrl, null)
        .then(response => {
        this.subscribedUsers = response.data;
      })
    },

    /**
     * 获取用户粉丝列表
     * @returns {Promise<void>}
     */
    async fetchFanUsers() {
      await fetchWrapper.get(`${baseUrl}/subscribers`, null)
        .then(response => {
          this.fanUser = response.data;
        })
    },

    async fetchFriends() {
      await fetchWrapper.get(`${baseUrl}/friends`, null)
        .then(response => {
          this.friends = response.data;
        })
    },

    async fetchMedias() {
      await fetchWrapper.get(`${baseUrl}/medias`, null)
        .then(response => {
          this.medias = response.data;
        })
    },

    async subscribe(publisherUserId) {
      await fetchWrapper.post(`${baseUrl}/${publisherUserId}`, null)
        .then(response => {
          this.subscribed = response.data.success;
        })
    },

    async removeSubscription(publisherUserId) {
      await fetchWrapper.delete(`${baseUrl}/${publisherUserId}`, null)
        .then(response => {
          this.subscribed = !response.data.success;
        })
    },
  }
});
