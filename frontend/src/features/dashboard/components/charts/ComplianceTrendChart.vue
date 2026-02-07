<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, type ChartData, type ChartOptions } from 'chart.js'
import ChartCard from './ChartCard.vue'
import type { ComplianceTrend } from '../../types/dashboard'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

interface Props {
  data: ComplianceTrend | undefined
  loading?: boolean
}

const props = defineProps<Props>()

const chartData = computed<ChartData<'line'>>(() => {
  if (!props.data) {
    return {
      labels: [],
      datasets: [],
    }
  }

  return {
    labels: props.data.labels,
    datasets: props.data.datasets.map((dataset, index) => ({
      ...dataset,
      borderColor: index === 0 ? '#10B981' : '#3B82F6',
      backgroundColor: index === 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(59, 130, 246, 0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: index === 0 ? '#10B981' : '#3B82F6',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2,
    })),
  }
})

const chartOptions: ChartOptions<'line'> = {
  responsive: true,
  maintainAspectRatio: false,
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
          const label = context.dataset.label || ''
          const value = context.parsed.y || 0
          return `${label}: ${value}%`
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      ticks: {
        callback: (value) => `${value}%`,
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
    },
    x: {
      grid: {
        display: false,
      },
    },
  },
}

const hasData = computed(() => {
  if (!props.data) return false
  return props.data.labels.length > 0 && props.data.datasets.length > 0
})
</script>

<template>
  <ChartCard title="Compliance Trend" subtitle="Compliance rate over time" :loading="loading">
    <div class="h-64">
      <div v-if="!hasData && !loading" class="h-full flex flex-col items-center justify-center text-muted-foreground">
        <p>No compliance data available</p>
      </div>
      <Line v-else :data="chartData" :options="chartOptions" />
    </div>
  </ChartCard>
</template>
