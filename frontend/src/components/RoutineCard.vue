<template>
  <div @click="$emit('select', rutina)" class="flex items-start gap-4 p-4 hover:bg-gray-100 rounded cursor-pointer">
    <div class="flex-1">
      <p class="text-lg font-semibold">{{ rutina.nombre }}</p>
      <p class="text-sm text-gray-600">{{ rutina.creador }} · {{ rutina.deporte }}</p>
      <p class="text-sm text-gray-500 mt-1">{{ rutina.descripcion }}</p>
    </div>
    <div class="flex items-center gap-1">
      <span v-for="i in 5" :key="i" class="text-yellow-400">
        <span v-if="i <= rutina.valoracion">★</span>
        <span v-else class="text-gray-300">☆</span>
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rutina: Object
})
defineEmits(['select'])
</script>