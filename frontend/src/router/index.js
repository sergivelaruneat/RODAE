import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: () => import('@/views/LoginView.vue')},
  { path: '/register', name: 'Register', component: () => import('@/views/RegisterView.vue')},
  { path: '/recoverpassword', name: 'RecoverPassword', component: () => import('@/views/RecoverPasswordView.vue')},
  { path: '/resetpassword', name: 'ResetPassword', component: () => import('@/views/ResetPasswordView.vue')},
  { path: '/profile', name: 'Profile', component: () => import('@/views/ProfileView.vue')},
  { path: '/editprofile', name: 'EditProfile', component: () => import('@/views/EditProfileView.vue')},
  { path: '/messages', name: 'Messages', component: () => import('@/views/MessagesView.vue') },
  { path: '/chat', name: 'Chat', component: () => import('@/views/ChatView.vue')},
  { path: '/publications', name: 'Publications', component: () => import('@/views/PublicationView.vue')},
  { path: '/profile/:user', name: 'OtherProfile', component: () => import('@/views/OtherProfileView.vue')}
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
