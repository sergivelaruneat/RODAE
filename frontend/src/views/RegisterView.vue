<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 bg-white border border-gray-200 rounded-lg shadow-sm">
      <img src="/logoSF.png" alt="RODAE" class="w-40 mx-auto mb-6" />

      <form @submit.prevent="submit" class="space-y-4">
        <input
          v-model="name"
          type="text"
          placeholder="Nombre completo"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />

        <input
          v-model="username"
          type="text"
          placeholder="Nombre de usuario (debe ser único)"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
        <!-- feedback disponibilidad -->
        <p v-if="usernameAvailable === true" class="text-sm text-green-600 mt-1">
          ✔ Nombre de usuario disponible
        </p>
        <p v-else-if="usernameAvailable === false" class="text-sm text-red-600 mt-1">
          ✖ Este nombre de usuario ya está en uso
        </p>

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

        <input
          v-model="password_confirmation"
          type="password"
          placeholder="Confirmar contraseña"
          minlength="6"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />

        <select
          v-model="role"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        >
          <option disabled value="">Selecciona el tipo de cuenta</option>
          <option value="athlete">Atleta</option>
          <option value="trainer">Entrenador</option>
        </select>

        <button
          type="submit"
          :disabled="loading || usernameAvailable === false"
          class="w-full py-2 font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 transition disabled:opacity-60"
        >
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>

        <!-- Mensajes -->
        <p v-if="ok" class="text-sm text-green-600 text-center">{{ ok }}</p>
        <p v-if="error" class="text-sm text-red-600 text-center whitespace-pre-line">{{ error }}</p>

        <!-- Acciones tras registro -->
        <div v-if="ok" class="mt-3 flex flex-col items-center gap-2">
          <button
            type="button"
            @click="resendVerification"
            :disabled="resendLoading"
            class="text-blue-600 hover:underline"
          >
            {{ resendLoading ? 'Reenviando…' : 'Reenviar verificación' }}
          </button>
          <router-link
            to="/login"
            class="text-sm text-gray-700 hover:underline"
          >
            Ir a iniciar sesión
          </router-link>

          <p v-if="resendMsg" class="text-sm text-green-600 text-center">{{ resendMsg }}</p>
          <p v-if="resendError" class="text-sm text-red-600 text-center">{{ resendError }}</p>
        </div>
      </form>
    </div>

    <div class="mt-6 text-center border border-gray-200 bg-white p-4 rounded w-full max-w-sm">
      <p class="text-sm">
        ¿Ya tienes una cuenta?
        <router-link to="/login" class="text-blue-600 font-semibold hover:underline">Entrar</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const name = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const role = ref('') // 'athlete' | 'trainer'

const error = ref('')
const ok = ref('')
const loading = ref(false)

// Disponibilidad username (debounce)
const usernameAvailable = ref(null) // null = no comprobado, true = libre, false = ocupado
let timeout = null

watch(username, (val) => {
  usernameAvailable.value = null
  if (timeout) clearTimeout(timeout)
  if (val && val.length > 2) {
    timeout = setTimeout(async () => {
      try {
        const { data } = await axios.get(`/check-username/${encodeURIComponent(val)}`)
        usernameAvailable.value = data.available
      } catch {
        usernameAvailable.value = null
      }
    }, 400)
  }
})

const resendMsg = ref('')
const resendError = ref('')
const resendLoading = ref(false)

const resendVerification = async () => {
  resendMsg.value = ''
  resendError.value = ''
  resendLoading.value = true
  try {
    // Endpoint público por email (ver notas de backend abajo)
    const { data } = await axios.post('/email/resend-public', { email: email.value })
    resendMsg.value = data?.message || 'Te hemos enviado un nuevo correo de verificación.'
  } catch (e) {
    resendError.value = e?.response?.data?.message || 'No se pudo reenviar el correo de verificación.'
  } finally {
    resendLoading.value = false
  }
}

const submit = async () => {
  error.value = ''
  ok.value = ''

  if (password.value.length < 6) {
    error.value = 'La contraseña debe tener al menos 6 caracteres.'
    return
  }
  if (password.value !== password_confirmation.value) {
    error.value = 'Las contraseñas no coinciden.'
    return
  }
  if (usernameAvailable.value === false) {
    error.value = 'Este nombre de usuario ya está en uso.'
    return
  }

  loading.value = true
  try {
    await axios.post('/register', {
      name: name.value,
      username: username.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
      role: role.value,
    })
    ok.value = 'Registro correcto. Revisa tu correo para verificar la cuenta.'
    // (opcional) limpiar campos:
    // name.value = username.value = email.value = password.value = password_confirmation.value = role.value = ''
  } catch (e) {
    const errs = e?.response?.data?.errors
    if (errs) {
      error.value = Object.values(errs).flat().join('\n')
    } else if (e?.response?.data?.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'No se pudo completar el registro. Revisa los datos.'
    }
  } finally {
    loading.value = false
  }
}
</script>