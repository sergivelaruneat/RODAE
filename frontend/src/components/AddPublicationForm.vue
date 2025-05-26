<!-- src/components/AddPublicationForm.vue -->
<template>
  <div class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full relative">
      <button
        class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl font-bold"
        @click="$emit('close')"
      >
        ×
      </button>
      <h2 class="text-lg font-bold mb-4">Nueva publicación</h2>

      <form @submit.prevent="submitForm">
        <input
          v-model="titulo"
          type="text"
          placeholder="Título"
          class="w-full mb-3 px-4 py-2 border rounded"
          required
        />
        <textarea
          v-model="descripcion"
          placeholder="Descripción"
          class="w-full mb-3 px-4 py-2 border rounded"
          required
        ></textarea>
        <!-- Botón personalizado para archivos -->
        <div class="mb-4">
        <span class="block text-sm font-medium text-gray-700 mb-1">Archivos</span>
        <button
            type="button"
            class="w-full py-2 px-4 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            @click="abrirInput"
        >
            Añadir archivo
        </button>
        <input
            ref="inputArchivo"
            type="file"
            multiple
            class="hidden"
            @change="handleArchivos"
        />
        <p v-if="archivos.length" class="text-xs text-gray-500 mt-2">
            {{ archivos.length }} archivo(s) seleccionado(s)
        </p>
        </div>
        <select v-model="deporte" class="w-full mb-4 px-4 py-2 border rounded">
          <option disabled value="">Selecciona un deporte</option>
          <option>Powerlifting</option>
          <option>CrossFit</option>
          <option>Fitness</option>
          <option>Bodybuilding</option>
          <option>Senderismo</option>
        </select>

        <button
          type="submit"
          class="w-full py-2 text-white font-semibold rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90"
        >
          Publicar
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const titulo = ref('')
const descripcion = ref('')
const deporte = ref('')
const archivos = ref([])

const emit = defineEmits(['close', 'publicar'])
const inputArchivo = ref(null)

const abrirInput = () => {
  if (inputArchivo.value) {
    inputArchivo.value.click()
  }
}

const handleArchivos = (event) => {
  archivos.value = Array.from(event.target.files)
}

const submitForm = () => {
  emit('publicar', {
    titulo: titulo.value,
    descripcion: descripcion.value,
    deporte: deporte.value,
    archivos: archivos.value,
    fecha: new Date().toISOString().split('T')[0]
  })
  emit('close')
}


</script>
