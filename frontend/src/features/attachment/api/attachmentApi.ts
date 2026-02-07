import { apiClient } from "@/lib/api/client";
import type {
  Attachment,
  AttachmentFilters,
  AttachmentListResponse,
} from "@/features/assessment/types/attachment.types";
import type { ApiMessageResponse } from "@/types/api.types";

export const attachmentApi = {
  // ============ ATTACHMENT CRUD ============

  async list(filters?: AttachmentFilters): Promise<AttachmentListResponse> {
    const response = await apiClient.get<AttachmentListResponse>(
      "/attachments",
      {
        params: filters,
      },
    );
    return response.data;
  },

  async listAttachments(
    filters?: AttachmentFilters,
  ): Promise<AttachmentListResponse> {
    return this.list(filters);
  },

  async show(id: string): Promise<Attachment> {
    const response = await apiClient.get<{ data: Attachment }>(
      `/attachments/${id}`,
    );
    return response.data.data;
  },

  async upload(
    file: File,
    category?: string,
    description?: string,
  ): Promise<Attachment> {
    const formData = new FormData();
    formData.append("file", file);
    if (category) formData.append("category", category);
    if (description) formData.append("description", description);

    const response = await apiClient.post<{ data: Attachment }>(
      "/attachments",
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      },
    );
    return response.data.data;
  },

  async uploadAttachment(data: {
    file: File;
    category?: string;
    description?: string;
  }): Promise<Attachment> {
    return this.upload(data.file, data.category, data.description);
  },

  async delete(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message: string }>(
      `/attachments/${id}`,
    );
    return response.data;
  },

  async download(id: string): Promise<void> {
    window.open(
      `${import.meta.env.VITE_API_URL}/attachments/${id}/download`,
      "_blank",
    );
  },

  // ============ BRIDGE OPERATIONS (Assessment Response Linking) ============

  async getResponseAttachments(
    responseId: string,
  ): Promise<Attachment[]> {
    const response = await apiClient.get<AttachmentListResponse>(
      `/attachment-links/assessment-response/${responseId}`,
    );
    return response.data.data;
  },

  async uploadAndLinkAttachment(data: {
    assessmentResponseId: string;
    file: File;
    description?: string;
  }): Promise<ApiMessageResponse> {
    const formData = new FormData();
    formData.append("assessment_response_id", data.assessmentResponseId);
    formData.append("file", data.file);
    if (data.description) {
      formData.append("description", data.description);
    }

    const response = await apiClient.post<{ message: string }>(
      "/attachment-links/assessment-response/upload",
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      },
    );
    return response.data;
  },

  async linkAttachment(data: {
    assessmentResponseId: string;
    attachmentId: string;
  }): Promise<ApiMessageResponse> {
    const response = await apiClient.post<{ message: string }>(
      "/attachment-links/assessment-response",
      {
        assessment_response_id: data.assessmentResponseId,
        attachment_id: data.attachmentId,
      },
    );
    return response.data;
  },

  async unlinkAttachment(
    responseId: string,
    attachmentId: string,
  ): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message: string }>(
      `/attachment-links/assessment-response/${responseId}/${attachmentId}`,
    );
    return response.data;
  },
};
