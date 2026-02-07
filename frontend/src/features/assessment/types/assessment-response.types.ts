/**
 * Assessment Response (Requirement) Types
 * 
 * ⚠️ IMPORTANT: These types are for INDIVIDUAL requirement responses,
 * NOT for the parent Assessment entity!
 * 
 * Response Status Flow: active → pending_review → reviewed
 * 
 * Note: Responses do NOT have cancelled/rejected/finished states.
 * These are assessment-level only. Responses can only go back to active.
 * 
 * For Assessment types, see: assessment.types.ts
 */

// Response Status - SEPARATE from Assessment status!
// Flow: active → pending_review → reviewed
export type AssessmentResponseStatus =
  | "active"           // Initial state, user can fill/edit compliance
  | "pending_review"   // User finished filling, waiting for reviewer
  | "reviewed";        // Reviewer has approved

// Compliance Status - for the actual assessment content
export type ComplianceStatus =
  | "non_compliant"
  | "partially_compliant"
  | "fully_compliant"
  | "not_applicable";

// Interfaces
export interface AssessmentResponse {
  id: string;
  assessmentId: string;
  standardRequirementId: string;
  status: AssessmentResponseStatus;
  complianceStatus?: ComplianceStatus;
  comments?: string | null;
  createdAt?: string;
  updatedAt?: string;
  // Flattened requirement fields
  requirementId?: string;
  requirementDisplayCode?: string;
  requirementDescription?: string;
  requirementTitle?: string;
  requirementEvidenceHint?: string;
}

export interface ResponseHistoryItem {
  id: string;
  fromStatus?: AssessmentResponseStatus;
  toStatus: AssessmentResponseStatus;
  note?: string;
  createdAt: string;
  user?: {
    id: string;
    name: string;
    email: string;
  };
}

// Request/Response types
export interface UpdateResponseRequest {
  comments?: string;
  compliance_status?: ComplianceStatus;
}

export interface TransitionResponseRequest {
  status: AssessmentResponseStatus;
  note?: string;
}

// Workflow helper types
export interface ResponseWorkflowState {
  status: AssessmentResponseStatus;
  isEditable: boolean;
  canSubmit: boolean;
  canReview: boolean;
  canRequestChanges: boolean;
}

/**
 * Get workflow state for a response status
 */
export function getResponseWorkflowState(status: AssessmentResponseStatus): ResponseWorkflowState {
  return {
    status,
    isEditable: status === "active",
    canSubmit: status === "active",
    canReview: status === "pending_review",
    canRequestChanges: status === "pending_review",
  };
}

/**
 * Valid status transitions for responses
 */
export const VALID_RESPONSE_TRANSITIONS: Record<AssessmentResponseStatus, AssessmentResponseStatus[]> = {
  active: ["pending_review"],
  pending_review: ["reviewed", "active"],
  reviewed: ["active"],
};

/**
 * Check if a transition is valid
 */
export function isValidResponseTransition(
  from: AssessmentResponseStatus,
  to: AssessmentResponseStatus
): boolean {
  return VALID_RESPONSE_TRANSITIONS[from]?.includes(to) ?? false;
}
