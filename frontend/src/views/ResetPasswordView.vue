<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <h2 class="text-center text-lg font-semibold mb-4">Restablecer contraseña</h2>

      <form @submit.prevent="resetPassword" class="space-y-4">
        <input
          v-model="password"
          type="password"
          placeholder="Nueva contraseña"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          minlength="6"
          required
        />

        <input
          v-model="confirmPassword"
          type="password"
          placeholder="Confirmar contraseña"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          minlength="6"
          required
        />

        <button
          type="submit"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition"
        >
          Guardar nueva contraseña
        </button>

        <p v-if="error" class="text-sm text-red-600 text-center">{{ error }}</p>
        <p v-if="mensaje" class="text-sm text-green-600 text-center">{{ mensaje }}</p>
      </form>

      <div class="mt-4 text-center">
        <router-link to="/login" class="text-sm text-blue-600 hover:underline">Volver al inicio de sesión</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const password = ref('')
const confirmPassword = ref('')
const mensaje = ref('')
const error = ref('')

const route = useRoute()
const token = ref('')

onMounted(() => {
    token.value = route.query.token || ''
})

const resetPassword = async () => {
    error.value = ''
    success.value = ''

    if (password.value.length < 6) {
        error.value = 'La contraseña debe tener al menos 6 caracteres.'
        return
    }
    if (password.value !== confirmPassword.value) {
        error.value = 'Las contraseñas no coinciden.'
        return
    }

    error.value = ''
    mensaje.value = 'Contraseña restablecida correctamente (simulado).'
    password.value = ''
    confirmPassword.value = ''
}
</script>