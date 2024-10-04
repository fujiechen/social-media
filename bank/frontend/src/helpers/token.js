import Auth from '@/services/Auth';

export const setupToken = async (t) => Auth.overrideUser(t);

export const sample = () => null;
