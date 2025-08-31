import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL;

// ---- AUTH ----
export const register = (payload) =>
  axios.post(`${API_URL}/register`, payload);

export const login = async (email, password) => {
  const { data } = await axios.post(`${API_URL}/login`, { email, password });
  // Guardamos token
  localStorage.setItem("token", data.token);
  axios.defaults.headers.common["Authorization"] = `Bearer ${data.token}`;
  return data;
};

export const getMe = () => axios.get(`${API_URL}/me`);

export const logout = async () => {
  await axios.post(`${API_URL}/logout`);
  localStorage.removeItem("token");
  delete axios.defaults.headers.common["Authorization"];
};

export const resendVerification = () =>
  axios.post(`${API_URL}/email/resend`);
