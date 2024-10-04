import {defineStore} from "pinia";
import {fetchWrapper} from '@/helpers/fetch-wrapper';

const baseUrl = `${process.env.VUE_APP_API_URL}/medias`;

export const useMediaStore = defineStore(
  "media",
  {
    state: () => {
      return {
        medias: [],
        media: {
          children_medias: []
        },
      };
    },
    actions: {
      async clearData() {
        this.data = {
          medias: [],
          media: {},
        };
      },

      async getMediaList(mediaUserId, types) {
        const response = await fetchWrapper.get(baseUrl, {
          media_user_id: mediaUserId,
          types: types,
        });
        this.medias = response.data;
      },

      async fetchOne(mediaId) {
        await fetchWrapper.get(`${baseUrl}/${mediaId}`, null)
          .then(response => {
            this.media = response.data;
          });
      },

      async fetchSimilarMedias(mediaId) {
        await fetchWrapper.get(`${baseUrl}/${mediaId}/similar`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      //TODO update to suggestion
      async getMediaListOfSuggestion() {
        const response = await fetchWrapper.get(baseUrl, null);
        this.medias = response.data;
      },

      async getMediaListOfSearch(searchText, actorName, tagName, categoryName, nickName) {
        const response = await fetchWrapper.get(`${baseUrl}?media_search_text=${searchText}&actor_name=${actorName}&category_name=${categoryName}&tag_names=${tagName}&nickname=${nickName}`, null)
        this.medias = response.data;
      },

      async fetchMediaListByCategoryId(categoryId) {
        await fetchWrapper.get(`${baseUrl}?category_id=${categoryId}`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListByTagId(tagId) {
        await fetchWrapper.get(`${baseUrl}?tag_id=${tagId}`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListByActorId(actorId) {
        await fetchWrapper.get(`${baseUrl}?actor_id=${actorId}`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListByMediaUserId(mediaUserId) {
        await fetchWrapper.get(`${baseUrl}?media_user_id=${mediaUserId}`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListOfHistories() {
        await fetchWrapper.get(`${baseUrl}/histories`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListOfLikes() {
        await fetchWrapper.get(`${baseUrl}/likes`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListOfFavorites() {
        await fetchWrapper.get(`${baseUrl}/favorites`, null)
          .then(response => {
            this.medias = response.data;
          });
      },

      async fetchMediaListOfComments() {
        await fetchWrapper.get(`${baseUrl}/favorites`)
          .then(response => {
            this.medias = response.data;
          });
      },

    },
  });
