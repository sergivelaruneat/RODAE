import http from './http'

// helper para “desenvolver” respuestas con o sin .data.data
const unwrap = (r) => (r?.data?.data ?? r?.data ?? r)

// Listado general (público)
export const getAllRoutines = (params = {}) =>
  http.get('/routines', { params }).then(r => unwrap(r)) // si paginas devuelve {data,meta}, usa unwrap(r).data

// Detalle (público)
export const getRoutine = (id) =>
  http.get(`/routines/${id}`).then(r => unwrap(r))

// Mis rutinas creadas (trainer)
export const getMyCreatedRoutines = (params = {}) =>
  http.get('/me/routines/created', { params }).then(r => unwrap(r))

// Mis rutinas seguidas
export const getMyRoutines = (params = {}) =>
  http.get('/me/routines', { params }).then(r => unwrap(r))

// Crear / actualizar / borrar
export const createRoutine = (payload) =>
  http.post('/routines', payload).then(r => unwrap(r))

export const updateRoutine = (id, payload) =>
  http.put(`/routines/${id}`, payload).then(r => unwrap(r))

export const deleteRoutine = (id) =>
  http.delete(`/routines/${id}`) // 204

// Seguir / dejar de seguir
export const followRoutine = (id) =>
  http.post(`/routines/${id}/follow`) // 204

export const unfollowRoutine = (id) =>
  http.delete(`/routines/${id}/follow`) // 204

// Valorar / quitar valoración
export const rateRoutine = (id, rating) =>
  http.post(`/routines/${id}/rate`, { rating }).then(r => unwrap(r))

export const unrateRoutine = (id) =>
  http.delete(`/routines/${id}/rate`).then(r => unwrap(r))

// Asignar / desasignar
export const assignRoutine = (routineId, userId) =>
  http.post(`/routines/${routineId}/assign`, { user_id: userId }).then(r => unwrap(r)) // 204 → '' (ok)

export const unassignRoutine = (userId, routineId) =>
  http.delete(`/users/${userId}/routines/${routineId}`) // 204

// Perfil público: seguidas / creadas por un usuario
export const getUserFollowedRoutines = (userId, params = {}) =>
  http.get(`/users/${userId}/routines/followed`, { params }).then(r => unwrap(r))

export const getUserCreatedRoutines = (userId, params = {}) =>
  http.get(`/users/${userId}/routines/created`, { params }).then(r => unwrap(r))

// Enum deportes
export const getSports = () =>
  http.get('/meta/sport').then(r => unwrap(r))
