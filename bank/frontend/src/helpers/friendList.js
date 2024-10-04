export const convertFriendToButton = (user, defaultName) => (user !== undefined ? user.nickname[0] : defaultName);

export const convertFriendToName = (user, defaultName) => (user !== undefined ? user.nickname : defaultName);
