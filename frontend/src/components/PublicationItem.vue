<template>
  <div
  class="relative cursor-pointer overflow-hidden rounded shadow aspect-square bg-gray-200"
  @click="$emit('openModal', post)"
>
  <!-- Imagen -->
  <img
    v-if="post.tipo === 'imagen'"
    :src="post.archivos[0]"
    alt="Publicación"
    class="w-full h-full object-cover"
  />

  <!-- Vídeo -->
  <video
    v-else
    :src="post.archivos[0]"
    muted
    playsinline
    class="w-full h-full object-cover pointer-events-none"
  ></video>

  <!-- Icono de múltiple archivos -->
  <div
    v-if="post.archivos.length > 1"
    class="absolute top-2 right-2 bg-black bg-opacity-60 text-white p-1 rounded-full text-xs"
  >
    📰
  </div>

  <!-- Icono de vídeo solo si no hay múltiples archivos -->
  <div
    v-else-if="post.tipo === 'video'"
    class="absolute top-2 right-2 bg-black bg-opacity-60 text-white p-1 rounded-full text-xs"
  >
    🎥
  </div>
</div>

</template>

<script setup>
const props = defineProps({ post: Object })
</script>
