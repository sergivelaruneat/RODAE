<template>
  <div
    class="p-3 border border-gray-200 rounded hover:bg-gray-100 cursor-pointer mb-2"
    @click="verDetalle"
  >
    <p class="font-medium text-gray-800 text-sm">Reporte de: {{ reporte.titulo }}</p>
    <p class="text-xs text-gray-500 mt-1 truncate">{{ reporte.resumen }}</p>
  </div>
</template>

<script setup>
defineProps({
  reporte: Object
})

const verDetalle = () => {
  alert('Vista detalle del reporte no implementada aún.')
}
</script>
