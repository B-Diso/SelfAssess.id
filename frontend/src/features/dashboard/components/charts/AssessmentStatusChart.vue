<script setup lang="ts">
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, type ChartData, type ChartOptions } from 'chart.js'
import ChartCard from './ChartCard.vue'
import { STATUS_COLORS, type AssessmentStatusCount } from '../../types/dashboard'

ChartJS.register(ArcElement, Tooltip, Legend)

interface Props {
  data: AssessmentStatusCount | undefined
  loading?: boolean
}

const props = defineProps<Props>()

const chartData = computed<ChartData<'doughnut'>>(() => {
  if (!props.data) {
    return {
      labels: [],
      datasets: [],
    }
  }

  const labels = Object.keys(props.data).map(key => 
    key.charAt(0).toUpperCase() + key.slice(1)
  )
  const values = Object.values(props.data)
  const colors = Object.keys(props.data).map(key => STATUS_COLORS[key] || '#9CA3AF')

  return {
    labels,
    datasets: [
      {
        data: values,
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  }
})

const chartOptions: ChartOptions<'doughnut'> = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '60%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        padding: 16,
        usePointStyle: true,
        pointStyle: 'circle',
      },
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const label = context.label || ''
          const value = context.parsed || 0
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0) as number
          const percentage = total > 0 ? Math.round((value / total) * 100) : 0
          return `${label}: ${value} (${percentage}%)`
        },
      },
    },
  },
}

const hasData = computed(() => {
  if (!props.data) return false
  return Object.values(props.data).some(val => val > 0)
})

const totalAssessments = computed(() => {
  if (!props.data) return 0
  return Object.values(props.data).reduce((a, b) => a + b, 0)
})
</script>

<template>
  <ChartCard title="Assessment Status" subtitle="Distribution by current status" :loading="loading">
    <div class="h-64 relative">
      <div v-if="!hasData && !loading" class="h-full flex flex-col items-center justify-center text-muted-foreground">
        <p>No assessment data available</p>
      </div>
      <template v-else>
        <Doughnut :data="chartData" :options="chartOptions" />
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="text-center">
            <p class="text-3xl font-bold">{{ totalAssessments }}</p>
            <p class="text-sm text-muted-foreground">Total</p>
          </div>
        </div>
      </template>
    </div>
  </ChartCard>
</template>
