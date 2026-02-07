/**
 * Dashboard API
 * 
 * API client functions for dashboard data fetching.
 */

import { apiClient } from '@/lib/api/client';
import type {
  DashboardStatistics,
  Activity,
  AssessmentSummary,
  ComplianceTrend,
  ActionPlanSummary,
} from '../types/dashboard';

export interface ApiResponse<T> {
  success: boolean;
  data: T;
}

/**
 * Get dashboard statistics based on user role
 */
export const getStatistics = async (): Promise<DashboardStatistics> => {
  const response = await apiClient.get<ApiResponse<DashboardStatistics>>('/dashboard/statistics');
  return response.data.data;
};

/**
 * Get recent activities
 */
export const getActivities = async (limit = 10): Promise<Activity[]> => {
  const response = await apiClient.get<ApiResponse<Activity[]>>(`/dashboard/activities?limit=${limit}`);
  return response.data.data;
};

/**
 * Get assessment summary by status
 */
export const getAssessments = async (): Promise<AssessmentSummary> => {
  const response = await apiClient.get<ApiResponse<AssessmentSummary>>('/dashboard/assessments');
  return response.data.data;
};

/**
 * Get compliance trend data
 */
export const getComplianceTrend = async (months = 6): Promise<ComplianceTrend> => {
  const response = await apiClient.get<ApiResponse<ComplianceTrend>>(`/dashboard/compliance-trend?months=${months}`);
  return response.data.data;
};

/**
 * Get action plans needing attention
 */
export const getActionPlans = async (limit = 10): Promise<ActionPlanSummary> => {
  const response = await apiClient.get<ApiResponse<ActionPlanSummary>>(`/dashboard/action-plans?limit=${limit}`);
  return response.data.data;
};
