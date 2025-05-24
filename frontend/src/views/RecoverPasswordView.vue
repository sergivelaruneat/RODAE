<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <h2 class="text-center text-lg font-semibold mb-4">Recuperar contraseña</h2>

      <form @submit.prevent="recover" class="space-y-4">
        <input
          v-model="email"
          type="email"
          placeholder="Correo electrónico"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />

        <button
          type="submit"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition"
        >
          Enviar instrucciones
        </button>
        <p v-if="mensaje" class="text-sm text-green-600 text-center">{{ mensaje }}</p>
      </form>

      <div class="mt-4 text-center">
        <router-link to="/login" class="text-sm text-blue-600 hover:underline">Volver al inicio de sesión</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('')
const mensaje = ref('')

const recover = () => {
  // Aquí solo mostramos un mensaje simulado
  mensaje.value = 'Si el correo está registrado, recibirás instrucciones pronto.'
  email.value = ''
}
</script>
