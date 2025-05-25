<template>
  <div class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center">
    <div class="bg-white w-full max-w-6xl h-[80vh] rounded-lg flex overflow-hidden relative">
      
      <!-- Botón cerrar -->
      <button
        class="absolute top-4 right-4 text-black text-3xl font-bold z-50 hover:text-red-500"
        @click="$emit('close')"
      >
        ×
      </button>

      <!-- Contenido multimedia -->
      <div class="w-2/3 h-full bg-black flex items-center justify-center relative">
        <div class="w-full h-full flex items-center overflow-hidden">
          <template v-for="(media, index) in post.archivos" :key="index">
            <div v-show="currentIndex === index" class="w-full h-full flex items-center justify-center">
              <img
                v-if="post.tipo === 'imagen'"
                :src="media"
                class="max-h-full max-w-full object-contain"
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
        <button v-if="post.archivos.length > 1 && currentIndex > 0" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-4xl" @click="currentIndex--">‹</button>
        <button v-if="post.archivos.length > 1 && currentIndex < post.archivos.length - 1" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-4xl" @click="currentIndex++">›</button>
      </div>

      <!-- Panel lateral derecho -->
      <div class="w-1/3 h-full flex flex-col">
        <!-- Scrollable contenido -->
        <div class="p-4 overflow-y-auto flex-1">
          <h2 class="text-lg font-semibold mb-2">{{ post.nombre }}</h2>
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

          <!-- Comentarios existentes -->
          <h3 class="text-sm font-semibold mb-2">Comentarios</h3>
          <ul>
            <li v-for="comentario in post.comentarios" :key="comentario.id" class="mb-2 text-sm text-gray-800">
              <span class="font-semibold">{{ comentario.autor }}:</span>
              {{ comentario.texto }}
            </li>
          </ul>
        </div>

        <!-- Componente de nueva caja de comentarios -->
        <CommentBox @comentar="agregarComentario" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommentBox from './CommentBox.vue'

const props = defineProps({ post: Object })

const currentIndex = ref(0)

watch(() => props.post, () => {
  currentIndex.value = 0
})

const agregarComentario = (texto) => {
  if (texto.trim()) {
    props.post.comentarios.push({
      id: Date.now(),
      autor: 'Tú',
      texto
    })
  }
}
</script>
