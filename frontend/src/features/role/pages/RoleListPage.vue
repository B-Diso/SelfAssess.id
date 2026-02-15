<script setup lang="ts">
import { ref, reactive, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import { Plus } from "lucide-vue-next";
import {
  useRoles,
  useCreateRole,
  useUpdateRole,
  useDeleteRole,
} from "../composables/useRoles";
import type {
  Role,
  CreateRoleRequest,
  UpdateRoleRequest,
} from "../types/role.types";
import { Button } from "@/components/ui/button";
import DataTable from "@/components/ui/DataTable.vue";
import { createColumns } from "../components/data-table/columns";
import CreateRoleDialog from "../components/create-role-dialog.vue";
import EditRoleDialog from "../components/edit-role-dialog.vue";
import DeleteRoleDialog from "../components/delete-role-dialog.vue";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import { usePermissions } from "@/composables/userPermissions";
import { PERMISSIONS } from "@/lib/constants";

const { hasPermission } = usePermissions();

const { data: roles, isLoading, error } = useRoles();
const createMutation = useCreateRole();
const updateMutation = useUpdateRole();
const deleteMutation = useDeleteRole();

// Dialog states
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedRole = ref<Role | null>(null);

// Available permissions (from backend format)
const availablePermissions = [
  "create-user",
  "view-users",
  "update-user",
  "delete-user",
  "transfer-user",
  "create-organization",
  "view-organizations",
  "update-organization",
  "delete-organization",
  "view-organization-members",
  "manage-roles",
  "assign-roles",
  "view-roles",
  "view-permissions",
  "review-assessments",
];

// Handlers for dropdown actions
function handleEdit(role: Role) {
  selectedRole.value = role;
  showEditDialog.value = true;
}

function handleDelete(role: Role) {
  selectedRole.value = role;
  showDeleteDialog.value = true;
}

// Pagination state (client-side for roles)
const filters = reactive({
  page: 1,
  perPage: 15,
});

// Search state
const searchInput = ref("");
const searchQuery = ref("");

// Debounced search with 300ms delay
watchDebounced(
  searchInput,
  (newValue) => {
    searchQuery.value = newValue;
    filters.page = 1;
  },
  { debounce: 300 },
);

// Filter roles based on search
const filteredRoles = computed(() => {
  const rolesList = roles.value || [];
  if (!searchQuery.value) return rolesList;

  const query = searchQuery.value.toLowerCase();
  return rolesList.filter(
    (role) =>
      role.name.toLowerCase().includes(query) ||
      role.guardName.toLowerCase().includes(query),
  );
});

// Data for data table
const tableData = computed(() => filteredRoles.value);
const totalItems = computed(() => tableData.value.length);
const totalPages = computed(() =>
  Math.max(1, Math.ceil(totalItems.value / filters.perPage)),
);

// Create columns with handlers
const tableColumns = computed(() =>
  createColumns({
    onEdit: handleEdit,
    onDelete: handleDelete,
  }),
);

// Pagination handlers
function handlePageSizeChange(size: number) {
  filters.perPage = size;
  filters.page = 1;
}

function handlePageChange(page: number) {
  filters.page = page;
}

// Form submissions
async function handleCreateRole(payload: CreateRoleRequest) {
  try {
    const response = await createMutation.mutateAsync(payload);
    toast.success(response.message ?? "Role created successfully");
    showCreateDialog.value = false;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to create role"));
  }
}

async function handleUpdateRole(payload: UpdateRoleRequest) {
  if (!selectedRole.value) return;

  try {
    const response = await updateMutation.mutateAsync({
      id: selectedRole.value.id,
      data: payload,
    });
    toast.success(response.message ?? "Role updated successfully");
    showEditDialog.value = false;
    selectedRole.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to update role"));
  }
}

async function handleDeleteRole() {
  if (!selectedRole.value) return;

  try {
    const response = await deleteMutation.mutateAsync(selectedRole.value.id);
    toast.success(response.message ?? "Role deleted successfully");
    showDeleteDialog.value = false;
    selectedRole.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete role"));
  }
}

watch(showEditDialog, (open) => {
  if (!open) {
    selectedRole.value = null;
  }
});

watch(showDeleteDialog, (open) => {
  if (!open) {
    selectedRole.value = null;
  }
});
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Roles & Permissions"
      description="Define platform-wide roles and manage their permission sets."
    >
      <template #actions>
        <Button
          v-if="hasPermission(PERMISSIONS.MANAGE_ROLES)"
          size="sm"
          class="h-8"
          @click="showCreateDialog = true"
        >
          <Plus class="h-3.5 w-3.5 mr-2" />
          Add Role
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search roles..."
        :show-reset="searchInput !== ''"
        @reset="searchInput = ''"
      />

      <!-- Table Section -->
      <div class="flex-1 min-h-0 p-3 flex flex-col">
        <div v-if="isLoading" class="h-full flex items-center justify-center">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p
              class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
            >
              Loading roles...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">Error loading roles</p>
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
    <CreateRoleDialog
      v-model:open="showCreateDialog"
      :is-submitting="createMutation.isPending.value"
      :available-permissions="availablePermissions"
      @submit="handleCreateRole"
    />

    <EditRoleDialog
      v-model:open="showEditDialog"
      :is-submitting="updateMutation.isPending.value"
      :role="selectedRole"
      :available-permissions="availablePermissions"
      @submit="handleUpdateRole"
    />

    <DeleteRoleDialog
      v-model:open="showDeleteDialog"
      :is-submitting="deleteMutation.isPending.value"
      :role-name="selectedRole?.name"
      @confirm="handleDeleteRole"
    />
  </div>
</template>
