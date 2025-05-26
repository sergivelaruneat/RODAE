<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-6 text-center">Editar Perfil</h2>

      <form @submit.prevent="guardarCambios" class="space-y-4">
        <div class="flex flex-col items-center">
          <img
            :src="fotoPerfil"
            alt="Foto actual"
            class="w-24 h-24 rounded-full object-cover mb-2 cursor-pointer"
            @click="ampliarFoto = true"
          />
          <label class="cursor-pointer text-sm text-blue-600 hover:underline">
            Cambiar foto
            <input type="file" class="hidden" @change="seleccionarFoto" />
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input v-model="nombre" type="text" class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
          <input v-model="fechaNacimiento" type="date" class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deporte principal</label>
          <input v-model="deporte" type="text" class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
          <select v-model="rol" class="w-full border px-3 py-2 rounded">
            <option value="Atleta">Atleta</option>
            <option value="Entrenador">Entrenador</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea v-model="descripcion" rows="3" class="w-full border px-3 py-2 rounded"></textarea>
        </div>

        <div class="flex justify-end space-x-4 pt-4">
          <button
            type="button"
            class="px-5 py-2 text-sm rounded font-semibold text-gray-600 border border-gray-300 hover:bg-gray-100"
            @click="cancelar"
          >
            Cancelar
          </button>

          <button
            type="submit"
            class="px-5 py-2 text-sm text-white font-semibold rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90"
          >
            Guardar cambios
          </button>
        </div>
      </form>
    </div>

    <!-- Modal para hacer zoom a la foto -->
    <div
      v-if="ampliarFoto"
      class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
    >
      <div class="relative">
        <button
          class="absolute top-2 right-2 text-white text-3xl font-bold z-50 hover:text-red-500"
          @click="ampliarFoto = false"
        >
          ×
        </button>
        <img :src="fotoPerfil" class="max-h-[80vh] max-w-full object-contain rounded-lg" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'

const router = useRouter()

// Datos simulados
const fotoPerfil = ref('/perfilUsuario.jpg')
const nombre = ref('Sergio Velarde Álvarez')
const fechaNacimiento = ref('1999-10-10')
const deporte = ref('Powerlifting')
const rol = ref('Entrenador')
const descripcion = ref('Mi vida se basa en estar entrenando o lesionado.')

const ampliarFoto = ref(false)

const seleccionarFoto = (e) => {
  const file = e.target.files[0]
  if (file) {
    // Simulación: convertir a URL temporal para previsualizar
    fotoPerfil.value = URL.createObjectURL(file)
  }
}

const guardarCambios = () => {
  alert('Cambios guardados (simulado)')
  router.push('/profile')
}

const cancelar = () => {
  router.push('/profile')
}
</script>

