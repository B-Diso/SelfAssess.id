import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import type { Ref } from "vue";
import { assessmentApi } from "../api/assessmentApi";
import type {
  AssessmentFilters,
  AssessmentWorkflowRequest,
  CreateActionPlanRequest,
  CreateAssessmentRequest,
  UpdateActionPlanRequest,
  UpdateAssessmentRequest,
  UpdateRequirementRequest,
} from "../types/assessment.types";
import { standardApi } from "@/features/standard/api/standardApi";

// Assessment queries
export function useAssessments(filters: Ref<AssessmentFilters>) {
  return useQuery({
    queryKey: ["quality-assessments", filters],
    queryFn: () => {
      // Remove 'all' and empty values before sending to API
      const cleanedFilters = { ...filters.value };
      if (cleanedFilters.status === "all") {
        delete cleanedFilters.status;
      }
      if (cleanedFilters.organizationId === "all") {
        delete cleanedFilters.organizationId;
      }
      if (!cleanedFilters.search || cleanedFilters.search.trim() === "") {
        delete cleanedFilters.search;
      }
      return assessmentApi.listAssessments(cleanedFilters);
    },
  });
}

export function useStandards() {
  return useQuery({
    queryKey: ["standards"],
    queryFn: () => standardApi.getStandards(),
  });
}

export function useAssessment(id: Ref<string>) {
  return useQuery({
    queryKey: ["quality-assessments", id],
    queryFn: () => assessmentApi.getAssessment(id.value),
    enabled: () => !!id.value,
  });
}

// Assessment mutations
export function useCreateAssessment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateAssessmentRequest) =>
      assessmentApi.createAssessment(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
    },
  });
}

export function useUpdateAssessment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateAssessmentRequest }) =>
      assessmentApi.updateAssessment(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
      queryClient.invalidateQueries({ queryKey: ["quality-assessments", id] });
    },
  });
}

export function useTransitionAssessment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({
      id,
      payload,
    }: {
      id: string;
      payload: AssessmentWorkflowRequest;
    }) => assessmentApi.transitionAssessment(id, payload),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
      queryClient.invalidateQueries({ queryKey: ["quality-assessments", id] });
    },
  });
}

export function useDeleteAssessment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => assessmentApi.deleteAssessment(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
    },
  });
}

// Requirement mutations
export function useUpdateRequirement() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({
      assessmentId,
      requirementId,
      data,
    }: {
      assessmentId: string;
      requirementId: string;
      data: UpdateRequirementRequest;
    }) => assessmentApi.updateRequirement(assessmentId, requirementId, data),
    onSuccess: (_, { assessmentId }) => {
      queryClient.invalidateQueries({
        queryKey: ["quality-assessments", assessmentId],
      });
    },
  });
}

// Action Plan operations
export function useCreateActionPlan() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateActionPlanRequest) =>
      assessmentApi.createActionPlan(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
      queryClient.invalidateQueries({ queryKey: ["action-plans"] });
    },
  });
}

export function useActionPlans(assessmentId: Ref<string>) {
  return useQuery({
    queryKey: ["action-plans", assessmentId],
    queryFn: () => assessmentApi.listActionPlans(assessmentId.value),
    enabled: () => !!assessmentId.value,
  });
}

export function useUpdateActionPlan() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateActionPlanRequest }) =>
      assessmentApi.updateActionPlan(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
      queryClient.invalidateQueries({ queryKey: ["action-plans"] });
    },
  });
}

export function useDeleteActionPlan() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => assessmentApi.deleteActionPlan(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["quality-assessments"] });
      queryClient.invalidateQueries({ queryKey: ["action-plans"] });
    },
  });
}
