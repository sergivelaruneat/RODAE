<!-- src/components/RadialChart.vue -->
<template>
  <div class="w-full h-full">
    <Doughnut v-if="values.length" :data="chartData" :options="chartOptions" />
    <div v-else class="h-full flex items-center justify-center text-sm text-gray-500">
      Sin datos todavía.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(ArcElement, Tooltip, Legend, ChartDataLabels)

const props = defineProps({
  // Admite objeto {label: count} o array [{label,count}]
  deportes: { type: [Object, Array], default: () => ({}) }
})

/* Normaliza datos a [{label, value}] */
const entries = computed(() => {
  if (Array.isArray(props.deportes)) {
    return props.deportes.map(x => ({
      label: x.label ?? x.sport ?? String(x[0]),
      value: Number(x.count ?? x[1] ?? 0),
    })).filter(e => e.value > 0)
  }
  return Object.entries(props.deportes || {})
    .map(([label, v]) => ({ label, value: Number(v || 0) }))
    .filter(e => e.value > 0)
})

const labels = computed(() => entries.value.map(e => e.label))
const values = computed(() => entries.value.map(e => e.value))

/* Colores dinámicos */
const colors = computed(() => {
  const n = Math.max(values.value.length, 1)
  return values.value.map((_, i) => `hsl(${Math.round((360*i)/n)}, 70%, 55%)`)
})

/* Datos reactivos para vue-chartjs */
const chartData = computed(() => ({
  labels: labels.value,
  datasets: [{
    data: values.value,
    backgroundColor: colors.value,
    borderColor: '#fff',
    borderWidth: 2,
    hoverOffset: 4,
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true } },
    datalabels: {
      color: '#fff',
      font: { weight: 'bold', size: 12 },
      formatter: (v) => v
    }
  }
}
</script>
