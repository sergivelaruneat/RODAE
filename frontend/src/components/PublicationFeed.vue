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
    :current-user="props.currentUser"
    @close="onClose"
    @deleted="onDeleted"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PublicationItem from './PublicationItem.vue'
import PublicationModal from './PublicationModal.vue'

const props = defineProps({ posts: { type: Array, required: true }, currentUser: { type: Object, default: null }})

const emit = defineEmits(['deleted'])

const modalAbierto = ref(false)
const publicacionSeleccionada = ref(null)


const abrirModal = (post) => {
  publicacionSeleccionada.value = post
  modalAbierto.value = true
}
function onClose() {
  modalAbierto.value = false
  publicacionSeleccionada.value = null
}

function onDeleted(id) {
  onClose()            // cierra el modal
  emit('deleted', id)  // avisa al padre (ProfileView) para retirar la tarjeta
}

</script>

