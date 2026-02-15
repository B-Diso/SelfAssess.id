<script setup lang="ts">
import { reactive, ref, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import { useRouter } from "vue-router";
import { Loader2, Plus, Search } from "lucide-vue-next";
import { useStandardStore } from "../stores/standardStore";
import { useAuth } from "@/features/auth/composables/useAuth";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import type {
  Standard,
  StandardType,
} from "../types/standard";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import StandardListTable from "../components/StandardListTable.vue";
import StandardCreateDialog from "../components/modal/StandardCreateDialog.vue";
import StandardEditDialog from "../components/modal/StandardEditDialog.vue";
import type { SortingState } from "@tanstack/vue-table";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";

const router = useRouter();
const store = useStandardStore();
const { isSuperAdmin } = useAuth();

// Only Super Admin can create standards
const canCreateStandard = isSuperAdmin;

type SortField =
  | "name"
  | "version"
  | "type"
  | "createdAt"
  | "updatedAt";

const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  type: "" as StandardType | "" | "all",
  isActive: undefined as boolean | undefined,
  sortBy: "updatedAt" as SortField,
  sortOrder: "desc" as "asc" | "desc",
  search: "",
});

// Debounced search with 300ms delay
watchDebounced(
  searchInput,
  (newValue) => {
    filters.search = newValue;
    filters.page = 1;
    syncFiltersToStore();
    store.updateSearchFilter(newValue);
    store.fetchStandards();
  },
  { debounce: 300 },
);

function syncFiltersToStore() {
  store.setFilters({
    type: filters.type,
    isActive: filters.isActive,
    search: filters.search,
    perPage: filters.perPage,
    page: filters.page,
    sortBy: filters.sortBy,
    sortOrder: filters.sortOrder,
  });
}

const totalItems = computed(() => store.pagination.total);
const totalPages = computed(() => store.pagination.lastPage);

const tableSorting = ref<SortingState>([]);

const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedStandard = ref<Standard | null>(null);
const editingStandard = ref<Standard | null>(null);
const isDeleting = ref(false);
const isUpdating = ref(false);
const lastErrorToast = ref<string | null>(null);

const typeOptions = [
  { value: "all", label: "All Types" },
  { value: "internal", label: "Internal" },
  { value: "regulatory", label: "Regulatory" },
  { value: "standard", label: "Standard" },
  { value: "bestPractice", label: "Best Practice" },
  { value: "other", label: "Other" },
];

function handleEdit(standard: Standard) {
  editingStandard.value = standard;
  showEditDialog.value = true;
}

function handleDelete(standard: Standard) {
  selectedStandard.value = standard;
  showDeleteDialog.value = true;
}

function handleView(standard: Standard) {
  router.push(`/standards/${standard.id}`);
}

watch(
  () => [filters.sortBy, filters.sortOrder] as [SortField, "asc" | "desc"],
  ([sortBy, sortOrder]) => {
    tableSorting.value = [
      {
        id: sortBy,
        desc: sortOrder === "desc",
      },
    ];
  },
  { immediate: true },
);

watch(
  () => store.pagination.currentPage,
  (currentPage) => {
    if (
      typeof currentPage === "number" &&
      currentPage > 0 &&
      currentPage !== filters.page
    ) {
      filters.page = currentPage;
    }
  },
);

watch(showDeleteDialog, (open) => {
  if (!open) {
    selectedStandard.value = null;
    isDeleting.value = false;
  }
});

watch(showEditDialog, (open) => {
  if (!open) {
    editingStandard.value = null;
    isUpdating.value = false;
  }
});

watch(
  () => store.error,
  (message) => {
    if (message && message !== lastErrorToast.value) {
      toast.error(message);
      lastErrorToast.value = message;
    }
    if (!message) {
      lastErrorToast.value = null;
    }
  },
);

async function handleDeleteStandard() {
  if (!selectedStandard.value) return;

  isDeleting.value = true;

  const deletePromise = store.deleteStandard(selectedStandard.value.id);

  try {
    await toast.promise(deletePromise, {
      loading: "Deleting standard...",
      success: "Standard deleted successfully",
      error: (err: unknown) =>
        getApiErrorMessage(err, "Failed to delete standard"),
    });
    showDeleteDialog.value = false;
    selectedStandard.value = null;
  } catch (err) {
    console.error(err);
  } finally {
    isDeleting.value = false;
  }
}

function handlePageSizeChange(size: number) {
  filters.perPage = size;
  filters.page = 1;
  syncFiltersToStore();
  store.updatePagination(filters.page, filters.perPage);
  store.fetchStandards();
}

function handlePageChange(page: number) {
  filters.page = page;
  syncFiltersToStore();
  store.updatePagination(page, filters.perPage);
  store.fetchStandards();
}

function handleSortingChange(sortingState: SortingState) {
  const [sort] = sortingState;

  if (!sort) {
    filters.sortBy = "updatedAt";
    filters.sortOrder = "desc";
    filters.page = 1;
    syncFiltersToStore();
    store.updateSorting(filters.sortBy, filters.sortOrder);
    store.fetchStandards();
    return;
  }

  const validSortFields: SortField[] = [
    "name",
    "version",
    "type",
    "updatedAt",
  ];

  if (validSortFields.includes(sort.id as SortField)) {
    filters.sortBy = sort.id as SortField;
    filters.sortOrder = sort.desc ? "desc" : "asc";
    filters.page = 1;
    syncFiltersToStore();
    store.updateSorting(filters.sortBy, filters.sortOrder);
    store.fetchStandards();
  }
}

function handleTypeChange(value: unknown) {
  if (typeof value === "string") {
    filters.type = value === "all" ? "" : (value as StandardType | "");
    filters.page = 1;
    syncFiltersToStore();
    store.updateTypeFilter(filters.type);
    store.fetchStandards();
  }
}

function handleActiveChange(value: unknown) {
  filters.isActive =
    value === "" ? undefined : value === "true" ? true : value === "false" ? false : undefined;
  filters.page = 1;
  syncFiltersToStore();
  store.updateActiveFilter(filters.isActive);
  store.fetchStandards();
}

const resetFilters = () => {
  searchInput.value = "";
  filters.type = "";
  filters.isActive = undefined;
  filters.search = "";
  filters.page = 1;
  syncFiltersToStore();
  store.resetFilters();
  store.fetchStandards();
};

async function handleCreateStandard(data: {
  name: string;
  version: string;
  type?: StandardType;
  description?: string;
  isActive?: boolean;
}) {
  const createPromise = store.createStandard({
    name: data.name,
    version: data.version,
    type: data.type as StandardType,
    description: data.description,
    isActive: data.isActive,
  });

  try {
    await toast.promise(createPromise, {
      loading: "Creating standard...",
      success: "Standard created successfully",
      error: (err: unknown) =>
        getApiErrorMessage(err, "Failed to create standard"),
    });
    showCreateDialog.value = false;
  } catch (err) {
    console.error(err);
  }
}

async function handleUpdateStandard(data: {
  name: string;
  version: string;
  type?: StandardType;
  description?: string;
}) {
  if (!editingStandard.value) return;

  isUpdating.value = true;

  const updatePromise = store.updateStandard(editingStandard.value.id, {
    name: data.name,
    version: data.version,
    type: data.type,
    description: data.description,
  });

  try {
    await toast.promise(updatePromise, {
      loading: "Updating standard...",
      success: "Standard updated successfully",
      error: (err: unknown) =>
        getApiErrorMessage(err, "Failed to update standard"),
    });
    showEditDialog.value = false;
    editingStandard.value = null;
  } catch (err) {
    console.error(err);
  } finally {
    isUpdating.value = false;
  }
}

// Function to close the dialog
function closeDialog() {
  showDeleteDialog.value = false;
}

// Initial fetch
syncFiltersToStore();
store.fetchStandards();
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Standards Management"
      description="Manage compliance frameworks, regulatory requirements, and internal best practices."
    >
      <template #actions>
        <Button
          v-if="canCreateStandard"
          size="sm"
          class="h-8"
          @click="showCreateDialog = true"
        >
          <Plus class="h-3.5 w-3.5 mr-2" />
          Create Standard
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search by name or version..."
        :show-reset="
          filters.type !== '' ||
          filters.isActive !== undefined ||
          searchInput !== ''
        "
        @reset="resetFilters"
      >
        <template #filters>
          <div class="flex flex-wrap items-center gap-2">
            <Select
              :model-value="filters.type"
              @update:model-value="handleTypeChange"
            >
              <SelectTrigger class="h-9 w-36 bg-white border-slate-200">
                <SelectValue placeholder="Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="option in typeOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>

            <Select
              :model-value="
                filters.isActive === undefined
                  ? ''
                  : String(filters.isActive)
              "
              @update:model-value="handleActiveChange"
            >
              <SelectTrigger class="h-9 w-36 bg-white border-slate-200">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="true">Active</SelectItem>
                <SelectItem value="false">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </template>
      </TableToolbar>

      <!-- Table Section -->
      <div class="flex-1 min-h-0 p-3 flex flex-col">
        <div
          v-if="store.loading"
          class="h-full flex items-center justify-center"
        >
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p
              class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
            >
              Loading standards...
            </p>
          </div>
        </div>

        <div
          v-else-if="store.error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">
              Error loading standards
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              Please try again or contact support.
            </p>
            <Button
              variant="outline"
              size="sm"
              class="mt-4"
              @click="store.fetchStandards()"
              >Retry</Button
            >
          </div>
        </div>

        <div
          v-else-if="!store.standards.length"
          class="h-full flex flex-col items-center justify-center gap-3 py-12"
        >
          <div class="p-4 rounded-full bg-slate-50">
            <Search class="h-8 w-8 text-slate-300" />
          </div>
          <div class="text-center">
            <p class="text-sm font-semibold text-slate-900">
              No standards found
            </p>
            <p class="text-xs text-slate-500 max-w-[200px] mt-1">
              Try adjusting your filters or search keywords.
            </p>
          </div>
          <Button variant="outline" size="sm" class="mt-2" @click="resetFilters"
            >Clear all filters</Button
          >
        </div>

        <StandardListTable
          v-else
          :standards="store.standards"
          :sorting="tableSorting"
          :page="filters.page"
          :page-size="filters.perPage"
          :total-items="totalItems"
          :total-pages="totalPages"
          @sorting-change="handleSortingChange"
          @page-size-change="handlePageSizeChange"
          @page-change="handlePageChange"
          @edit="handleEdit"
          @delete="handleDelete"
          @view="handleView"
        />
      </div>
    </div>

    <AlertDialog
      :open="showDeleteDialog"
      @open-change="(open: boolean) => (showDeleteDialog = open)"
    >
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Standard</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete
            <span class="font-medium">{{ selectedStandard?.name }}</span
            >? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="sm:justify-end gap-2">
          <AlertDialogCancel as-child>
            <Button 
            variant="outline" 
            :disabled="isDeleting"
            @click="closeDialog"
            >Cancel</Button>
          </AlertDialogCancel>
          <AlertDialogAction as-child>
            <Button
              variant="destructive"
              :disabled="isDeleting"
              @click="handleDeleteStandard"
            >
              <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
              {{ isDeleting ? "Deleting..." : "Delete" }}
            </Button>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <StandardCreateDialog
      v-model:open="showCreateDialog"
      :is-submitting="store.loading"
      @submit="handleCreateStandard"
    />

    <StandardEditDialog
      v-model:open="showEditDialog"
      :standard="editingStandard"
      :is-submitting="isUpdating"
      @submit="handleUpdateStandard"
    />
  </div>
</template>
