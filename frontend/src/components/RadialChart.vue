<template>
  <Doughnut :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(ArcElement, Tooltip, Legend, ChartDataLabels)

const props = defineProps({
  deportes: Object
})

const labels = Object.keys(props.deportes)
const values = Object.values(props.deportes)

const chartData = {
  labels,
  datasets: [
    {
      data: values,
      backgroundColor: [
        '#3B82F6',
        '#A855F7',
        '#F59E0B',
        '#EF4444',
        '#10B981'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }
  ]
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    },
    datalabels: {
      color: '#fff',
      font: {
        weight: 'bold',
        size: 12
      },
      formatter: (value) => value  // 👉 esto muestra el número directamente
    }
  }
}
</script>
