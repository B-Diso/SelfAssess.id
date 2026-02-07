import { apiClient } from "@/lib/api/client";
import type {
  Attachment,
  AttachmentListResponse,
  AttachmentFilters,
  CreateAttachmentRequest,
  UpdateAttachmentRequest,
  LinkAttachmentRequest,
  UploadAndLinkRequest,
  AttachmentResponse,
  AttachmentMessageResponse,
} from "../types/attachment.types";

export const attachmentApi = {
  // ============ ATTACHMENT CRUD ============

  async listAttachments(
    filters?: AttachmentFilters,
  ): Promise<AttachmentListResponse> {
    const response = await apiClient.get<AttachmentListResponse>(
      "/attachments",
      {
        params: filters,
      },
    );
    return response.data;
  },

  async getAttachment(id: string): Promise<Attachment> {
    const response = await apiClient.get<AttachmentResponse>(
      `/attachments/${id}`,
    );
    return response.data.data;
  },

  async uploadAttachment(data: CreateAttachmentRequest): Promise<Attachment> {
    const formData = new FormData();
    formData.append("file", data.file);
    if (data.category) {
      formData.append("category", data.category);
    }
    if (data.description) {
      formData.append("description", data.description);
    }

    const response = await apiClient.post<AttachmentResponse>(
      "/attachments",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      },
    );
    return response.data.data;
  },

  async updateAttachment(
    id: string,
    data: UpdateAttachmentRequest,
  ): Promise<Attachment> {
    const response = await apiClient.patch<AttachmentResponse>(
      `/attachments/${id}`,
      data,
    );
    return response.data.data;
  },

  async deleteAttachment(id: string): Promise<AttachmentMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/attachments/${id}`,
    );
    return { message: response.data?.message ?? "Attachment deleted" };
  },

  async downloadAttachment(id: string): Promise<string> {
    return `/api/attachments/${id}/download`;
  },

  // ============ BRIDGE OPERATIONS (Evidence Linking) ============

  async getResponseAttachments(responseId: string): Promise<Attachment[]> {
    const response = await apiClient.get<AttachmentListResponse>(
      `/attachment-links/assessment-response/${responseId}`,
    );
    console.log("getResponseAttachments response:", response.data);
    // Backend might return data directly in response.data or in response.data.data
    return Array.isArray(response.data)
      ? response.data
      : response.data.data || [];
  },

  async linkAttachment(
    data: LinkAttachmentRequest,
  ): Promise<AttachmentMessageResponse> {
    const response = await apiClient.post<{ message?: string }>(
      "/attachment-links/assessment-response",
      {
        assessment_response_id: data.assessmentResponseId,
        attachment_id: data.attachmentId,
      },
    );
    return { message: response.data?.message ?? "Attachment linked" };
  },

  async uploadAndLinkAttachment(
    data: UploadAndLinkRequest,
  ): Promise<AttachmentMessageResponse> {
    const formData = new FormData();
    formData.append("assessment_response_id", data.assessmentResponseId);
    formData.append("file", data.file);
    if (data.description) {
      formData.append("description", data.description);
    }

    const response = await apiClient.post<{ message?: string }>(
      "/attachment-links/assessment-response/upload",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      },
    );
    return {
      message: response.data?.message ?? "Attachment uploaded and linked",
    };
  },

  async unlinkAttachment(
    responseId: string,
    attachmentId: string,
  ): Promise<AttachmentMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/attachment-links/assessment-response/${responseId}/${attachmentId}`,
    );
    return { message: response.data?.message ?? "Attachment unlinked" };
  },
};
