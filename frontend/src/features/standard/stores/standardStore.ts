/**
 * Standard Store
 *
 * Pinia store for managing Standards, Sections, and Requirements
 * Following the Composition API pattern from assessmentStore.ts
 */

import { defineStore } from "pinia";
import { ref, computed } from "vue";
import type {
  Standard,
  StandardSection,
  StandardRequirement,
  StandardFilters,
  CreateStandardRequest,
  UpdateStandardRequest,
  CreateSectionRequest,
  CreateRequirementRequest,
} from "../types/standard";
import { standardApi, sectionApi, requirementApi } from "../api/standardApi";

export const useStandardStore = defineStore("standard", () => {
  // ============ STATE ============

  // Core data
  const standards = ref<Standard[]>([]);
  const currentStandard = ref<Standard | null>(null);
  const sections = ref<StandardSection[]>([]);
  const requirements = ref<StandardRequirement[]>([]);

  // UI state
  const loading = ref(false);
  const error = ref<string | null>(null);
  const expandedNodeIds = ref<string[]>([]);
  const expandedNodes = computed(() => new Set(expandedNodeIds.value));
  const selectedItemId = ref<string | null>(null);

  // Filter and pagination state
  const filters = ref<StandardFilters>({
    type: "",
    isActive: undefined,
    search: "",
    perPage: 15,
    page: 1,
    sortBy: "updatedAt",
    sortOrder: "desc",
  });

  const pagination = ref({
    total: 0,
    lastPage: 1,
    currentPage: 1,
    perPage: 15,
  });

  // ============ GETTERS/COMPUTED ============

  const activeStandards = computed(() =>
    standards.value.filter((s) => s.isActive),
  );

  const inactiveStandards = computed(() =>
    standards.value.filter((s) => !s.isActive),
  );

  const treeData = computed(() => sections.value);

  const hasStandards = computed(() => standards.value.length > 0);

  const hasCurrentStandard = computed(() => currentStandard.value !== null);

  // ============ ACTIONS ============

  // ----- Standards CRUD -----

  async function fetchStandards() {
    loading.value = true;
    error.value = null;
    try {
      const response = await standardApi.getStandards(filters.value);
      standards.value = response.data.map(normalizeStandard);
      pagination.value = {
        total: response.meta.total,
        lastPage: response.meta.lastPage,
        currentPage: response.meta.currentPage,
        perPage: response.meta.perPage,
      };
    } catch (e) {
      error.value = "Failed to fetch standards";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function fetchStandard(id: string) {
    loading.value = true;
    error.value = null;
    try {
      const response = await standardApi.getStandard(id);
      const normalized = normalizeStandard(response.data);
      currentStandard.value = normalized;
      return normalized;
    } catch (e) {
      error.value = "Failed to fetch standard";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function createStandard(data: CreateStandardRequest) {
    loading.value = true;
    error.value = null;
    try {
      const response = await standardApi.createStandard(data);
      const normalized = normalizeStandard(response.data);
      standards.value.unshift(normalized);
      return normalized;
    } catch (e) {
      error.value = "Failed to create standard";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function updateStandard(id: string, data: UpdateStandardRequest) {
    loading.value = true;
    error.value = null;
    try {
      const response = await standardApi.updateStandard(id, data);
      const normalized = normalizeStandard(response.data);

      // Update in standards list
      const index = standards.value.findIndex((s) => s.id === id);
      if (index !== -1) {
        standards.value[index] = normalized;
      }

      // Update current standard if it's the same
      if (currentStandard.value?.id === id) {
        currentStandard.value = normalized;
      }

      return normalized;
    } catch (e) {
      error.value = "Failed to update standard";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function deleteStandard(id: string) {
    loading.value = true;
    error.value = null;
    try {
      await standardApi.deleteStandard(id);
      standards.value = standards.value.filter((s) => s.id !== id);

      if (currentStandard.value?.id === id) {
        currentStandard.value = null;
      }
    } catch (e) {
      error.value = "Failed to delete standard";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // ----- Sections CRUD -----

  async function fetchSections(standardId: string) {
    loading.value = true;
    error.value = null;
    try {
      const response = await sectionApi.getSections(standardId);
      sections.value = response.data;
      return response.data;
    } catch (e) {
      error.value = "Failed to fetch sections";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function fetchSectionsTree(standardId: string, preserveState = true) {
    loading.value = true;
    error.value = null;
    // Preserve current expanded nodes and selection
    const previousExpanded = preserveState ? [...expandedNodeIds.value] : [];
    const previousSelected = preserveState ? selectedItemId.value : null;
    
    try {
      const flatNodes = await standardApi.getStandardTree(standardId);
      const tree = buildTreeFromFlatNodes(flatNodes);
      sections.value = tree;
      
      // Restore expanded nodes and selection after fetch
      if (preserveState) {
        expandedNodeIds.value = previousExpanded;
        selectedItemId.value = previousSelected;
      }
      
      return tree;
    } catch (e) {
      error.value = "Failed to fetch sections tree";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  function buildTreeFromFlatNodes(nodes: any[]): StandardSection[] {
    const map = new Map<string, any>();
    const roots: StandardSection[] = [];

    // First pass: create all section objects (ignoring requirements for now)
    nodes
      .filter((n) => n.type === "section")
      .forEach((node) => {
        map.set(node.id, {
          ...node,
          children: [],
          requirements: [],
        });
      });

    // Second pass: associate requirements and build section hierarchy
    nodes.forEach((node) => {
      if (node.type === "requirement") {
        const parentSection = map.get(node.parentId);
        if (parentSection) {
          parentSection.requirements.push({
            ...node,
            displayCode: node.code, // Map 'code' back to 'displayCode' for StandardRequirement compatibility
          });
        }
      } else if (node.type === "section") {
        const section = map.get(node.id);
        if (node.parentId && map.has(node.parentId)) {
          map.get(node.parentId).children.push(section);
        } else {
          roots.push(section);
        }
      }
    });

    return roots;
  }

  async function createSection(standardId: string, data: CreateSectionRequest) {
    loading.value = true;
    error.value = null;
    try {
      const response = await sectionApi.createSection(standardId, data);
      await fetchSectionsTree(standardId);
      return response.data;
    } catch (e) {
      error.value = "Failed to create section";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function updateSection(
    id: string,
    data: Partial<CreateSectionRequest>,
  ) {
    loading.value = true;
    error.value = null;
    try {
      const response = await sectionApi.updateSection(id, data);

      // Update nested tree (bukan hanya root level)
      updateSectionInTree(sections.value, id, response.data as StandardSection);

      return response.data;
    } catch (e) {
      error.value = "Failed to update section";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function deleteSection(id: string) {
    loading.value = true;
    error.value = null;
    try {
      await sectionApi.deleteSection(id);
      // Remove from sections array (and handle nested children)
      sections.value = removeSectionById(sections.value, id);
    } catch (e) {
      error.value = "Failed to delete section";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // ----- Requirements CRUD -----

  async function fetchRequirements(standardId?: string, sectionId?: string) {
    loading.value = true;
    error.value = null;
    try {
      const response = await requirementApi.getRequirements({
        standardId,
        standardSectionId: sectionId,
      });
      requirements.value = response.data;
      pagination.value = {
        total: response.meta.total,
        lastPage: response.meta.lastPage,
        currentPage: response.meta.currentPage,
        perPage: response.meta.perPage,
      };
      return response;
    } catch (e) {
      error.value = "Failed to fetch requirements";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function createRequirement(
    standardId: string,
    data: CreateRequirementRequest,
  ) {
    loading.value = true;
    error.value = null;
    try {
      const response = await requirementApi.createRequirement(standardId, data);
      return response.data;
    } catch (e) {
      error.value = "Failed to create requirement";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function updateRequirement(
    id: string,
    data: Partial<CreateRequirementRequest>,
  ) {
    loading.value = true;
    error.value = null;
    try {
      const response = await requirementApi.updateRequirement(id, data);

      // Update nested tree
      updateRequirementInTree(sections.value, id, response.data as StandardRequirement);

      // Also update in requirements array
      const index = requirements.value.findIndex((r) => r.id === id);
      if (index !== -1) {
        requirements.value[index] = response.data;
      }

      return response.data;
    } catch (e) {
      error.value = "Failed to update requirement";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function deleteRequirement(id: string) {
    loading.value = true;
    error.value = null;
    try {
      await requirementApi.deleteRequirement(id);
      requirements.value = requirements.value.filter((r) => r.id !== id);
    } catch (e) {
      error.value = "Failed to delete requirement";
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // ----- Filter Actions -----

  function setFilters(newFilters: Partial<StandardFilters>) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function updateTypeFilter(type: StandardFilters["type"]) {
    filters.value.type = type;
    filters.value.page = 1;
  }

  function updateActiveFilter(isActive: boolean | undefined) {
    filters.value.isActive = isActive;
    filters.value.page = 1;
  }

  function updateSearchFilter(search: string) {
    filters.value.search = search;
    filters.value.page = 1;
  }

  function updatePagination(page: number, perPage?: number) {
    filters.value.page = page;
    if (perPage) {
      filters.value.perPage = perPage;
    }
  }

  function updateSorting(sortBy: string, sortOrder: "asc" | "desc") {
    filters.value.sortBy = sortBy;
    filters.value.sortOrder = sortOrder;
  }

  function resetFilters() {
    filters.value = {
      type: "",
      isActive: undefined,
      search: "",
      perPage: 15,
      page: 1,
      sortBy: "updatedAt",
      sortOrder: "desc",
    };
  }

  // ----- Reset Action -----

  function reset() {
    standards.value = [];
    currentStandard.value = null;
    sections.value = [];
    requirements.value = [];
    loading.value = false;
    error.value = null;
    resetFilters();
  }

  // ============ HELPER FUNCTIONS ============

  function removeSectionById(
    nodes: StandardSection[],
    id: string,
  ): StandardSection[] {
    return nodes
      .filter((node) => node.id !== id)
      .map((node) => ({
        ...node,
        children: node.children
          ? removeSectionById(node.children, id)
          : undefined,
      }));
  }

  function normalizeStandard(standard: Standard): Standard {
    return {
      ...standard,
      isActive: standard.isActive ?? true,
      periodType: standard.periodType ?? null,
    };
  }

  // Helper: Update section in nested tree
  function updateSectionInTree(
    nodes: StandardSection[],
    id: string,
    data: StandardSection
  ): boolean {
    for (let i = 0; i < nodes.length; i++) {
      if (nodes[i]!.id === id) {
        nodes[i] = { ...nodes[i]!, ...data };
        return true;
      }
      if (nodes[i]!.children?.length) {
        if (updateSectionInTree(nodes[i]!.children!, id, data)) {
          return true;
        }
      }
    }
    return false;
  }

  // Helper: Update requirement in nested tree
  function updateRequirementInTree(
    nodes: StandardSection[],
    id: string,
    data: StandardRequirement
  ): boolean {
    for (const node of nodes) {
      if (node.requirements) {
        const reqIndex = node.requirements.findIndex((r) => r.id === id);
        if (reqIndex > -1) {
          node.requirements[reqIndex] = { ...node.requirements[reqIndex], ...data } as StandardRequirement;
          return true;
        }
      }
      if (node.children?.length) {
        if (updateRequirementInTree(node.children, id, data)) {
          return true;
        }
      }
    }
    return false;
  }

  // ============ RETURN ============

  return {
    // State
    standards,
    currentStandard,
    sections,
    requirements,
    loading,
    error,
    filters,
    pagination,

    // Getters
    activeStandards,
    inactiveStandards,
    treeData,
    hasStandards,
    hasCurrentStandard,
    expandedNodes,
    expandedNodeIds,
    selectedItemId,

    // Actions - Standards
    fetchStandards,
    fetchStandard,
    createStandard,
    updateStandard,
    deleteStandard,

    // Actions - Sections
    fetchSections,
    fetchSectionsTree,
    createSection,
    updateSection,
    deleteSection,

    // Actions - Requirements
    fetchRequirements,
    createRequirement,
    updateRequirement,
    deleteRequirement,

    // Actions - Filters
    setFilters,
    updateTypeFilter,
    updateActiveFilter,
    updateSearchFilter,
    updatePagination,
    updateSorting,
    resetFilters,

    // Actions - Reset
    reset,

    // Tree state management
    setSelectedItemId: (id: string | null) => {
      selectedItemId.value = id;
    },
    toggleExpandedNode: (id: string) => {
      const index = expandedNodeIds.value.indexOf(id);
      if (index > -1) {
        expandedNodeIds.value.splice(index, 1);
      } else {
        expandedNodeIds.value.push(id);
      }
    },
    expandAll: (ids: string[]) => {
      ids.forEach(id => {
        if (!expandedNodeIds.value.includes(id)) {
          expandedNodeIds.value.push(id);
        }
      });
    },
    collapseAll: () => {
      expandedNodeIds.value = [];
    },
  };
});
