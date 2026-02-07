// ============ ATTACHMENT TYPES ============

export interface Attachment {
  id: string;
  organizationId: string;
  name: string;
  mimeType: string;
  size: number;
  category: "documentation" | "evidence" | "reference_material" | null;
  description: string | null;
  createdById: string;
  createdByName: string | null;
  updatedById: string;
  createdAt: string;
  updatedAt: string;
  downloadUrl: string;
}

export interface AttachmentListResponse {
  data: Attachment[];
  meta: {
    currentPage: number;
    total: number;
    perPage: number;
    lastPage: number;
  };
}

export interface AttachmentFilters {
  organizationId?: string;
  category?: "documentation" | "evidence" | "reference_material";
  search?: string;
  page?: number;
  perPage?: number;
}

// ============ REQUEST TYPES ============

export interface CreateAttachmentRequest {
  file: File;
  category?: "documentation" | "evidence" | "reference_material";
  description?: string;
}

export interface UpdateAttachmentRequest {
  description?: string;
  category?: "documentation" | "evidence" | "reference_material";
}

export interface LinkAttachmentRequest {
  assessmentResponseId: string;
  attachmentId: string;
}

export interface UploadAndLinkRequest {
  assessmentResponseId: string;
  file: File;
  description?: string;
}

// ============ RESPONSE TYPES ============

export interface AttachmentResponse {
  data: Attachment;
}

export interface AttachmentMessageResponse {
  message: string;
}
