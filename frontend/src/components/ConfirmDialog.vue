<template>
  <div v-if="open" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow p-5 w-full max-w-sm">
      <h3 class="font-semibold text-lg mb-2">{{ title }}</h3>
      <p class="text-sm text-gray-600 mb-4">{{ message }}</p>
      <div class="flex justify-end gap-2">
        <button class="px-3 py-2 text-white rounded font-semibold bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90" @click="$emit('cancel')">Cancelar</button>
        <button class="px-3 py-2 text-red-600 text-sm border border-red-600 rounded px-2 py-1 hover:bg-red-600 hover:text-white disabled:opacity-60" @click="$emit('confirm')">
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar' },
  message: { type: String, default: '¿Seguro que deseas continuar?' },
})
defineEmits(['confirm','cancel'])
</script>
