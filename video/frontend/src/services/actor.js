import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/medias/actors`;

export const getActorList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const getActor = async (actorId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${actorId}`, null);
  return response.data;
};

