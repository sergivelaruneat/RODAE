// src/services/http.js
import axios from "axios";

/**
 * Resuelve la baseURL a partir de las envs y fuerza https si la app corre bajo https.
 */
function resolveBaseURL() {
  const raw =
    import.meta.env.VITE_API_URL ||
    import.meta.env.VITE_API_BASE_URL ||
    "/api"; // fallback típico a un proxy local

  // Si es relativa (empieza por /), pegamos el origin actual (útil en dev con proxy)
  let url = raw.startsWith("/") ? `${window.location.origin}${raw}` : raw;

  // Si la página se sirve por HTTPS y la API apunta a http://, lo elevamos a https://
  if (window.location.protocol === "https:" && url.startsWith("http://")) {
    const upgraded = url.replace(/^http:\/\//i, "https://");
    if (import.meta.env.DEV) {
      console.warn("[http] Elevando API a HTTPS:", upgraded);
    }
    url = upgraded;
  }
  return url;
}

const http = axios.create({
  baseURL: resolveBaseURL(),
  // Si vas a usar cookies/CSRF, cambia a true. Con JWT típicamente false.
  withCredentials: false,
  timeout: 15000,
});

// ---- Helpers para token ----
export function setToken(token) {
  if (token) {
    localStorage.setItem("token", token);
    http.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    clearToken();
  }
}

export function clearToken() {
  localStorage.removeItem("token");
  delete http.defaults.headers.common.Authorization;
}

export function setBaseURL(url) {
  http.defaults.baseURL = url;
}

// Cargar token si ya existe (p.ej. tras recargar la página)
(() => {
  const t = localStorage.getItem("token");
  if (t) http.defaults.headers.common.Authorization = `Bearer ${t}`;
})();

// ---- Interceptor de request ----
http.interceptors.request.use((config) => {
  // Aceptamos JSON por defecto
  config.headers = config.headers || {};
  config.headers.Accept = "application/json";

  // Para JSON normal, fija Content-Type; para FormData NO lo fijes (deja que Axios ponga boundary)
  const isFormData =
    typeof FormData !== "undefined" && config.data instanceof FormData;
  if (!isFormData && !config.headers["Content-Type"]) {
    config.headers["Content-Type"] = "application/json";
  }

  return config;
});

// ---- Interceptor de respuesta ----
http.interceptors.response.use(
  (res) => res,
  async (error) => {
    const status = error?.response?.status;

    if (status === 401) {
      // Token inválido/expirado → limpiar sesión
      clearToken();

      // Opcional: redirigir automáticamente al login
      if (window.location.pathname !== "/login") {
        window.location.assign("/login");
     }
    }

    return Promise.reject(error);
  }
);

export default http;
