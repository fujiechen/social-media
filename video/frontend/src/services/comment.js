import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrlPrefix = `${process.env.VUE_APP_API_URL}/medias/`;
const baseUrlSuffix = `/comments`;

export const fetchCommentListByMediaId = async (mediaId, params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrlPrefix}${mediaId}${baseUrlSuffix}${paramsString && '?'+paramsString}`, null);
};

export const createCommentByMediaId = async (mediaId, body) => {
  return await fetchWrapper.post(`${baseUrlPrefix}${mediaId}${baseUrlSuffix}`, body);
};
