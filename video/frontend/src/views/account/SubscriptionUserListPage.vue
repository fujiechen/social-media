<script setup>
import { onMounted, watch, ref } from 'vue';
import { useRoute, useRouter } from "vue-router";
import { showConfirmDialog, showFailToast, showToast } from "vant";
import UserList from "@/components/UserList";
import { fetchFriends, fetchSubscribers, fetchUserSubscriptions } from "@/services/userSubscription";
import { unsubscribeUserByUserId } from "@/services/follow";
import { useAuthStore } from "@/stores/auth.store";

const route = useRoute();
const router = useRouter();
const {user} = useAuthStore();

const active = ref('');

const subscription = ref({
  list: [],
  params: {
    page: 1,
    per_page: 10,
  },
  loading: true,
  finished: false,
});

const subscriber = ref({
  list: [],
  params: {
    page: 1,
    per_page: 10,
  },
  loading: true,
  finished: false,
});

const friends = ref({
  list: [],
  params: {
    page: 1,
    per_page: 10,
  },
  loading: true,
  finished: false,
});

const loadSubscription = async () => {
  subscription.value.loading = true;
  try {
    const response = await fetchUserSubscriptions(subscription.value.params);
    subscription.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    subscription.value.list = [
      ...subscription.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取关注列表，请稍后重试！');
    subscription.value.finished = true;
  }
  subscription.value.loading = false;
};

const loadSubscriber = async () => {
  subscriber.value.loading = true;
  try {
    const response = await fetchSubscribers(subscriber.value.params);
    subscriber.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    subscriber.value.list = [
      ...subscriber.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取粉丝列表，请稍后重试！');
    subscriber.value.finished = true;
  }
  subscriber.value.loading = false;
};

const loadFriends = async () => {
  friends.value.loading = true;
  try {
    const response = await fetchFriends(friends.value.params);
    friends.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    friends.value.list = [
      ...friends.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取朋友列表，请稍后重试！');
    friends.value.finished = true;
  }
  friends.value.loading = false;
};

const updateContent = async () => {
  const query = { ...route.query };
  delete query.type;
  await router.replace({ query })

  if (active.value === 'subscriptions' && !subscription.value.finished) {
    await loadSubscription();
  } else if (active.value === 'subscribers' && !subscriber.value.finished) {
    await loadSubscriber();
  } else if (active.value === 'friends' && !friends.value.finished) {
    await loadFriends();
  }
};

onMounted(async () => {
  active.value = route.query?.type || 'subscriptions';
});

watch(async () => active.value, updateContent);

const handleUnfollow = async (listName, userId) => {
  if (!user?.access_token) {
    localStorage.setItem('video-ref', window.location.href);
    await router.push({ name: "login" });
    return;
  }

  showConfirmDialog({
    title: '请确认取消关注',
  })
    .then(async () => {
      await unsubscribeUserByUserId(userId);
      if (listName === 'subscriptions') {
        subscription.value.list = subscription.value.list.map(mediaUser => {
          if (mediaUser.id ===  userId) {
            return {
              ...mediaUser,
              publisher: {
                ...mediaUser.publisher,
                is_followed: false,
              },
            };
          }
          return mediaUser;
        });
      } else if (listName === 'subscribers') {
        subscriber.value.list = subscriber.value.list.map(mediaUser => {
          if (mediaUser.id ===  userId) {
            return {
              ...mediaUser,
              publisher: {
                ...mediaUser.publisher,
                is_followed: false,
              },
            };
          }
          return mediaUser;
        });
      } else if (listName === 'friends') {
        friends.value.list = friends.value.list.map(mediaUser => {
          if (mediaUser.id ===  userId) {
            return {
              ...mediaUser,
              publisher: {
                ...mediaUser.publisher,
                is_followed: false,
              },
            };
          }
          return mediaUser;
        });
      }
      showToast('已取消关注');
    })
    .catch(() => {});
};

</script>

<template>
  <van-tabs v-model:active="active" class="sub-list-page-tabs">
    <van-tab title="关注" name="subscriptions">
      <UserList
        :media-users="subscription.list"
        :loading="subscription.loading"
        :has-more-pages="!subscription.finished"
        :load-next-page="loadSubscription"
        :unfollow-user="(userId) => handleUnfollow('subscriptions', userId)"
      />
    </van-tab>
    <van-tab title="粉丝" name="subscribers">
      <UserList
        :media-users="subscriber.list"
        :loading="subscriber.loading"
        :has-more-pages="!subscriber.finished"
        :load-next-page="loadSubscriber"
        :unfollow-user="(userId) => handleUnfollow('subscribers', userId)"
      />
    </van-tab>
    <van-tab title="互相关注" name="friends">
      <UserList
        :media-users="friends.list"
        :loading="friends.loading"
        :has-more-pages="!friends.finished"
        :load-next-page="loadFriends"
        :unfollow-user="(userId) => handleUnfollow('friends', userId)"
      />
    </van-tab>
  </van-tabs>
</template>
<style>
.sub-list-page-tabs .van-tabs__wrap {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
