<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <h2 class="text-center text-lg font-semibold mb-4">Restablecer contraseña</h2>

      <form @submit.prevent="resetPassword" class="space-y-4">
        <input
          v-model="password"
          type="password"
          placeholder="Nueva contraseña (mínimo 6 caracteres)"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          minlength="6"
          required
        />

        <input
          v-model="password_confirmation"
          type="password"
          placeholder="Confirmar contraseña"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          minlength="6"
          required
        />

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition disabled:opacity-60"
        >
          {{ loading ? 'Guardando…' : 'Guardar nueva contraseña' }}
        </button>

        <p v-if="error" class="text-sm text-red-600 text-center whitespace-pre-line">{{ error }}</p>
        <p v-if="ok" class="text-sm text-green-600 text-center">{{ ok }}</p>
      </form>

      <div class="mt-4 text-center">
        <router-link to="/login" class="text-sm text-blue-600 hover:underline">Volver al inicio de sesión</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const token = ref('')
const email = ref('') // viene en la URL (?email=...)
const password = ref('')
const password_confirmation = ref('')

const loading = ref(false)
const ok = ref('')
const error = ref('')

onMounted(() => {
  token.value = route.query.token || ''
  email.value = route.query.email || ''
})

const resetPassword = async () => {
  error.value = ''
  ok.value = ''

  if (!token.value || !email.value) {
    error.value = 'El enlace no es válido o está incompleto.'
    return
  }
  if (password.value.length < 6) {
    error.value = 'La contraseña debe tener al menos 6 caracteres.'
    return
  }
  if (password.value !== password_confirmation.value) {
    error.value = 'Las contraseñas no coinciden.'
    return
  }

  loading.value = true
  try {
    await axios.post('/password/reset', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    ok.value = 'Contraseña actualizada correctamente.'
    // redirige al login (opcional con flag)
    setTimeout(() => router.push({ name: 'Login', query: { reset: 1 } }), 700)
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo restablecer la contraseña.'
  } finally {
    loading.value = false
  }
}
</script>
