<template>
  <li
    class="flex items-start justify-between p-3 hover:bg-gray-50 cursor-pointer rounded border border-gray-200"
    @click="$emit('select', report.id)"
  >
    <div>
      <p class="text-xs text-gray-500">{{ formatDate(report.created_at) }}</p>
      <p class="font-semibold text-sm">
        {{ report.routine?.name || 'Rutina' }}
      </p>
      <p class="text-xs text-gray-500">
        {{ report.routine?.sport_label || report.routine?.sport || '—' }}
      </p>
    </div>
    <div class="text-sm text-gray-600">
      {{ (report.items?.length ?? 0) }} ítems
    </div>
  </li>
</template>

<script setup>
defineProps({
  report: { type: Object, required: true }
})

const formatDate = (iso) => {
  if (!iso) return '—'
  try { return new Date(iso).toLocaleDateString() }
  catch { return iso }
}
</script>
