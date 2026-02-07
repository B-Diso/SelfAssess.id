<script setup lang="ts">
import { ref, reactive, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import { Plus } from "lucide-vue-next";
import {
  useOrganizations,
  useCreateOrganization,
  useUpdateOrganization,
  useDeleteOrganization,
} from "../composables/useOrganizations";
import { useAuth } from "@/features/auth/composables/useAuth";
import { PERMISSIONS } from "@/lib/constants";
import type {
  Organization,
  CreateOrganizationRequest,
  UpdateOrganizationRequest,
} from "../types/organization.types";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import DataTable from "@/components/ui/DataTable.vue";
import { createColumns } from "../components/data-table/columns";
import CreateOrganizationDialog from "../components/create-organization-dialog.vue";
import EditOrganizationDialog from "../components/edit-organization-dialog.vue";
import DeleteOrganizationDialog from "../components/delete-organization-dialog.vue";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";

const { hasPermission } = useAuth();

const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  search: "",
  isActive: "" as "" | "active" | "inactive",
  sortBy: "created_at" as const,
  sortOrder: "desc" as const,
});

// Debounced search with 500ms delay
watchDebounced(
  searchInput,
  (newValue) => {
    filters.search = newValue;
    filters.page = 1;
  },
  { debounce: 300 },
);

// Watch for status filter changes
watch(
  () => filters.isActive,
  () => {
    filters.page = 1;
  },
);

const filtersRef = computed(() => ({ ...filters }));
const { data: organizations, isLoading, error } = useOrganizations(filtersRef);
const createMutation = useCreateOrganization();
const updateMutation = useUpdateOrganization();
const deleteMutation = useDeleteOrganization();

// Dialog states
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedOrganization = ref<Organization | null>(null);

// Handlers for dropdown actions
function handleEdit(org: Organization) {
  selectedOrganization.value = org;
  showEditDialog.value = true;
}

function handleDelete(org: Organization) {
  selectedOrganization.value = org;
  showDeleteDialog.value = true;
}

function handleViewMembers(org: Organization) {
  // TODO: Navigate to members page or open members dialog
  console.log("View members for:", org.name);
}

function handleToggleStatus(org: Organization) {
  // TODO: Implement toggle status
  console.log("Toggle status for:", org.name);
}

// Data for data table
const tableData = computed(() => organizations.value?.data || []);
const totalItems = computed(
  () => organizations.value?.meta?.total ?? tableData.value.length,
);
const totalPages = computed(() => organizations.value?.meta?.lastPage ?? 1);

// Create columns with handlers
const tableColumns = computed(() =>
  createColumns({
    onEdit: handleEdit,
    onDelete: handleDelete,
    onViewMembers: handleViewMembers,
    onToggleStatus: handleToggleStatus,
  }),
);

watch(showEditDialog, (open) => {
  if (!open) {
    selectedOrganization.value = null;
  }
});

watch(showDeleteDialog, (open) => {
  if (!open) {
    selectedOrganization.value = null;
  }
});

// Pagination handlers
function handlePageSizeChange(size: number) {
  filters.perPage = size;
}

function handlePageChange(page: number) {
  filters.page = page;
}

watch(
  () => filters.isActive,
  () => {
    filters.page = 1;
  },
);

// Form submissions
async function handleCreateOrganization(payload: CreateOrganizationRequest) {
  try {
    const response = await createMutation.mutateAsync(payload);
    toast.success(response.message ?? "Organization created successfully");
    showCreateDialog.value = false;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to create organization"));
  }
}

async function handleUpdateOrganization(payload: UpdateOrganizationRequest) {
  if (!selectedOrganization.value) return;

  const original = selectedOrganization.value;
  const updateData: UpdateOrganizationRequest = {};

  if (payload.name && payload.name !== original.name) {
    updateData.name = payload.name;
  }

  const normalizedPayloadDescription = payload.description ?? "";
  const normalizedOriginalDescription = original.description ?? "";
  if (normalizedPayloadDescription !== normalizedOriginalDescription) {
    updateData.description = payload.description;
  }

  if (typeof payload.isActive === "boolean") {
    updateData.isActive = payload.isActive;
  }

  try {
    const response = await updateMutation.mutateAsync({
      id: original.id,
      data: updateData,
    });
    toast.success(response.message ?? "Organization updated successfully");
    showEditDialog.value = false;
    selectedOrganization.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to update organization"));
  }
}

async function handleDeleteOrganization() {
  if (!selectedOrganization.value) return;

  try {
    const response = await deleteMutation.mutateAsync(
      selectedOrganization.value.id,
    );
    toast.success(response.message ?? "Organization deleted successfully");
    showDeleteDialog.value = false;
    selectedOrganization.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete organization"));
  }
}

function resetFilters() {
  searchInput.value = "";
  filters.search = "";
  filters.isActive = "";
  filters.page = 1;
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Organizations"
      description="Manage partner organizations and their details."
    >
      <template #actions>
        <Button
          v-if="hasPermission(PERMISSIONS.CREATE_ORGANIZATION)"
          size="sm"
          class="h-8"
          @click="showCreateDialog = true"
        >
          <Plus class="h-3.5 w-3.5 mr-2" />
          Add Organization
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search by name..."
        :show-reset="searchInput !== '' || filters.isActive !== ''"
        @reset="resetFilters"
      >
        <template #filters>
          <Select v-model="filters.isActive">
            <SelectTrigger class="h-9 w-40 bg-white border-slate-200">
              <SelectValue placeholder="All Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Status</SelectItem>
              <SelectItem value="active">Active Only</SelectItem>
              <SelectItem value="inactive">Inactive Only</SelectItem>
            </SelectContent>
          </Select>
        </template>
      </TableToolbar>

      <!-- Table Section -->
      <div class="flex-1 min-h-0 p-3 flex flex-col">
        <div v-if="isLoading" class="h-full flex items-center justify-center">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p
              class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
            >
              Loading organizations...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">
              Error loading organizations
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              Please check your connection and try again.
            </p>
            <Button variant="outline" size="sm" class="mt-4" @click="() => {}"
              >Retry</Button
            >
          </div>
        </div>

        <DataTable
          v-else
          :columns="tableColumns"
          :data="tableData"
          :page="filters.page"
          :page-size="filters.perPage"
          :total-items="totalItems"
          :total-pages="totalPages"
          :compact="true"
          @page-size-change="handlePageSizeChange"
          @page-change="handlePageChange"
        />
      </div>
    </div>

    <!-- Dialogs -->
    <CreateOrganizationDialog
      v-model:open="showCreateDialog"
      :is-submitting="createMutation.isPending.value"
      @submit="handleCreateOrganization"
    />

    <EditOrganizationDialog
      v-model:open="showEditDialog"
      :is-submitting="updateMutation.isPending.value"
      :organization="selectedOrganization"
      @submit="handleUpdateOrganization"
    />

    <DeleteOrganizationDialog
      v-model:open="showDeleteDialog"
      :is-submitting="deleteMutation.isPending.value"
      :organization-name="selectedOrganization?.name"
      @confirm="handleDeleteOrganization"
    />
  </div>
</template>
