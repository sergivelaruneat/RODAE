import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import RecoverPasswordView from '../views/RecoverPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'

const routes = [
  { path: '/', redirect: '/login'},
  { path: '/login', name: 'Login', component: LoginView },
  { path: '/register', name: 'Register', component: RegisterView },
  { path: '/recoverpassword', name: 'RecoverPassword', component: RecoverPasswordView },
  { path: '/resetpassword',  name: 'ResetPassword',  component: ResetPasswordView}
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
