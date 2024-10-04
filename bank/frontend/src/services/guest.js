import BrowserConstants from '@/constants/Browser';

export const getGuestData = () => JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_GUEST_KEY));

export const upsertGuestData = (updateData) => {
  const guest = JSON.parse(localStorage.getItem(BrowserConstants.LOCAL_STORAGE_GUEST_KEY));
  if (guest) {
    const newGuest = {
      ...guest,
      ...updateData,
    };
    localStorage.setItem(BrowserConstants.LOCAL_STORAGE_GUEST_KEY, JSON.stringify(newGuest));
  } else {
    localStorage.setItem(BrowserConstants.LOCAL_STORAGE_GUEST_KEY, JSON.stringify(updateData));
  }
};
