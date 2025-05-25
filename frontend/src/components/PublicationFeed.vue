<template>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <PublicationItem
      v-for="post in posts"
      :key="post.id"
      :post="post"
      @openModal="abrirModal"
    />

    <PublicationModal
      v-if="modalAbierto"
      :post="publicacionSeleccionada"
      @close="modalAbierto = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PublicationItem from './PublicationItem.vue'
import PublicationModal from './PublicationModal.vue'

const props = defineProps({ posts: Array })

const modalAbierto = ref(false)
const publicacionSeleccionada = ref(null)

const abrirModal = (post) => {
  publicacionSeleccionada.value = post
  modalAbierto.value = true
}
</script>

