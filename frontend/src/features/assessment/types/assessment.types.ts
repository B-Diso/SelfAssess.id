/**
 * Assessment (Parent) Types
 *
 * ⚠️ IMPORTANT: These types are for the PARENT Assessment entity,
 * NOT for individual requirement responses!
 *
 * Assessment Status Flow: draft → active → pending_review → reviewed → pending_finish → finished
 * Alternative path: cancelled (cannot revert to draft once left)
 *
 * For Response types, see: assessment-response.types.ts
 */

import type {
  StandardRequirement,
  StandardSection,
} from "../../standard/types/standard";
import type { 
  AssessmentResponse, 
  AssessmentResponseStatus,
  ResponseHistoryItem 
} from "./assessment-response.types";

// Re-export response types for backward compatibility
export type { 
  AssessmentResponse, 
  AssessmentResponseStatus,
  ResponseHistoryItem 
} from "./assessment-response.types";

// Assessment Status Flow: draft → active → pending_review → reviewed → pending_finish → finished
// Alternative paths: rejected (revert to active), cancelled (Super Admin only)
export type AssessmentStatus =
  | "draft"           // Initial state
  | "active"          // Org user can input
  | "pending_review"  // Submitted for org admin review
  | "reviewed"        // Reviewed by org admin
  | "pending_finish"  // Org request finish, pending super admin
  | "finished"        // Final state (Super Admin only)
  | "rejected"        // Rejected, can reactivate to active
  | "cancelled";      // Cancelled by Super Admin

export type ActionPlanStatus = "open" | "in_progress" | "closed";

// Action Plan types
export interface ActionPlan {
  id: string;
  assessmentId: string;
  assessmentResponseId?: string;
  title: string;
  actionPlan?: string | null;
  pic: string;
  dueDate: string;
  createdAt: string;
  updatedAt?: string;
}

export interface CreateActionPlanRequest {
  assessmentId: string;
  assessmentResponseId?: string;
  title: string;
  pic?: string;
  dueDate?: string;
  actionPlan?: string;
}

export interface UpdateActionPlanRequest {
  title?: string;
  pic?: string;
  dueDate?: string;
  actionPlan?: string;
}

export interface AssessmentRequirement extends StandardRequirement {
  response?: AssessmentResponse;
  score?: number;
}

export interface AssessmentElement extends StandardSection {
  requirements?: AssessmentRequirement[];
  progress?: {
    total: number;
    completed: number;
    percentage: number;
  };
}

export interface UpdateRequirementRequest {
  comments?: string;
  compliance_status?:
    | "non_compliant"
    | "partially_compliant"
    | "fully_compliant"
    | "not_applicable";
}

// Assessment types
export interface Assessment {
  id: string;
  organizationId: string;
  organizationName?: string;
  standardId: string;
  standardName?: string;
  name: string;
  periodValue?: string | null;
  startDate?: string | null;
  endDate?: string | null;
  status: AssessmentStatus;
  overallScore?: number | null;
  submittedAt?: string | null;
  finalizedAt?: string | null;
  notes?: string | null;
  total?: number;
  completed?: number;
  pendingReview?: number;
  percentage?: number;
  responses?: AssessmentResponse[];
  createdAt: string;
  updatedAt: string;
}

export interface AssessmentDetail extends Assessment {
  // Additional detail fields if any
}

export interface CreateAssessmentRequest {
  organizationId?: string;
  standardId: string;
  name: string;
  periodValue?: string;
  startDate?: string;
  endDate?: string;
}

export interface UpdateAssessmentRequest {
  name?: string;
  periodValue?: string;
  startDate?: string;
  endDate?: string;
  status?: AssessmentStatus;
  notes?: string;
}

export interface AssessmentListResponse {
  data: Assessment[];
  meta: {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
  };
}

export interface AssessmentFilters {
  page?: number;
  perPage?: number;
  status?: string;
  organizationId?: string;
  search?: string;
}

// Helper types for UI
export interface RequirementProgress {
  total: number;
  completed: number;
  percentage: number;
}

export interface ElementProgress extends RequirementProgress {
  elementId: string;
  elementCode: string;
}

// ============ TREE NAVIGATION TYPES ============

export type TreeNodeType =
  | "standard"
  | "section"
  | "domain"
  | "element"
  | "requirement";

export interface AssessmentTreeNode {
  id: string;
  type: TreeNodeType;
  code: string;
  title: string;
  description?: string;
  children?: AssessmentTreeNode[];
  requirement?: AssessmentResponse;
  progress?: NodeProgress;
  isExpanded?: boolean;
  isActive?: boolean;
  // For requirement nodes
  evidenceHint?: string;
  weight?: number;
}

export interface NodeProgress {
  total: number;
  completed: number;
  percentage: number;
}

export interface AssessmentSummary {
  overallScore: number;
  completedRequirements: number;
  totalRequirements: number;
  partialRequirements: number;
  inProgressRequirements: number;
  pendingRequirements: number;
  actionPlansCount: number;
  evidencesCount: number;
}

// ============ WORKFLOW TYPES ============

export interface WorkflowLog {
  id: string;
  fromStatus: AssessmentStatus;
  toStatus: AssessmentStatus;
  note?: string | null;
  userId?: string | null;
  userName?: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface AssessmentWorkflowResponse {
  assessment: Assessment;
  transition: WorkflowLog | null;
}

export interface ResponseWorkflowResponse {
  response: AssessmentResponse;
  transition: ResponseHistoryItem | null;
}

// ⚠️ IMPORTANT: Use AssessmentStatus for Assessment, AssessmentResponseStatus for Response
export interface AssessmentWorkflowRequest {
  status: AssessmentStatus;
  note?: string;
}

export interface ResponseWorkflowRequest {
  status: AssessmentResponseStatus;  // Uses DIFFERENT enum!
  note?: string;
}

// ============ NOTES TYPES ============

export interface UpdateNotesRequest {
  comments?: string;
  actionPlan?: string;
}

// ============ ASSESSMENT WORKFLOW HELPERS ============

/**
 * Valid status transitions for assessments
 * Based on the new simplified workflow:
 * - draft → active → pending_review → reviewed → pending_finish → finished
 * - All states can go to cancelled (except finished which can only be cancelled)
 * - Cancelled can return to active
 * - Rejected can return to active
 */
export const VALID_ASSESSMENT_TRANSITIONS: Record<AssessmentStatus, AssessmentStatus[]> = {
  draft: ["active", "cancelled"],
  active: ["pending_review"],
  pending_review: ["reviewed", "active"],
  reviewed: ["pending_finish", "active"],
  pending_finish: ["finished", "active"],
  finished: ["cancelled"],
  rejected: ["active"],
  cancelled: ["active"],
};

/**
 * Check if an assessment transition is valid
 */
export function isValidAssessmentTransition(
  from: AssessmentStatus,
  to: AssessmentStatus
): boolean {
  return VALID_ASSESSMENT_TRANSITIONS[from]?.includes(to) ?? false;
}

/**
 * Workflow state helper interface
 * Provides detailed workflow capabilities for each assessment status
 */
export interface AssessmentWorkflowState {
  status: AssessmentStatus;
  canActivate: boolean;
  canSubmitForReview: boolean;
  canApproveReview: boolean;
  canRejectReview: boolean;
  canRequestFinish: boolean;
  canRevertFromReviewed: boolean;
  canFinalize: boolean;
  canRevertFromPendingFinish: boolean;
  canCancel: boolean;
}

/**
 * Get detailed workflow state for an assessment status
 *
 * Rules:
 * - draft: Can activate or cancel
 * - active: Can submit for review
 * - pending_review: Can approve (reviewed) or reject (revert to active)
 * - reviewed: Can request finish or revert to active
 * - pending_finish: Can finalize (finished) or revert to active
 * - finished: Can cancel
 * - cancelled: Can reactivate
 * - rejected: Can reactivate
 */
export function getAssessmentWorkflowState(status: AssessmentStatus): AssessmentWorkflowState {
  return {
    status,
    canActivate: status === "draft" || status === "cancelled" || status === "rejected",
    canSubmitForReview: status === "active",
    canApproveReview: status === "pending_review",
    canRejectReview: status === "pending_review",
    canRequestFinish: status === "reviewed",
    canRevertFromReviewed: status === "reviewed",
    canFinalize: status === "pending_finish",
    canRevertFromPendingFinish: status === "pending_finish",
    canCancel: ["draft", "active", "pending_review", "reviewed", "pending_finish", "finished"].includes(status),
  };
}
