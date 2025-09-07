<template>
  <div
    class="relative cursor-pointer overflow-hidden rounded shadow aspect-square bg-gray-200"
    @click="emitOpen"
  >
    <!-- Media -->
    <template v-if="firstMedia">
      <!-- Imagen -->
      <img
        v-if="!isVideo"
        :src="firstMedia"
        alt="Publicación"
        class="w-full h-full object-cover"
        @error="onImgErr"
      />

      <!-- Vídeo -->
      <video
        v-else
        :src="firstMedia"
        muted
        playsinline
        class="w-full h-full object-cover pointer-events-none"
      ></video>

      <!-- Iconos -->
      <div
        v-if="mediaList.length > 1"
        class="absolute top-2 right-2 bg-black/60 text-white p-1 rounded-full text-xs"
        title="Varios archivos"
      >📰</div>

      <div
        v-else-if="isVideo"
        class="absolute top-2 right-2 bg-black/60 text-white p-1 rounded-full text-xs"
        title="Vídeo"
      >🎥</div>
    </template>

    <!-- Placeholder si no hay media -->
    <div
      v-else
      class="w-full h-full flex items-center justify-center text-gray-500 text-sm"
    >
      Sin media
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const emit = defineEmits(['openModal'])
const props = defineProps({ post: { type: Object, required: true } })

// Normaliza lista de medios
const mediaList = computed(() => {
  const p = props.post || {}
  if (Array.isArray(p.archivos) && p.archivos.length) return p.archivos.filter(Boolean)
  if (p.mediaUrl) return [p.mediaUrl]
  return []
})

const firstMedia = computed(() => mediaList.value[0] || null)
const isVideo = computed(() => {
  if (props.post?.tipo === 'video') return true
  const url = firstMedia.value || ''
  return /\.mp4$|\.webm$|\.ogg$/i.test(url)
})

const emitOpen = () => emit('openModal', props.post)

const onImgErr = (e) => {
  e.target.src =
    'data:image/svg+xml;utf8,' +
    encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-family="Arial" font-size="14">Imagen no disponible</text></svg>')
}
</script>
