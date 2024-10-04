import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/medias/actors`;

export const useActorStore = defineStore({
  id: 'actors',
  state: () => ({
    actors: [],
    actor: {
      avatar_file: {
        url: '',
      }
    },
  }),
  actions: {
    async fetchAll() {
      await fetchWrapper.get(baseUrl, null)
        .then(response => {
        this.actors = response.data;
      })
    },
    async fetchOne(actorId) {
      await fetchWrapper.get(`${baseUrl}/${actorId}`, null)
        .then(response => {
          this.actor = response.data;
        })
    },
    async search(actorName) {
      await fetchWrapper.get(`${baseUrl}?name=${actorName}`, null)
        .then(response => {
          this.actors = response.data;
        })
    },

  }
});
