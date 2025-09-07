import http from './http'

// Listado general (público)
export const getAllRoutines = (params = {}) =>
  http.get('/routines', { params }).then(r => r.data.data) // usa r.data si quieres meta

// Detalle (público)
export const getRoutine = (id) =>
  http.get(`/routines/${id}`).then(r => (r.data?.data ?? r.data))

// Mis rutinas creadas (trainer)
export const getMyCreatedRoutines = (params = {}) =>
  http.get('/me/routines/created', { params }).then(r => r.data.data)

// Mis rutinas seguidas
export const getMyRoutines = (params = {}) =>
  http.get('/me/routines', { params }).then(r => r.data.data)

// Crear / actualizar / borrar
export const createRoutine = (payload) =>
  http.post('/routines', payload).then(r => r.data)

export const updateRoutine = (id, payload) =>
  http.put(`/routines/${id}`, payload).then(r => r.data)

export const deleteRoutine = (id) =>
  http.delete(`/routines/${id}`)

// Seguir / dejar de seguir
export const followRoutine = (id) =>
  http.post(`/routines/${id}/follow`)

export const unfollowRoutine = (id) =>
  http.delete(`/routines/${id}/follow`)

// Valorar / quitar valoración
export const rateRoutine = (id, rating) =>
  http.post(`/routines/${id}/rate`, { rating }).then(r => r.data)

export const unrateRoutine = (id) =>
  http.delete(`/routines/${id}/rate`).then(r => r.data)

// Asignar / desasignar (trainer propietario → atleta)
export const assignRoutine = (routineId, userId) =>
  http.post(`/routines/${routineId}/assign`, { user_id: userId }).then(r => r.data)

export const unassignRoutine = (userId, routineId) =>
  http.delete(`/users/${userId}/routines/${routineId}`)

// Perfil público: seguidas / creadas por un usuario
export const getUserFollowedRoutines = (userId, params = {}) =>
  http.get(`/users/${userId}/routines/followed`, { params }).then(r => r.data.data)

export const getUserCreatedRoutines = (userId, params = {}) =>
  http.get(`/users/${userId}/routines/created`, { params }).then(r => r.data.data)

// Enum deportes
export const getSports = () =>
  http.get('/meta/sport').then(r => r.data)
