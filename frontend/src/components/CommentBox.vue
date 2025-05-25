<template>
  <div class="border-t px-4 py-3">
    <input
      v-model="comentario"
      placeholder="Escribe un comentario..."
      class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-2"
    />
    <button
      @click="emitirComentario"
      class="w-full py-2 text-white rounded font-semibold bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90"
    >
      Publicar
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const comentario = ref('')

const emit = defineEmits(['comentar'])

const emitirComentario = () => {
  if (comentario.value.trim()) {
    emit('comentar', comentario.value)
    comentario.value = ''
  }
}
</script>
