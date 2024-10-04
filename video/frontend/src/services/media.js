import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/medias`;

export const fetchMediaList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const fetchMediaSuggestList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/recommendation${paramsString && '?'+paramsString}`, null);
};

export const fetchMediaById = async (mediaId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${mediaId}`, null);
  return response.data;
};

export const fetchSimilarListByMediaId = async (mediaId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${mediaId}/similar`, null);
  return response.data;
};

export const toggleMediaLike = async (mediaId) => {
  const response = await fetchWrapper.post(`${baseUrl}/likes/${mediaId}`, null);
  return response.like;
};

export const toggleMediaFavorite = async (mediaId) => {
  const response = await fetchWrapper.post(`${baseUrl}/favorites/${mediaId}`, null);
  return response.favorite;
};

export const fetchFavoriteList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/favorites${paramsString && '?'+paramsString}`, null);
};

export const fetchHistoryList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/histories${paramsString && '?'+paramsString}`, null);
};

export const fetchLikeList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/likes${paramsString && '?'+paramsString}`, null);
};

export const fetchCommentList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/comments/medias${paramsString && '?'+paramsString}`, null);
};
