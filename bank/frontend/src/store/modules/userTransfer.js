import UserAccount from '@/services/UserAccount';
import UserOrder from '@/services/UserOrder';
import FriendList from '@/constants/FriendList';

const initialState = {
  popupOpenTrigger: 0,
  selectedFriendInfo: { name: '', email: '' },
  friendsList: [],
  friendListReloadTrigger: 0,
  accounts: [],
};

const getters = {};

const actions = {
  getAccounts({ commit }) {
    return UserAccount
      .list()
      .then(
        (response) => {
          const accounts = response.data;
          if (accounts && accounts.length > 0) {
            commit('loadAccountSuccess', { accounts });
            return Promise.resolve(accounts);
          }
          commit('loadAccountFailed');
          return Promise.reject(Error('Error getting the account for transferring'));
        },
        (error) => {
          commit('loadAccountFailed');
          return Promise.reject(error);
        },
      );
  },
  getFriendsList({ commit }) {
    return UserOrder
      .listFriendsForTransfer({
        limit: FriendList.FRIEND_LIST_LIMIT,
      })
      .then(
        (response) => {
          const friendsList = response.data;
          if (friendsList !== undefined) {
            commit('loadFriendListSuccess', {
              friendsList,
            });
            return Promise.resolve(friendsList);
          }
          commit('loadFriendListFailed');
          return Promise.reject(Error('Error getting transfer friend list'));
        },
        (error) => {
          commit('loadFriendListFailed');
          return Promise.reject(error);
        },
      );
  },
  transfer(_, {
    amount, fromUserAccountId, toUserEmail, toUserName, comment,
  }) {
    return UserOrder.transfer({
      amount,
      fromUserAccountId,
      toUserEmail,
      toUserName,
      comment,
    });
  },
};

const mutations = {
  loadAccountSuccess(state, { accounts }) {
    state.accounts = accounts;
  },
  loadAccountFailed(state) {
    state.accounts = [];
  },
  loadFriendListSuccess(state, { friendsList }) {
    state.friendsList = friendsList;
  },
  loadFriendListFailed(state) {
    state.friendsList = [];
  },
  openPopup(state, { name = '', email = '' }) {
    state.popupOpenTrigger += 1;
    state.selectedFriendInfo = { name, email };
  },
  refreshFriendListAfterTransfer(state) {
    state.friendListReloadTrigger += 1;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
