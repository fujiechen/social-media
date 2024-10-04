import Settings from '@/services/Settings';
import SettingConstants from '@/constants/Setting';

const initialState = {
  loadingDepositDescription: false,
  textBankAccount: [],
  depositHelp: null,
  loadingWithdrawDescription: false,
  withdrawHelp: null,
  loadingExchangeDescription: false,
  exchangeHelp: null,
  loadingTransferDescription: false,
  transferHelp: null,
  loadingPurchaseDescription: false,
  purchaseHelp: null,
  loadingBannerInfo: false,
  bannerInfo: null,
  loadingHelpData: false,
  helpData: null,
  loadingTacData: false,
  tacData: null,
  loadingAssetCardImage: false,
  assetCardImage: null,
  loadingDepositAmountOptions: false,
  depositAmountOptions: [],
  loadingCustomerServiceImg: false,
  customerServiceImg: null,
};

const getters = {};

const actions = {
  getDepositDescription({ commit, state }, { locale }) {
    if (state.textBankAccount.length === 0) {
      commit('startLoadingDepositDescription');
      Settings.getSettings({
        names: `${SettingConstants.NAME.JSON_BANK_ACCOUNT},${SettingConstants.NAME.DEPOSIT_HELP}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const jsonBankAccount = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.JSON_BANK_ACCOUNT,
          );
          const bankAccounts = JSON.parse(jsonBankAccount?.value);
          commit('updateJsonBankAccount', bankAccounts);

          const depositHelper = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.DEPOSIT_HELP,
          );
          commit('updateDepositHelp', depositHelper);

          commit('finishLoadingDepositDescription');
        },
        () => {
          commit('finishLoadingDepositDescription');
        },
      );
    }
  },
  getWithdrawDescription({ commit, state }, { locale }) {
    if (state.withdrawHelp === null) {
      commit('startLoadingWithdrawDescription');
      Settings.getSettings({
        names: `${SettingConstants.NAME.WITHDRAW_HELP}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.WITHDRAW_HELP,
          );
          commit('updateWithdrawHelp', text);

          commit('finishLoadingWithdrawDescription');
        },
        () => {
          commit('finishLoadingWithdrawDescription');
        },
      );
    }
  },
  getExchangeDescription({ commit, state }, { locale }) {
    if (state.exchangeHelp === null) {
      commit('startLoadingExchangeDescription');
      Settings.getSettings({
        names: `${SettingConstants.NAME.EXCHANGE_HELP}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.EXCHANGE_HELP,
          );
          commit('updateExchangeHelp', text);

          commit('finishLoadingExchangeDescription');
        },
        () => {
          commit('finishLoadingExchangeDescription');
        },
      );
    }
  },
  getTransferDescription({ commit, state }, { locale }) {
    if (state.transferHelp === null) {
      commit('startLoadingTransferDescription');
      Settings.getSettings({
        names: `${SettingConstants.NAME.TRANSFER_HELP}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.TRANSFER_HELP,
          );
          commit('updateTransferHelp', text);

          commit('finishLoadingTransferDescription');
        },
        () => {
          commit('finishLoadingTransferDescription');
        },
      );
    }
  },
  getPurchaseDescription({ commit, state }, { locale }) {
    if (state.purchaseHelp === null) {
      commit('startLoadingPurchaseDescription');
      Settings.getSettings({
        names: `${SettingConstants.NAME.PURCHASE_HELP}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.PURCHASE_HELP,
          );
          commit('updatePurchaseHelp', text);

          commit('finishLoadingPurchaseDescription');
        },
        () => {
          commit('finishLoadingPurchaseDescription');
        },
      );
    }
  },
  getBannerInfo({ commit, state }, { locale }) {
    if (state.bannerInfo === null) {
      commit('startLoadingBannerInfo');
      Settings.getSettings({
        names: `${SettingConstants.NAME.BANNER_IMAGE_URL},${SettingConstants.NAME.BANNER_TITLE},${SettingConstants.NAME.BANNER_SLOGAN},${SettingConstants.NAME.ABOUT_US_URL}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const imageUrl = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.BANNER_IMAGE_URL,
          );
          const title = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.BANNER_TITLE,
          );
          const slogan = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.BANNER_SLOGAN,
          );
          const aboutUsUrl = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.ABOUT_US_URL,
          );
          commit('updateBannerInfo', {
            imageUrl: imageUrl?.value,
            title: title?.value,
            slogan: slogan?.value,
            aboutUsUrl: aboutUsUrl?.value,
          });

          commit('finishLoadingBannerInfo');
        },
        () => {
          commit('finishLoadingBannerInfo');
        },
      );
    }
  },
  getHelpData({ commit, state }, { locale }) {
    if (state.helpData === null) {
      commit('startLoadingHelpData');
      Settings.getSettings({
        names: `${SettingConstants.NAME.HELP_HTML}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.HELP_HTML,
          );
          commit('updateHelpData', text);

          commit('finishLoadingHelpData');
        },
        () => {
          commit('finishLoadingHelpData');
        },
      );
    }
  },
  getTacData({ commit, state }, { locale }) {
    if (state.tacData === null) {
      commit('startLoadingTacData');
      Settings.getSettings({
        names: `${SettingConstants.NAME.TAC_HTML}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.TAC_HTML,
          );
          commit('updateTacData', text);

          commit('finishLoadingTacData');
        },
        () => {
          commit('finishLoadingTacData');
        },
      );
    }
  },
  getAssetCardImage({ commit, state }, { locale }) {
    if (state.assetCardImage === null) {
      commit('startLoadingAssetCardImageData');
      Settings.getSettings({
        names: `${SettingConstants.NAME.ASSET_CARD_IMAGE_URL}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const urlText = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.ASSET_CARD_IMAGE_URL,
          );
          commit('updateAssetCardImageData', urlText);

          commit('finishLoadingAssetCardImageData');
        },
        () => {
          commit('finishLoadingAssetCardImageData');
        },
      );
    }
  },
  getDepositAmountOptions({ commit, state }, { locale }) {
    if (state.depositAmountOptions.length === 0) {
      commit('startLoadingDepositAmountOptions');
      Settings.getSettings({
        names: `${SettingConstants.NAME.DEPOSIT_AMOUNT_OPTIONS}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const text = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.DEPOSIT_AMOUNT_OPTIONS,
          );
          commit('updateDepositAmountOptions', text);

          commit('finishLoadingDepositAmountOptions');
        },
        () => {
          commit('finishLoadingDepositAmountOptions');
        },
      );
    }
  },
  getCustomerServiceImg({ commit, state }, { locale }) {
    if (state.customerServiceImg === null) {
      commit('startLoadingCustomerServiceImgData');
      Settings.getSettings({
        names: `${SettingConstants.NAME.URL_CUSTOMER_SERVICE}`,
        language: locale,
      }).then(
        (response) => {
          const settingsResponse = response.data;

          const urlText = settingsResponse.find(
            (setting) => setting.name === SettingConstants.NAME.URL_CUSTOMER_SERVICE,
          );
          commit('updateCustomerServiceImgData', urlText);

          commit('finishLoadingCustomerServiceImgData');
        },
        () => {
          commit('finishLoadingCustomerServiceImgData');
        },
      );
    }
  },
};

const mutations = {
  startLoadingDepositDescription(state) {
    state.loadingDepositDescription = true;
  },
  finishLoadingDepositDescription(state) {
    state.loadingDepositDescription = false;
  },
  updateJsonBankAccount(state, data) {
    state.textBankAccount = data;
  },
  updateDepositHelp(state, data) {
    state.depositHelp = data?.value;
  },
  startLoadingWithdrawDescription(state) {
    state.loadingWithdrawDescription = true;
  },
  finishLoadingWithdrawDescription(state) {
    state.loadingWithdrawDescription = false;
  },
  updateWithdrawHelp(state, data) {
    state.withdrawHelp = data?.value;
  },
  startLoadingExchangeDescription(state) {
    state.loadingExchangeDescription = true;
  },
  finishLoadingExchangeDescription(state) {
    state.loadingExchangeDescription = false;
  },
  updateExchangeHelp(state, data) {
    state.exchangeHelp = data?.value;
  },
  startLoadingTransferDescription(state) {
    state.loadingTransferDescription = true;
  },
  finishLoadingTransferDescription(state) {
    state.loadingTransferDescription = false;
  },
  updateTransferHelp(state, data) {
    state.transferHelp = data?.value;
  },
  startLoadingPurchaseDescription(state) {
    state.loadingPurchaseDescription = true;
  },
  finishLoadingPurchaseDescription(state) {
    state.loadingPurchaseDescription = false;
  },
  updatePurchaseHelp(state, data) {
    state.purchaseHelp = data?.value;
  },
  startLoadingBannerInfo(state) {
    state.loadingBannerInfo = true;
  },
  finishLoadingBannerInfo(state) {
    state.loadingBannerInfo = false;
  },
  updateBannerInfo(state, data) {
    state.bannerInfo = data;
  },
  startLoadingHelpData(state) {
    state.loadingHelpData = true;
  },
  finishLoadingHelpData(state) {
    state.loadingHelpData = false;
  },
  updateHelpData(state, data) {
    state.helpData = data?.value;
  },
  startLoadingTacData(state) {
    state.loadingTacData = true;
  },
  finishLoadingTacData(state) {
    state.loadingTacData = false;
  },
  updateTacData(state, data) {
    state.tacData = data?.value;
  },
  startLoadingAssetCardImageData(state) {
    state.loadingAssetCardImage = true;
  },
  finishLoadingAssetCardImageData(state) {
    state.loadingAssetCardImage = false;
  },
  updateAssetCardImageData(state, data) {
    state.assetCardImage = data?.value;
  },
  startLoadingDepositAmountOptions(state) {
    state.loadingDepositAmountOptions = true;
  },
  finishLoadingDepositAmountOptions(state) {
    state.loadingDepositAmountOptions = false;
  },
  updateDepositAmountOptions(state, data) {
    if (data?.value) {
      state.depositAmountOptions = JSON.parse(data.value);
    } else {
      state.depositAmountOptions = [];
    }
  },
  startLoadingCustomerServiceImgData(state) {
    state.loadingCustomerServiceImg = true;
  },
  finishLoadingCustomerServiceImgData(state) {
    state.loadingCustomerServiceImg = false;
  },
  updateCustomerServiceImgData(state, data) {
    state.customerServiceImg = data?.value;
  },
};

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations,
};
