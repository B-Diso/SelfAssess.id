/**
 * useDashboard Composable
 * 
 * Vue composable for fetching and managing dashboard data using TanStack Query.
 */

import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import {
  getStatistics,
  getActivities,
  getAssessments,
  getComplianceTrend,
  getActionPlans,
} from '../api/dashboardApi';
import type {
  DashboardStatistics,
  Activity,
  AssessmentSummary,
  ComplianceTrend,
  ActionPlanSummary,
} from '../types/dashboard';

// Query keys for caching
export const dashboardKeys = {
  all: ['dashboard'] as const,
  statistics: () => [...dashboardKeys.all, 'statistics'] as const,
  activities: (limit: number) => [...dashboardKeys.all, 'activities', limit] as const,
  assessments: () => [...dashboardKeys.all, 'assessments'] as const,
  complianceTrend: (months: number) => [...dashboardKeys.all, 'compliance-trend', months] as const,
  actionPlans: (limit: number) => [...dashboardKeys.all, 'action-plans', limit] as const,
};

/**
 * Composable for dashboard statistics
 */
export function useDashboardStatistics() {
  return useQuery<DashboardStatistics>({
    queryKey: dashboardKeys.statistics(),
    queryFn: getStatistics,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Composable for recent activities
 */
export function useDashboardActivities(limit = 10) {
  return useQuery<Activity[]>({
    queryKey: dashboardKeys.activities(limit),
    queryFn: () => getActivities(limit),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
}

/**
 * Composable for assessment summary
 */
export function useDashboardAssessments() {
  return useQuery<AssessmentSummary>({
    queryKey: dashboardKeys.assessments(),
    queryFn: getAssessments,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * Composable for compliance trend
 */
export function useComplianceTrend(months = 6) {
  return useQuery<ComplianceTrend>({
    queryKey: dashboardKeys.complianceTrend(months),
    queryFn: () => getComplianceTrend(months),
    staleTime: 10 * 60 * 1000, // 10 minutes
  });
}

/**
 * Composable for action plans
 */
export function useDashboardActionPlans(limit = 10) {
  return useQuery<ActionPlanSummary>({
    queryKey: dashboardKeys.actionPlans(limit),
    queryFn: () => getActionPlans(limit),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
}

/**
 * Combined composable for all dashboard data
 */
export function useDashboard() {
  const statisticsQuery = useDashboardStatistics();
  const activitiesQuery = useDashboardActivities();
  const assessmentsQuery = useDashboardAssessments();
  const complianceTrendQuery = useComplianceTrend();
  const actionPlansQuery = useDashboardActionPlans();

  // Check if any query is loading
  const isLoading = computed(() =>
    statisticsQuery.isLoading.value ||
    activitiesQuery.isLoading.value ||
    assessmentsQuery.isLoading.value ||
    complianceTrendQuery.isLoading.value ||
    actionPlansQuery.isLoading.value
  );

  // Check if all queries have data
  const hasData = computed(() =>
    statisticsQuery.data.value !== undefined
  );

  return {
    // Individual queries
    statistics: statisticsQuery,
    activities: activitiesQuery,
    assessments: assessmentsQuery,
    complianceTrend: complianceTrendQuery,
    actionPlans: actionPlansQuery,
    // Combined states
    isLoading,
    hasData,
  };
}
