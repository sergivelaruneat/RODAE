// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', redirect: '/login' },

  // Públicas
  { path: '/login',          name: 'Login',          component: () => import('@/views/LoginView.vue') },
  { path: '/register',       name: 'Register',       component: () => import('@/views/RegisterView.vue') },
  { path: '/recoverpassword',name: 'RecoverPassword',component: () => import('@/views/RecoverPasswordView.vue') },
  { path: '/resetpassword',  name: 'ResetPassword',  component: () => import('@/views/ResetPasswordView.vue') },

  // Privadas
  { path: '/publications',   name: 'Publications',   component: () => import('@/views/PublicationView.vue'),  meta:{ requiresAuth:true }},
  { path: '/profile',        name: 'Profile',        component: () => import('@/views/ProfileView.vue'),      meta:{ requiresAuth:true }},
  { path: '/editprofile',    name: 'EditProfile',    component: () => import('@/views/EditProfileView.vue'),  meta:{ requiresAuth:true }},
  { path: '/profile/:user',  name: 'OtherProfile',   component: () => import('@/views/OtherProfileView.vue'), meta:{ requiresAuth:true }},
  { path: '/messages',       name: 'Messages',       component: () => import('@/views/MessagesView.vue'),     meta:{ requiresAuth:true }},
  { path: '/chat',           name: 'Chat',           component: () => import('@/views/ChatView.vue'),         meta:{ requiresAuth:true }},

  { path: '/routines',       name: 'Routines',       component: () => import('@/views/RoutinesView.vue'),     meta:{ requiresAuth:true }},
  { path: '/routine/create', name: 'RoutineCreate',  component: () => import('@/views/RoutineCreateView.vue'),meta:{ requiresAuth:true }},
  { path: '/routine/:id',    name: 'RoutineSelected',component: () => import('@/views/RoutineSelected.vue'),  meta:{ requiresAuth:true }},
  { path: '/routine/:id/edit', name: 'RoutineEdit',  component: () => import('@/views/RoutineEditView.vue'),  meta:{ requiresAuth:true }},

  // Progreso / Reportes
  { path: '/progress',       name: 'Progress',       component: () => import('@/views/ProgressView.vue'),     meta:{ requiresAuth:true }},
  { path: '/reports/create/:routineId',    name: 'ReportCreate',    component: () => import('@/views/ReportView.vue'), meta:{ requiresAuth:true }},
  { path: '/reports/:id',    name: 'ReportView',    component: () => import('@/views/ReportView.vue'),  meta:{ requiresAuth:true }},
  { path: '/reports/:id/edit',    name: 'ReportEdit',    component: () => import('@/views/ReportView.vue'),  meta:{ requiresAuth:true }},
  // Ver progreso de otro usuario
  { path: '/progress/:userId',  name: 'ProgressUser', component: () => import('@/views/ProgressView.vue'), meta:{ requiresAuth:true }},

  // Buscador de usuarios
  { path: '/users/search',   name: 'UsersSearch',    component: () => import('@/views/UsersSearchView.vue'),  meta:{ requiresAuth:true }},

]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

// Guard global
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta?.requiresAuth && !token) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  // Si ya estoy logueado, evitar ir a login/register
  if (token && (to.name === 'Login' || to.name === 'Register')) {
    return next({ name: 'Publications' })
  }

  next()
})

export default router