<template>
  <div
    @click="$emit('select', routine.id)"
    class="flex items-start gap-4 p-4 hover:bg-gray-100 rounded cursor-pointer"
  >
    <div class="flex-1">
      <p class="text-lg font-semibold">{{ routine.name }}</p>
      <p class="text-sm text-gray-600">
        {{ routine.owner?.name }} · {{ routine.sport_label || routine.sport }}
      </p>
      <p class="text-xs text-gray-500 mt-1">
        {{ routine.exercises_count }} ejercicios
      </p>
    </div>

    <div class="flex items-center gap-1">
      <span v-for="i in 5" :key="i" class="text-yellow-400">
        <span v-if="i <= rating">★</span>
        <span v-else class="text-gray-300">☆</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  routine: { type: Object, required: true },
})

const rating = computed(() =>
  Math.round(props.routine?.rating_avg ?? 0)
)

defineEmits(['select'])
</script>
