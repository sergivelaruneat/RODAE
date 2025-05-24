<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <form @submit.prevent="register" class="space-y-4">
        <input
          v-model="nombre"
          type="text"
          placeholder="Nombre completo"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />

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
            placeholder="Contraseña (mínimo 6 caracteres)"
            minlength="6"
            class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        />

        <select
          v-model="tipoUsuario"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        >
          <option disabled value="">Selecciona el tipo de cuenta</option>
          <option value="atleta">Atleta</option>
          <option value="entrenador">Entrenador</option>
        </select>

        <button
          type="submit"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition"
        >
          Registrarse
        </button>
        <p v-if="error" class="text-sm text-red-600 text-center">{{ error }}</p>
      </form>
    </div>

    <div class="mt-6 text-center border border-gray-200 bg-white p-4 rounded w-full max-w-sm">
      <p class="text-sm">
        ¿Ya tienes una cuenta?
        <a href="/login" class="text-blue-600 font-semibold hover:underline">Entrar</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const nombre = ref('')
const email = ref('')
const password = ref('')
const tipoUsuario = ref('')
const error = ref('')

const register = async () => {
  error.value = ''
  try {
    if (password.value.length < 6) {
        error.value = 'La contraseña debe tener al menos 6 caracteres.'
        return
    }
    const res = await axios.post('http://127.0.0.1:8000/api/register', {
      nombre: nombre.value,
      email: email.value,
      password: password.value,
      tipo_usuario: tipoUsuario.value
    })
    alert('Registro exitoso')
    // router.push('/login')
  } catch (e) {
    error.value = 'No se pudo completar el registro. Revisa los datos.'
  }
}
</script>
