import UserAddressService from '../../services/UserAddress';

// initial state
const initialState = {
  addresses: [],
  isCreated: true,
  popupOpenTrigger: 0,
  deleteOpenTrigger: 0,
  editId: 0,
  deleteId: 0,
};

// getters
const getters = {
  editAddress: (state) => state.addresses.find(({ id }) => id === state.editId),
  deleteAddress: (state) => state.addresses.find(({ id }) => id === state.deleteId),
};

// actions
const actions = {
  list({ commit }) {
    return UserAddressService
      .list()
      .then(
        (response) => {
          const addresses = response.data;
          if (addresses !== undefined) {
            commit('loadListSuccess', {
              addresses: response.data,
            });
            return Promise.resolve(addresses);
          }
          commit('loadListFailed');
          return Promise.reject(Error('Error getting address list'));
        },
        (error) => {
          commit('loadListFailed');
          return Promise.reject(error);
        },
      );
  },
  create({ commit }, address) {
    return UserAddressService
      .create(address)
      .then((response) => {
        const newAddress = response.data;
        if (newAddress) {
          commit('addNewAddressToList', newAddress);
          return Promise.resolve(newAddress);
        }
        return Promise.reject(Error('Error making new address'));
      });
  },
  edit({ commit, state }, { address }) {
    return UserAddressService
      .update(state.editId, address)
      .then((response) => {
        const updatedAddress = response.data;
        if (updatedAddress) {
          commit('updateAddressToList', { id: state.editId, updatedAddress });
          return Promise.resolve(updatedAddress);
        }
        return Promise.reject(Error('Error updating the address'));
      });
  },
  delete({ commit, state }) {
    return UserAddressService
      .delete(state.deleteId)
      .then(() => {
        commit('deleteAddressFromList', state.deleteId);
      });
  },
};

// mutations
const mutations = {
  loadListSuccess(state, { addresses }) {
    state.addresses = addresses;
  },
  loadListFailed(state) {
    state.addresses = [];
  },
  addNewAddressToList(state, address) {
    state.addresses.push(address);
  },
  updateAddressToList(state, { id, updatedAddress }) {
    const updatedIndex = state.addresses.findIndex(((address) => address.id === id));
    state.addresses[updatedIndex] = updatedAddress;
  },
  deleteAddressFromList(state, id) {
    state.addresses = state.addresses.filter(((address) => address.id !== id));
  },
  openCreatePopup(state) {
    state.isCreated = true;
    state.editId = 0;
    state.popupOpenTrigger += 1;
  },
  openEditPopup(state, { id }) {
    state.isCreated = false;
    state.editId = id;
    state.popupOpenTrigger += 1;
  },
  openDeletePopup(state, { id }) {
    state.deleteId = id;
    state.deleteOpenTrigger += 1;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
