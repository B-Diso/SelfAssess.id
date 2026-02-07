import { apiClient } from "@/lib/api/client";
import type { ApiMessageResponse } from "@/types/api.types";
import type {
  ActionPlan,
  Assessment,
  AssessmentDetail,
  AssessmentFilters,
  AssessmentListResponse,
  AssessmentResponse,
  AssessmentWorkflowRequest,
  AssessmentWorkflowResponse,
  ResponseHistoryItem,
  ResponseWorkflowRequest,
  ResponseWorkflowResponse,
  CreateActionPlanRequest,
  CreateAssessmentRequest,
  UpdateActionPlanRequest,
  UpdateAssessmentRequest,
  UpdateRequirementRequest,
} from "../types/assessment.types";

export const assessmentApi = {
  // Assessment operations
  async listAssessments(
    filters?: AssessmentFilters,
  ): Promise<AssessmentListResponse> {
    const response = await apiClient.get<AssessmentListResponse>(
      "/assessments",
      {
        params: filters,
      },
    );
    return response.data;
  },

  async getAssessment(id: string): Promise<AssessmentDetail> {
    const response = await apiClient.get<{ data: AssessmentDetail }>(
      `/assessments/${id}`,
      {
        params: { with: "elements,requirements" },
      },
    );
    return response.data.data;
  },

  async createAssessment(
    payload: CreateAssessmentRequest,
  ): Promise<Assessment> {
    const response = await apiClient.post<{ data: Assessment }>(
      "/assessments",
      payload,
    );
    return response.data.data;
  },

  async updateAssessment(
    id: string,
    payload: UpdateAssessmentRequest,
  ): Promise<Assessment> {
    const response = await apiClient.put<{ data: Assessment }>(
      `/assessments/${id}`,
      payload,
    );
    return response.data.data;
  },

  async deleteAssessment(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/assessments/${id}`,
    );
    return { message: response.data?.message ?? "Assessment deleted" };
  },

  // ============ WORKFLOW METHODS ============

  async transitionAssessment(
    assessmentId: string,
    payload: AssessmentWorkflowRequest,
  ): Promise<AssessmentWorkflowResponse> {
    const response = await apiClient.post<{ data: AssessmentWorkflowResponse }>(
      `/assessments/${assessmentId}/workflow`,
      payload,
    );
    return response.data.data;
  },

  async transitionAssessmentResponse(
    responseId: string,
    payload: ResponseWorkflowRequest,
  ): Promise<ResponseWorkflowResponse> {
    const response = await apiClient.post<{ data: ResponseWorkflowResponse }>(
      `/assessment-responses/${responseId}/workflow`,
      payload,
    );
    return response.data.data;
  },

  // Requirement operations
  async updateRequirement(
    assessmentId: string,
    requirementId: string,
    payload: UpdateRequirementRequest,
  ): Promise<void> {
    await apiClient.put(
      `/assessments/${assessmentId}/requirements/${requirementId}`,
      payload,
    );
  },

  // ============ RESPONSE METHODS ============

  async updateResponse(
    responseId: string,
    data: UpdateRequirementRequest,
  ): Promise<AssessmentResponse> {
    const response = await apiClient.put<{ data: AssessmentResponse }>(
      `/assessment-responses/${responseId}`,
      data,
    );
    return response.data.data;
  },

  async getResponseHistory(responseId: string): Promise<ResponseHistoryItem[]> {
    const response = await apiClient.get<{ data: ResponseHistoryItem[] }>(
      `/assessment-responses/${responseId}/history`,
    );
    return response.data.data;
  },

  // Action Plan operations
  async createActionPlan(
    payload: CreateActionPlanRequest,
  ): Promise<ActionPlan> {
    const response = await apiClient.post<{ data: ActionPlan }>(
      "/assessment-action-plans",
      payload,
    );
    return response.data.data;
  },

  async listActionPlans(assessmentId: string): Promise<ActionPlan[]> {
    const response = await apiClient.get<{ data: ActionPlan[] }>(
      "/assessment-action-plans",
      {
        params: { assessmentId: assessmentId },
      },
    );
    return response.data.data;
  },

  async getActionPlans(params?: {
    assessmentResponseId?: string;
    assessmentId?: string;
  }): Promise<{ data: ActionPlan[] }> {
    const response = await apiClient.get<{ data: ActionPlan[] }>(
      "/assessment-action-plans",
      {
        params: params || {},
      },
    );
    return response.data;
  },

  async updateActionPlan(
    id: string,
    payload: UpdateActionPlanRequest,
  ): Promise<ActionPlan> {
    const response = await apiClient.put<{ data: ActionPlan }>(
      `/assessment-action-plans/${id}`,
      payload,
    );
    return response.data.data;
  },

  async deleteActionPlan(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/assessment-action-plans/${id}`,
    );
    return { message: response.data?.message ?? "Action plan deleted" };
  },

  async deleteActionPlanById(planId: string): Promise<void> {
    await apiClient.delete(`/assessment-action-plans/${planId}`);
  },
};
