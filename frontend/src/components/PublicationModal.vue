<template>
  <div class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center" v-if="post">
    <div class="bg-white w-full max-w-6xl h-[80vh] rounded-lg flex overflow-hidden relative">
      <!-- Contenido multimedia -->
      <div class="w-2/3 h-full bg-black flex items-center justify-center relative">
        <div class="w-full h-full flex items-center overflow-hidden">
          <template v-for="(media, index) in post.archivos" :key="index">
            <div v-show="currentIndex === index" class="w-full h-full flex items-center justify-center">
              <img
                v-if="post.tipo === 'imagen'"
                :src="media"
                class="max-h-full max-w-full object-contain"
                alt="Imagen de la publicación"
              />
              <video
                v-else
                :src="media"
                controls
                controlsList="nodownload"
                class="max-h-full max-w-full object-contain"
              ></video>
            </div>
          </template>
        </div>

        <!-- Slider -->
        <button
          v-if="post.archivos.length > 1 && currentIndex > 0"
          class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-4xl"
          @click="currentIndex--"
          aria-label="Anterior"
          title="Anterior"
        >
          ‹
        </button>
        <button
          v-if="post.archivos.length > 1 && currentIndex < post.archivos.length - 1"
          class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-4xl"
          @click="currentIndex++"
          aria-label="Siguiente"
          title="Siguiente"
        >
          ›
        </button>
      </div>

      <!-- Panel lateral derecho -->
      <div class="w-1/3 h-full flex flex-col">
        <!-- Scrollable contenido -->
        <div class="p-4 overflow-y-auto flex-1">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold">{{ post.titulo }}</h2>

            <!-- Botón eliminar (a la izquierda de la X) -->
            <button
              v-if="isOwner"
              @click="confirmOpen = true"          
              class="absolute top-5 right-12 z-50
                    text-red-600 text-sm border border-red-600 rounded px-2 py-1
                    hover:bg-red-600 hover:text-white disabled:opacity-60"
              :disabled="deleting"
              aria-label="Eliminar publicación"
              title="Eliminar publicación"
            >
              {{ deleting ? 'Eliminando…' : 'Eliminar' }}
            </button>
            <!-- Botón cerrar (X) -->
            <button
              class="absolute top-4 right-4 text-black text-3xl font-bold z-50 hover:text-red-500"
              @click="$emit('close')"
              aria-label="Cerrar"
            >
              ×
            </button>
            <!-- Diálogo de confirmación -->
            <ConfirmDialog
              :open="confirmOpen"
              title="Eliminar publicación"
              message="¿Seguro que quieres eliminar esta publicación? Esta acción no se puede deshacer."
              @cancel="confirmOpen = false"
              @confirm="doDelete"
            />
          </div>

          <p class="text-gray-700 text-sm mb-2">{{ post.descripcion }}</p>
          <p class="text-sm text-gray-500 mb-1">Publicado: {{ post.fecha }}</p>

          <div class="flex flex-wrap gap-2 mb-4">
            <span
              v-for="(tag, idx) in post.deporte"
              :key="idx"
              class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs"
            >
              #{{ tag }}
            </span>
          </div>

          <!-- Comentarios -->
          <h3 class="text-sm font-semibold mb-2">
            Comentarios
            <span v-if="totalComments !== null" class="text-gray-500 font-normal">({{ totalComments }})</span>
          </h3>

          <div v-if="loadingComments" class="text-sm text-gray-500">Cargando comentarios…</div>

          <ul v-else>
            <li
              v-for="c in comments"
              :key="c.id"
              class="mb-2 text-sm text-gray-800"
            >
              <span class="font-semibold">{{ c.user?.name ?? 'Usuario' }}:</span>
              {{ c.body }}
            </li>

            <li v-if="!comments.length" class="text-sm text-gray-500">Sé el primero en comentar</li>
          </ul>

          <div class="mt-3">
            <button
              v-if="hasMore && !loadingMore"
              @click="cargarMas"
              class="text-sm text-blue-600 hover:underline"
            >
              Cargar más
            </button>
            <span v-else-if="loadingMore" class="text-sm text-gray-500">Cargando…</span>
          </div>
        </div>

        <!-- Caja de nuevo comentario -->
        <CommentBox @comentar="agregarComentario" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import CommentBox from './CommentBox.vue'
import ConfirmDialog from './ConfirmDialog.vue' 

const props = defineProps({
  post: { type: Object, required: true },
  currentUser: { type: Object, default: null }
})
const emit = defineEmits(['close', 'deleted'])

const currentIndex = ref(0)
const deleting = ref(false)
const confirmOpen = ref(false)

/** Estado de comentarios */
const comments = ref([])            // lista local
const totalComments = ref(null)     // total del backend
const page = ref(1)
const perPage = 10
const hasMore = ref(false)
const loadingComments = ref(false)
const loadingMore = ref(false)

/** Cargar comentarios al cambiar de publicación */
watch(
  () => props.post?.id,
  async (newId) => {
    currentIndex.value = 0
    resetComments()
    if (newId) await cargarComentarios(1)
  },
  { immediate: true }
)

/** Owner: compatible con mock y con recurso del backend */
const isOwner = computed(() => {
  // Simplificamos la lógica para que solo compare IDs
  const currentUserId = props.currentUser?.id
  const postUserId = props.post?.user_id || props.post?.user?.id
  
  return currentUserId && postUserId && currentUserId === postUserId
})

function resetComments() {
  comments.value = []
  totalComments.value = null
  page.value = 1
  hasMore.value = false
}

/** GET /publications/{id}/comments */
async function cargarComentarios(targetPage = 1) {
  try {
    if (targetPage === 1) loadingComments.value = true
    else loadingMore.value = true

    const { data } = await axios.get(`/publications/${props.post.id}/comments`, {
      params: { per_page: perPage, page: targetPage },
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })

    // data es paginator Laravel: { data, meta, links }
    const items = Array.isArray(data?.data) ? data.data : []
    if (targetPage === 1) comments.value = items
    else comments.value = [...comments.value, ...items]

    totalComments.value = data?.meta?.total ?? comments.value.length
    page.value = targetPage
    const lastPage = data?.meta?.last_page ?? 1
    hasMore.value = page.value < lastPage
  } catch (e) {
    console.error('Error cargando comentarios', e)
  } finally {
    loadingComments.value = false
    loadingMore.value = false
  }
}

/** Botón "Cargar más" */
async function cargarMas() {
  if (!hasMore.value || loadingMore.value) return
  await cargarComentarios(page.value + 1)
}

/** POST /publications/{id}/comments */
async function agregarComentario(texto) {
  const body = (texto || '').trim()
  if (!body) return
  try {
    const { data } = await axios.post(
      `/publications/${props.post.id}/comments`,
      { body },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    )

    // Añadimos al final (o al principio, como prefieras)
    comments.value.push(data)
    totalComments.value = (totalComments.value ?? 0) + 1
  } catch (e) {
    alert(e?.response?.data?.message ?? 'No se pudo publicar el comentario.')
  }
}

/** DELETE /publications/{id} */
async function doDelete () {
  try {
    deleting.value = true
    await axios.delete(`/publications/${props.post.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    emit('deleted', props.post.id) // la vista del feed lo quita
    emit('close')                  // cierra el modal
  } catch (err) {
    console.error('Error eliminando publicación', err?.response?.data || err)
  } finally {
    deleting.value = false
    confirmOpen.value = false
  }
}
</script>
