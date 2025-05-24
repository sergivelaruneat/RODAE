<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <form @submit.prevent="login" class="space-y-4">
        <input
          v-model="email"
          type="email"
          placeholder="Correo electrónico"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
        <input
          v-model="password"
          type="password"
          placeholder="Contraseña"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
        <button
          type="submit"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition"
        >
          Entrar
        </button>
        <p v-if="error" class="text-sm text-red-600 text-center">{{ error }}</p>
      </form>

      <div class="mt-4 text-center">
        <router-link to="/recoverpassword" class="text-sm text-blue-600 hover:underline">¿Has olvidado la contraseña?</router-link>
      </div>
    </div>

    <div class="mt-6 text-center border border-gray-200 bg-white p-4 rounded w-full max-w-sm">
      <p class="text-sm">
        ¿No tienes una cuenta?
        <router-link to="/register" class="text-blue-600 font-semibold hover:underline">Regístrate</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const password = ref('')
const error = ref('')

const login = async () => {
  error.value = ''
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value
    })
    localStorage.setItem('token', res.data.token)
    alert('Login correcto')
    // router.push('/inicio')
  } catch (e) {
    error.value = 'Credenciales incorrectas'
  }
}
</script>
