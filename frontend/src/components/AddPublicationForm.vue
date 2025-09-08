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
        />
        <textarea
          v-model="descripcion"
          placeholder="Descripción"
          class="w-full mb-3 px-4 py-2 border rounded"
          required
        ></textarea>

        <!-- Archivo (obligatorio: imagen o vídeo) -->
        <div class="mb-4">
          <span class="block text-sm font-medium text-gray-700 mb-1">
            Imagen o vídeo (obligatorio)
          </span>
          <button
            type="button"
            class="w-full py-2 px-4 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            @click="abrirInput"
          >
            Seleccionar archivo
          </button>
          <input
            ref="inputArchivo"
            type="file"
            accept="image/*,video/*"
            class="hidden"
            @change="handleArchivo"
          />
          <p v-if="archivo" class="text-xs text-gray-500 mt-2">
            {{ archivo.name }} ({{ prettySize(archivo.size) }})
          </p>
          <p v-if="errorArchivo" class="text-xs text-red-600 mt-2">
            {{ errorArchivo }}
          </p>
        </div>

        <select v-model="deporte" class="w-full mb-4 px-4 py-2 border rounded">
          <option disabled value="">Selecciona un deporte</option>
          <option>Ciclismo</option>
          <option>Natación</option>
          <option>Jogging</option>
          <option>Correr</option>
          <option>Trail Running</option>
          <option>Atletismo</option>
          <option>Powerlifting</option>
          <option>Musculación</option>
          <option>CrossFit</option>
          <option>Escalada</option>
          <option>Calistenia</option>
        </select>

        <button
          type="submit"
          class="w-full py-2 text-white font-semibold rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 disabled:opacity-60"
          :disabled="loading"
        >
          {{ loading ? 'Publicando…' : 'Publicar' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import http from '@/services/http' // usa TU instancia con baseURL y token

const titulo = ref('')
const descripcion = ref('')
const deporte = ref('')
const archivo = ref(null)
const loading = ref(false)
const errorArchivo = ref('')

const emit = defineEmits(['close', 'created', 'publicar'])
const inputArchivo = ref(null)

const MAX_IMAGE_MB = 6
const MAX_VIDEO_MB = 60
const MAX_VIDEO_SECONDS = 30.5

const abrirInput = () => inputArchivo.value?.click()

function prettySize(bytes) {
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}

function handleArchivo(e) {
  errorArchivo.value = ''
  const file = e.target.files?.[0] || null
  if (!file) { archivo.value = null; return }

  const type = file.type || ''
  // Validación cliente (rápida)
  if (type.startsWith('image/')) {
    if (file.size > MAX_IMAGE_MB * 1024 * 1024) {
      errorArchivo.value = `La imagen no puede superar ${MAX_IMAGE_MB} MB.`
      archivo.value = null; return
    }
    archivo.value = file
    return
  }

  if (type.startsWith('video/')) {
    if (file.size > MAX_VIDEO_MB * 1024 * 1024) {
      errorArchivo.value = `El vídeo no puede superar ${MAX_VIDEO_MB} MB.`
      archivo.value = null; return
    }
    // Validar duración ≤ 30s con metadata del navegador
    const url = URL.createObjectURL(file)
    const v = document.createElement('video')
    v.preload = 'metadata'
    v.onloadedmetadata = () => {
      URL.revokeObjectURL(url)
      const seconds = v.duration
      if (Number.isFinite(seconds) && seconds > MAX_VIDEO_SECONDS) {
        errorArchivo.value = `El vídeo debe durar como máximo 30 segundos.`
        archivo.value = null
      } else {
        archivo.value = file
      }
    }
    v.onerror = () => {
      URL.revokeObjectURL(url)
      errorArchivo.value = 'No se pudo leer el vídeo.'
      archivo.value = null
    }
    v.src = url
    return
  }

  errorArchivo.value = 'Formato no permitido.'
  archivo.value = null
}

async function submitForm() {
  // Requiere archivo
  if (!archivo.value) {
    errorArchivo.value = 'Debes adjuntar una imagen o un vídeo.'
    return
  }

  try {
    loading.value = true
    const fd = new FormData()
    if (titulo.value) fd.append('title', titulo.value)
    fd.append('content', descripcion.value) // requerido por el back
    if (deporte.value) fd.append('sport', deporte.value)
    fd.append('media', archivo.value) // NOMBRE CORRECTO para publications

    // Usa tu instancia http -> enviará a {VITE_API_URL}/publications con Authorization
    const { data } = await http.post('/publications', fd)
    const payload = data?.data ?? data

    // notifica al padre (compatibilidad con @publicar y @created)
    emit('created', payload)
    emit('publicar', payload)
    emit('close')
  } catch (e) {
    const api = e?.response?.data
    if (api?.errors?.media?.length) {
      errorArchivo.value = api.errors.media[0]
    } else if (api?.message) {
      alert(api.message)
    } else {
      alert('Error creando publicación')
    }
    console.error(e)
  } finally {
    loading.value = false
  }
}
</script>
