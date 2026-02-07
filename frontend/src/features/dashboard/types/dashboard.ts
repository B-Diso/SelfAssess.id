/**
 * Dashboard Types
 * 
 * Type definitions for dashboard feature.
 */

// ============================================================================
// Statistics Types
// ============================================================================

export interface SuperAdminStats {
  totalOrganizations: number;
  totalUsers: number;
  activeAssessments: number;
  pendingReviews: number;
}

export interface OrgAdminStats {
  totalUsers: number;
  activeAssessments: number;
  completionRate: number;
  pendingActionPlans: number;
}

export interface ReviewerStats {
  pendingReviews: number;
  reviewedThisMonth: number;
  avgReviewTime: number;
  rejectedAssessments: number;
}

export interface UserStats {
  myAssessments: number;
  completionProgress: number;
  openRequirements: number;
  orgActionPlans: number;
}

export interface DashboardStatistics {
  superAdmin?: SuperAdminStats;
  orgAdmin?: OrgAdminStats;
  reviewer?: ReviewerStats;
  user?: UserStats;
}

// ============================================================================
// Activity Types
// ============================================================================

export interface Activity {
  id: string;
  type: 'assessment_created' | 'assessment_submitted' | 'assessment_reviewed' | 'user_login' | 'user_created' | 'action_plan_created' | 'action_plan_completed' | 'evidence_uploaded';
  description: string;
  user?: {
    id: string;
    name: string;
  };
  organization?: {
    id: string;
    name: string;
  };
  metadata?: Record<string, unknown>;
  createdAt: string;
}

// ============================================================================
// Assessment Types
// ============================================================================

export interface AssessmentStatusCount {
  draft: number;
  active: number;
  submitted: number;
  reviewed: number;
  finished: number;
  rejected: number;
  cancelled: number;
}

export interface RecentAssessment {
  id: string;
  name: string;
  organizationName?: string;
  status: string;
  progress: number;
  createdAt: string;
}

export interface AssessmentSummary {
  byStatus: AssessmentStatusCount;
  recent: RecentAssessment[];
}

// ============================================================================
// Compliance Trend Types
// ============================================================================

export interface ComplianceTrend {
  labels: string[];
  datasets: {
    label: string;
    data: number[];
  }[];
}

// ============================================================================
// Action Plan Types
// ============================================================================

export interface ActionPlan {
  id: string;
  title: string;
  assessment: {
    id: string;
    name: string;
  };
  dueDate: string;
  pic?: {
    id: string;
    name: string;
  };
  status: string;
}

export interface OverdueActionPlan extends ActionPlan {
  daysOverdue: number;
}

export interface UpcomingActionPlan extends ActionPlan {
  daysRemaining: number;
}

export interface ActionPlanSummary {
  overdue: OverdueActionPlan[];
  upcoming: UpcomingActionPlan[];
}

// ============================================================================
// Chart Types
// ============================================================================

export interface ChartDataset {
  label: string;
  data: number[];
  backgroundColor?: string | string[];
  borderColor?: string | string[];
  borderWidth?: number;
}

export interface ChartData {
  labels: string[];
  datasets: ChartDataset[];
}

// Status color mapping for charts
export const STATUS_COLORS: Record<string, string> = {
  draft: '#9CA3AF',      // gray-400
  active: '#3B82F6',     // blue-500
  submitted: '#F59E0B',  // amber-500
  reviewed: '#8B5CF6',   // violet-500
  finished: '#10B981',   // emerald-500
  rejected: '#EF4444',   // red-500
  cancelled: '#6B7280',  // gray-500
};
