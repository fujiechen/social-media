import {encodeJSONToBase64UriSafe} from "@/helpers/base64uri";

export const redirectToBank = (accessToken, text) => {
    const r = {
        text: text,
        url: window.location.href,
    };
    window.location.href = process.env.VUE_APP_BANK_URL + '/asset?t='
        + accessToken + '&r=' + encodeJSONToBase64UriSafe(r);
};
