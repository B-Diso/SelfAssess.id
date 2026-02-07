import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import { computed } from "vue";
import { attachmentApi } from "../api/attachmentApi";
import type { AttachmentFilters } from "@/features/assessment/types/attachment.types";
import type { Ref } from "vue";

export function useAttachments(filters: Ref<AttachmentFilters>) {
  const queryKey = computed(() => [
    "attachments",
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.category,
  ] as const);

  return useQuery({
    queryKey: queryKey,
    queryFn: async () => {
      try {
        return await attachmentApi.list(filters.value);
      } catch (error) {
        console.error("Failed to fetch attachments:", error);
        throw error;
      }
    },
  });
}

export function useUploadAttachment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({
      file,
      category,
      description,
    }: {
      file: File;
      category?: string;
      description?: string;
    }) => attachmentApi.upload(file, category, description),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["attachments"] });
    },
  });
}

export function useDeleteAttachment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => attachmentApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["attachments"] });
    },
  });
}

export function useResponseAttachments(responseId: Ref<string>) {
  return useQuery({
    queryKey: ["response-attachments", responseId],
    queryFn: () => attachmentApi.getResponseAttachments(responseId.value),
    enabled: () => !!responseId.value,
  });
}

export function useUploadAndLinkAttachment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (data: {
      assessmentResponseId: string;
      file: File;
      description?: string;
    }) => attachmentApi.uploadAndLinkAttachment(data),
    onSuccess: (_, data) => {
      queryClient.invalidateQueries({
        queryKey: ["response-attachments", data.assessmentResponseId],
      });
      queryClient.invalidateQueries({ queryKey: ["attachments"] });
    },
  });
}

export function useLinkAttachment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (data: {
      assessmentResponseId: string;
      attachmentId: string;
    }) => attachmentApi.linkAttachment(data),
    onSuccess: (_, data) => {
      queryClient.invalidateQueries({
        queryKey: ["response-attachments", data.assessmentResponseId],
      });
    },
  });
}

export function useUnlinkAttachment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (data: {
      responseId: string;
      attachmentId: string;
    }) => attachmentApi.unlinkAttachment(data.responseId, data.attachmentId),
    onSuccess: (_, data) => {
      queryClient.invalidateQueries({
        queryKey: ["response-attachments", data.responseId],
      });
    },
  });
}
