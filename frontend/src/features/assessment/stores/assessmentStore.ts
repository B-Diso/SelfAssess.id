import { defineStore } from "pinia";
import { ref, computed } from "vue";
import type {
  AssessmentDetail,
  AssessmentTreeNode,
  AssessmentSummary,
  UpdateRequirementRequest,
} from "../types/assessment.types";
import { assessmentApi } from "../api/assessmentApi";
import { standardApi } from "@/features/standard/api/standardApi";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";

export const useAssessmentStore = defineStore("assessment", () => {
  // ============ STATE ============

  // Core assessment data
  const assessment = ref<AssessmentDetail | null>(null);

  // Tree navigation state
  const treeData = ref<AssessmentTreeNode[]>([]);
  const expandedNodes = ref<string[]>([]);
  const activeNodeId = ref<string | null>(null);
  const activeRequirementId = ref<string | null>(null);
  const selectedStandardId = ref<string | null>(null);

  // UI state
  const loading = ref(false);
  const treeLoading = ref(false);
  const saving = ref(false);
  const activeTab = ref<"response" | "evidence" | "action-plans" | "notes">(
    "response",
  );

  // Workflow state
  const workflowLoading = ref(false);
  const isDirty = ref(false);

  // Search/filter state
  const searchQuery = ref("");
  const filterStatus = ref<string | null>(null);

  // Confirmation dialog state for unsaved changes
  const showUnsavedChangesDialog = ref(false);
  const pendingNodeId = ref<string | null>(null);

  // ============ GETTERS/COMPUTED ============

  const activeNode = computed(() => {
    if (!activeNodeId.value || !treeData.value.length) return null;

    const result = findNodeById(treeData.value, activeNodeId.value);

    return result;
  });

  const activeResponse = computed(() => {
    return activeNode.value?.requirement;
  });

  const summary = computed<AssessmentSummary | null>(() => {
    if (!treeData.value.length) return null;

    const allNodes = flattenTree(treeData.value);
    const requirements = allNodes.filter((n) => n.type === "requirement");
    
    // Requirements workflow: active → pending_review → reviewed
    // Progress: active = 0%, pending_review = 50%, reviewed = 100%
    
    // Completed (100%) = requirement sudah di-review
    const completed = requirements.filter(
      (r) => r.requirement?.status === "reviewed",
    );
    
    // In Progress (50%) = user sudah selesai mengisi, menunggu reviewer
    const pendingReview = requirements.filter(
      (r) => r.requirement?.status === "pending_review",
    );
    
    // Not Started (0%) = masih active, belum selesai diisi
    const active = requirements.filter(
      (r) => r.requirement?.status === "active" || !r.requirement?.status,
    );

    return {
      overallScore: assessment.value?.overallScore || 0,
      completedRequirements: completed.length,
      totalRequirements: requirements.length,
      partialRequirements: pendingReview.length, // pending_review = 50%
      inProgressRequirements: active.length, // active = 0%
      pendingRequirements: active.length,
      actionPlansCount: 0,
      evidencesCount: 0,
    };
  });

  const progressPercentage = computed(() => {
    // Progress calculation based on requirement status:
    // - active = 0%
    // - pending_review = 50%
    // - reviewed = 100%
    if (!summary.value || summary.value.totalRequirements === 0) return 0;
    
    // Calculate weighted progress
    const reviewedCount = summary.value.completedRequirements * 100; // 100% each
    const pendingReviewCount = summary.value.partialRequirements * 50; // 50% each
    const totalProgress = reviewedCount + pendingReviewCount;
    
    return Math.round(
      totalProgress / summary.value.totalRequirements,
    );
  });

  // Get progress percentage for a single requirement based on its status
  const getRequirementProgress = (requirementStatus?: string): number => {
    // Requirement workflow: active → pending_review → reviewed
    // - active = 0%
    // - pending_review = 50%
    // - reviewed = 100%
    if (!requirementStatus || requirementStatus === "active") return 0;
    if (requirementStatus === "pending_review") return 50;
    if (requirementStatus === "reviewed") return 100;
    return 0; // Default for any unrecognized status
  };

  const filteredTreeData = computed(() => {
    if (!searchQuery.value) return treeData.value;

    const query = searchQuery.value.toLowerCase();
    const filterNode = (
      node: AssessmentTreeNode,
    ): AssessmentTreeNode | null => {
      const matches =
        node.title.toLowerCase().includes(query) ||
        node.code.toLowerCase().includes(query) ||
        node.description?.toLowerCase().includes(query);

      const filteredChildren = node.children
        ?.map(filterNode)
        .filter(Boolean) as AssessmentTreeNode[];

      if (matches || filteredChildren?.length) {
        return {
          ...node,
          children: filteredChildren || [],
          isExpanded: true,
        };
      }
      return null;
    };

    return treeData.value
      .map(filterNode)
      .filter(Boolean) as AssessmentTreeNode[];
  });

  const canSubmit = computed(() => {
    if (!assessment.value) return false;
    
    // Assessment must be in active status
    if (assessment.value.status !== "active") return false;
    
    // Must have requirements
    if (!summary.value?.totalRequirements) return false;
    
    // STRICT REQUIREMENT: All requirements must be REVIEWED before assessment can be submitted
    // Bottom-up workflow: requirements → assessment
    // Each requirement must be individually reviewed by a reviewer first
    const allReviewed = summary.value.completedRequirements === summary.value.totalRequirements;
    
    return allReviewed;
  });

  const canActivate = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "draft";
  });

  const canSaveProgress = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "active";
  });

  const canRequestChanges = computed(() => {
    if (!assessment.value) return false;
    return (
      assessment.value.status === "pending_review" ||
      assessment.value.status === "reviewed"
    );
  });

  const canFinalize = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "pending_finish";
  });

  const canSubmitForReview = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "active";
  });

  const canStartReview = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "pending_review";
  });

  const canRequestFinish = computed(() => {
    if (!assessment.value) return false;
    return assessment.value.status === "reviewed";
  });

  const isEditable = computed(() => {
    // Check assessment status
    if (!assessment.value) return false;
    const assessmentEditable =
      assessment.value.status === "draft" ||
      assessment.value.status === "active";

    if (!assessmentEditable) return false;

    // Check response status - only editable when status is "active"
    // Response workflow: active → pending_review → reviewed
    if (activeResponse.value) {
      return activeResponse.value.status === "active";
    }

    return true;
  });

  const canCancelReviewed = computed(() => {
    // Only Org Admin can cancel reviewed status (reviewed → active)
    // This is enforced by backend policy: AssessmentResponsePolicy@cancelReviewed
    if (!activeResponse.value) return false;
    return activeResponse.value.status === "reviewed";
  });

  // ============ ACTIONS ============

  async function fetchAssessment(id: string) {
    loading.value = true;
    try {
      assessment.value = await assessmentApi.getAssessment(id);
      if (assessment.value?.standardId) {
        selectedStandardId.value = assessment.value.standardId;
        await fetchTreeData(assessment.value.standardId);
      }
    } finally {
      loading.value = false;
    }
  }

  async function fetchTreeData(standardId: string) {
    treeLoading.value = true;
    // Simpan expanded nodes yang ada
    const currentExpanded = [...expandedNodes.value];
    
    try {
      const flatNodes = await standardApi.getStandardTree(standardId);
      treeData.value = buildTreeFromFlatNodes(flatNodes);
      
      // Auto-expand first two levels (tapi jangan hapus yang sudah di-expand)
      const expanded: string[] = [];
      treeData.value.forEach((node) => {
        if (node.children?.length) {
          expanded.push(node.id);
          node.children.forEach((child) => {
            if (child.children?.length) {
              expanded.push(child.id);
            }
          });
        }
      });
      // Merge dengan expanded yang sudah ada
      expandedNodes.value = [...new Set([...currentExpanded, ...expanded])];
    } finally {
      treeLoading.value = false;
    }
  }

  function buildTreeFromFlatNodes(nodes: any[]): AssessmentTreeNode[] {
    const map = new Map<string, AssessmentTreeNode>();
    const roots: AssessmentTreeNode[] = [];

    // First pass: create all section objects (ignoring requirements for now)
    nodes
      .filter((n) => n.type === "section")
      .forEach((node) => {
        map.set(node.id, {
          id: node.id,
          type: node.type as any,
          code: node.code,
          title: node.title,
          description: node.description,
          evidenceHint: node.evidenceHint,
          children: [],
        });
      });

    // Second pass: associate requirements and build section hierarchy
    nodes.forEach((node) => {
      if (node.type === "requirement") {
        const parentSection = map.get(node.parentId);
        if (parentSection) {
          // Find or create requirement from assessment response
          const response = assessment.value?.responses?.find(
            (r) => r.standardRequirementId === node.id,
          );

          const requirementNode: AssessmentTreeNode = {
            id: node.id,
            type: node.type as any,
            code: node.code,
            title: node.title,
            description: node.description,
            evidenceHint: node.evidenceHint,
            requirement: response,
            children: [],
          };

          parentSection.children!.push(requirementNode);
        }
      } else if (node.type === "section") {
        const section = map.get(node.id);
        if (section) {
          if (node.parentId && map.has(node.parentId)) {
            map.get(node.parentId)!.children!.push(section);
          } else {
            roots.push(section);
          }
        }
      }
    });

    return roots;
  }

  // ============ FIELD VALIDATION HELPERS ============

  /**
   * Validates and extracts a string field from the response
   * @param value - The value to validate
   * @param fallback - Fallback value if value is null/undefined/empty
   * @param fieldName - Name of the field for debugging purposes
   * @returns Validated string value
   */
  function validateAndExtractString(
    value: string | null | undefined,
    fallback: string | undefined,
    fieldName: string,
  ): string {
    // Debug log for missing fields
    if (value === null || value === undefined) {
      console.debug(
        `[${fieldName}] Field is null/undefined, using fallback:`,
        fallback,
      );
      return fallback ?? "";
    }

    // Check for empty string
    if (value.trim() === "") {
      console.debug(
        `[${fieldName}] Field is empty string, using fallback:`,
        fallback,
      );
      return fallback ?? "";
    }

    return value;
  }

  /**
   * Validates and extracts a number field from the response
   * @param value - The value to validate
   * @param fallback - Fallback value if value is null/undefined
   * @param fieldName - Name of the field for debugging purposes
   * @returns Validated number value or undefined
   */
  function validateAndExtractNumber(
    value: number | null | undefined,
    fallback: number | undefined,
    fieldName: string,
  ): number | undefined {
    if (value === null || value === undefined) {
      console.debug(
        `[${fieldName}] Field is null/undefined, using fallback:`,
        fallback,
      );
      return fallback;
    }

    return value;
  }

  /**
   * Enhanced helper to update node fields directly in treeData
   * Use this when you need to update fields after initial tree creation
   */
  function updateNodeFields(
    nodeId: string,
    updates: Partial<
      Pick<AssessmentTreeNode, "title" | "evidenceHint" | "weight">
    >,
  ): boolean {
    const node = findNodeById(treeData.value, nodeId);
    if (!node) {
      console.warn(`[updateNodeFields] Node not found: ${nodeId}`);
      return false;
    }

    // Apply updates with validation
    if (updates.title !== undefined) {
      node.title = validateAndExtractString(updates.title, node.title, "title");
    }
    if (updates.evidenceHint !== undefined) {
      node.evidenceHint = validateAndExtractString(
        updates.evidenceHint,
        node.evidenceHint,
        "evidenceHint",
      );
    }
    if (updates.weight !== undefined) {
      node.weight = validateAndExtractNumber(
        updates.weight,
        node.weight,
        "weight",
      );
    }

    return true;
  }

  /**
   * Debug helper to log entire tree structure with specific fields
   * Use this to troubleshoot if fields are missing
   */
  function debugTreeFields(
    fieldNames: Array<"title" | "evidenceHint" | "weight">,
  ): void {
    const allNodes = flattenTree(treeData.value);

    allNodes.forEach((node) => {
      fieldNames.reduce(
        (acc, field) => {
          acc[field] = node[field];
          return acc;
        },
        {} as Record<string, unknown>,
      );
    });

    console.groupEnd();
  }

  function toggleNode(nodeId: string) {
    const index = expandedNodes.value.indexOf(nodeId);
    if (index > -1) {
      expandedNodes.value.splice(index, 1);
    } else {
      expandedNodes.value.push(nodeId);
    }
    updateNodeExpandedState(treeData.value, nodeId, index === -1);
  }

  function updateNodeExpandedState(
    nodes: AssessmentTreeNode[],
    nodeId: string,
    isExpanded: boolean,
  ): boolean {
    for (const node of nodes) {
      if (node.id === nodeId) {
        node.isExpanded = isExpanded;
        return true;
      }
      if (node.children?.length) {
        if (updateNodeExpandedState(node.children, nodeId, isExpanded))
          return true;
      }
    }
    return false;
  }

  async function setActiveNode(nodeId: string): Promise<boolean> {
    if (isDirty.value) {
      // Show confirmation dialog for unsaved changes
      showUnsavedChangesDialog.value = true;
      pendingNodeId.value = nodeId;
      return false;
    }
    activeNodeId.value = nodeId;
    setNodeActiveState(treeData.value, nodeId);
    return true;
  }

  function confirmNavigation() {
    if (pendingNodeId.value) {
      isDirty.value = false;
      activeNodeId.value = pendingNodeId.value;
      setNodeActiveState(treeData.value, pendingNodeId.value);
      pendingNodeId.value = null;
    }
    showUnsavedChangesDialog.value = false;
  }

  function cancelNavigation() {
    pendingNodeId.value = null;
    showUnsavedChangesDialog.value = false;
  }

  function setNodeActiveState(nodes: AssessmentTreeNode[], nodeId: string) {
    for (const node of nodes) {
      node.isActive = node.id === nodeId;
      if (node.children?.length) {
        setNodeActiveState(node.children, nodeId);
      }
    }
  }

  async function updateResponse(
    _nodeId: string,
    data: UpdateRequirementRequest,
  ) {
    const node = activeNode.value;
    if (!node?.requirement) return;

    saving.value = true;
    const previousActiveNodeId = activeNodeId.value;
    // Simpan expanded nodes
    const previousExpanded = [...expandedNodes.value];

    try {
      await assessmentApi.updateResponse(node.requirement.id, data);
      toast.success("Requirement updated successfully");
      isDirty.value = false;

      if (assessment.value?.id) {
        await fetchAssessment(assessment.value.id);
        // Restore
        if (previousActiveNodeId) {
          activeNodeId.value = previousActiveNodeId;
        }
        expandedNodes.value = previousExpanded;
      }
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to update requirement"));
      throw err;
    } finally {
      saving.value = false;
    }
  }


  async function activateAssessment(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "active",
        note,
      });
      toast.success("Assessment activated successfully");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to activate assessment"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function submitForReview(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "pending_review",
        note,
      });
      toast.success("Assessment submitted for review");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to submit assessment"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function backToActive(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "active",
        note,
      });
      toast.success("Assessment returned to active");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to return assessment to active"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function cancelSubmission(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      // Cancel submission = go back to active
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "active",
        note: note || "Submission cancelled by user",
      });
      toast.success("Submission cancelled");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to cancel submission"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function requestFinish(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "pending_finish",
        note,
      });
      toast.success("Finish requested successfully");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to request finish"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function cancelFinishRequest(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      // Cancel finish request = go back to reviewed
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "reviewed",
        note: note || "Finish request cancelled",
      });
      toast.success("Finish request cancelled");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to cancel finish request"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function reactivateAssessment(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "active",
        note,
      });
      toast.success("Assessment reactivated");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to reactivate assessment"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function saveDraft() {
    // Save draft functionality - mainly clears the dirty flag
    // Actual data is auto-saved through updateResponse
    isDirty.value = false;
    return Promise.resolve();
  }

  async function startReview(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "reviewed",
        note,
      });
      toast.success("Assessment review started");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to start review"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function requestChanges(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "active",
        note,
      });
      toast.success("Changes requested successfully");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to request changes"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  async function finalize(note?: string) {
    if (!assessment.value?.id) return;
    workflowLoading.value = true;
    try {
      await assessmentApi.transitionAssessment(assessment.value.id, {
        status: "finished",
        note,
      });
      toast.success("Assessment finalized successfully");
      await fetchAssessment(assessment.value.id);
    } catch (err: unknown) {
      toast.error(getApiErrorMessage(err, "Failed to finalize assessment"));
      throw err;
    } finally {
      workflowLoading.value = false;
    }
  }

  // ============ RESPONSE WORKFLOW METHODS ============

  /**
   * Mark requirement as complete (user finished filling)
   * Transition: active → pending_review
   */
  async function markRequirementComplete(responseId: string, note?: string) {
    return transitionResponse(responseId, "pending_review", note);
  }

  /**
   * Review and approve requirement
   * Transition: pending_review → reviewed
   */
  async function reviewRequirement(responseId: string, note?: string) {
    return transitionResponse(responseId, "reviewed", note);
  }

  /**
   * Return requirement to user for revision
   * Transition: pending_review → active
   * Permission: User + Org Admin
   */
  async function returnRequirement(responseId: string, note?: string) {
    return transitionResponse(responseId, "active", note);
  }

  /**
   * Cancel reviewed requirement and return to active
   * Transition: reviewed → active
   * Permission: Org Admin ONLY
   */
  async function cancelReviewedRequirement(responseId: string, note?: string) {
    return transitionResponse(responseId, "active", note);
  }

  async function transitionResponse(
    responseId: string,
    status: string,
    note?: string
  ) {
    workflowLoading.value = true;
    try {
      const result = await assessmentApi.transitionAssessmentResponse(
        responseId,
        {
          status: status as any,
          note,
        }
      );

      // Show appropriate toast based on status transition
      if (status === "pending_review") {
        toast.success("Requirement submitted for review");
      } else if (status === "reviewed") {
        toast.success("Requirement reviewed successfully");
      } else if (status === "active") {
        toast.success("Requirement returned for revision");
      }

      // Update local response status
      if (assessment.value?.responses) {
        const responseIndex = assessment.value.responses.findIndex(
          (r) => r.id === responseId
        );
        if (responseIndex !== -1) {
          assessment.value.responses[responseIndex]!.status =
            result.response.status;
        }
      }

      // Refresh full assessment to sync all data
      if (assessment.value?.id) {
        await fetchAssessment(assessment.value.id);
      }
    } catch (error) {
      console.error("Failed to transition response:", error);
      toast.error(getApiErrorMessage(error, "Failed to update requirement status"));
      throw error;
    } finally {
      workflowLoading.value = false;
    }
  }

  function reset() {
    assessment.value = null;
    treeData.value = [];
    expandedNodes.value = [];
    activeNodeId.value = null;
    activeRequirementId.value = null;
    selectedStandardId.value = null;
    isDirty.value = false;
    searchQuery.value = "";
    filterStatus.value = null;
    showUnsavedChangesDialog.value = false;
    pendingNodeId.value = null;
  }

  // ============ HELPER FUNCTIONS ============

  function findNodeById(
    nodes: AssessmentTreeNode[],
    id: string,
  ): AssessmentTreeNode | null {
    if (!nodes || !nodes.length || !id) {
      return null;
    }

    // DEBUG: Log all node IDs in the entire tree
    const allNodeIds: string[] = [];
    const collectAllIds = (nodeList: AssessmentTreeNode[]) => {
      for (const n of nodeList) {
        allNodeIds.push(n.id);
        if (n.children?.length) {
          collectAllIds(n.children);
        }
      }
    };
    collectAllIds(nodes);

    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      if (!node) continue;

      if (node.id === id) return node;

      if (node.children?.length) {
        const found = findNodeById(node.children, id);
        if (found) return found;
      }
    }

    return null;
  }

  function findNodeByResponseId(
    nodes: AssessmentTreeNode[],
    responseId: string,
  ): AssessmentTreeNode | null {
    for (const node of nodes) {
      // Find by response ID (used in uploadEvidence, linkEvidence, etc.)
      if (node.requirement?.id === responseId) return node;
      if (node.children?.length) {
        const found = findNodeByResponseId(node.children, responseId);
        if (found) return found;
      }
    }
    return null;
  }

  function findNodeByRequirementId(
    nodes: AssessmentTreeNode[],
    requirementId: string,
  ): AssessmentTreeNode | null {
    for (const node of nodes) {
      // Find by standardRequirementId (the requirement ID from standard)
      if (node.requirement?.standardRequirementId === requirementId)
        return node;
      if (node.children?.length) {
        const found = findNodeByRequirementId(node.children, requirementId);
        if (found) return found;
      }
    }
    return null;
  }

  function flattenTree(nodes: AssessmentTreeNode[]): AssessmentTreeNode[] {
    return nodes.reduce((acc, node) => {
      acc.push(node);
      if (node.children?.length) {
        acc.push(...flattenTree(node.children));
      }
      return acc;
    }, [] as AssessmentTreeNode[]);
  }

  // ============ RETURN ============

  return {
    // State
    assessment,
    treeData,
    expandedNodes,
    activeNodeId,
    activeRequirementId,
    selectedStandardId,
    loading,
    treeLoading,
    saving,
    activeTab,
    workflowLoading,
    isDirty,
    searchQuery,
    filterStatus,
    showUnsavedChangesDialog,
    pendingNodeId,

    // Computed
    activeNode,
    activeResponse,
    summary,
    progressPercentage,
    filteredTreeData,
    canSubmit,
    canActivate,
    canSaveProgress,
    canRequestChanges,
    canFinalize,
    canSubmitForReview,
    canStartReview,
    canRequestFinish,
    canCancelReviewed,
    isEditable,

    // Actions
    fetchAssessment,
    fetchTreeData,
    toggleNode,
    setActiveNode,
    confirmNavigation,
    cancelNavigation,
    updateResponse,
    activateAssessment,
    submitForReview,
    backToActive,
    cancelSubmission,
    reactivateAssessment,
    saveDraft,
    startReview,
    requestChanges,
    requestFinish,
    cancelFinishRequest,
    finalize,
    // Response workflow actions
    markRequirementComplete,
    reviewRequirement,
    returnRequirement,
    cancelReviewedRequirement,
    transitionResponse,
    reset,

    // Helper functions
    findNodeByResponseId,
    findNodeByRequirementId,
    findNodeById,
    updateNodeFields,
    debugTreeFields,
    getRequirementProgress,
  };
});
