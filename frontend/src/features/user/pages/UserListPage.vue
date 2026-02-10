<script setup lang="ts">
import { ref, reactive, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import type { SortingState } from "@tanstack/vue-table";
import { Plus, Loader2 } from "lucide-vue-next";
import {
  useUsers,
  useCreateUser,
  useUpdateUser,
  useDeleteUser,
  useAssignRole,
  useUpdateRole,
} from "../composables/useUsers";
import { useAuth } from "@/features/auth/composables/useAuth";
import { PERMISSIONS, ROLES } from "@/lib/constants";
import type {
  User,
  CreateUserRequest,
  UpdateUserRequest,
} from "../types/user.types";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import DataTable from "@/components/ui/DataTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import { createColumns } from "../components/data-table/columns";
import CreateUserDialog from "../components/create-user-dialog.vue";
import EditUserDialog from "../components/edit-user-dialog.vue";
import DeleteUserDialog from "../components/delete-user-dialog.vue";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";

const { hasPermission } = useAuth();

type SortField = "name" | "email" | "createdAt";

const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  search: "",
  role: "" as string,
  sortBy: "createdAt" as SortField,
  sortOrder: "desc" as "asc" | "desc",
});

const filtersRef = computed(() => ({ ...filters }));
const { data: users, isLoading, error } = useUsers(filtersRef);
const createMutation = useCreateUser();
const updateMutation = useUpdateUser();
const deleteMutation = useDeleteUser();
const assignRoleMutation = useAssignRole();
const updateRoleMutation = useUpdateRole();
const tableSorting = ref<SortingState>([]);

// Dialog states
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignRoleDialog = ref(false);
const selectedUser = ref<User | null>(null);

const assignRoleForm = reactive({
  role: "" as "" | "organization_admin" | "organization_user",
});

// Handlers for dropdown actions
function handleEdit(user: User) {
  selectedUser.value = user;
  showEditDialog.value = true;
}

function handleDelete(user: User) {
  selectedUser.value = user;
  showDeleteDialog.value = true;
}

function handleAssignRole(user: User) {
  selectedUser.value = user;
  const existingRole = user.roles[0] || "";
  const allowedRoles: ("organization_admin" | "organization_user")[] = [ROLES.ORGANIZATION_ADMIN, ROLES.ORGANIZATION_USER];
  assignRoleForm.role = existingRole && allowedRoles.includes(existingRole as any) ? (existingRole as any) : ROLES.ORGANIZATION_USER;
  showAssignRoleDialog.value = true;
}

// Data for data table
const tableData = computed(() => users.value?.data || []);
const totalItems = computed(
  () => users.value?.meta?.total ?? tableData.value.length,
);
const totalPages = computed(() => users.value?.meta?.lastPage ?? 1);

const roleFilterOptions = [
  ROLES.ORGANIZATION_ADMIN,
  ROLES.ORGANIZATION_USER,
] as const;

const createUserRoleOptions = [
  { label: "Organization Admin", value: ROLES.ORGANIZATION_ADMIN },
  { label: "Organization User", value: ROLES.ORGANIZATION_USER },
];

const tableColumns = computed(() =>
  createColumns(
    {
      onEdit: handleEdit,
      onDelete: handleDelete,
      onAssignRole: handleAssignRole,
    },
    { showOrganization: false }
  ),
);

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

// Debounced search with 500ms delay
watchDebounced(
  searchInput,
  (newValue) => {
    filters.search = newValue;
    filters.page = 1;
  },
  { debounce: 300 },
);

watch(
  () => [filters.role, filters.perPage],
  () => {
    filters.page = 1;
  },
);

watch(
  () => users.value?.meta?.currentPage,
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

watch(showEditDialog, (open) => {
  if (!open) {
    selectedUser.value = null;
  }
});

watch(showDeleteDialog, (open) => {
  if (!open) {
    selectedUser.value = null;
  }
});

// Form submissions
async function handleCreateUser(payload: CreateUserRequest) {
  try {
    const response = await createMutation.mutateAsync(payload);
    toast.success(response.message ?? "User created successfully");
    showCreateDialog.value = false;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to create user"));
  }
}

async function handleUpdateUser(payload: UpdateUserRequest) {
  if (!selectedUser.value || Object.keys(payload).length === 0) {
    return;
  }

  try {
    const response = await updateMutation.mutateAsync({
      id: selectedUser.value.id,
      data: payload,
    });
    toast.success(response.message ?? "User updated successfully");
    showEditDialog.value = false;
    selectedUser.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to update user"));
  }
}

async function handleDeleteUser() {
  if (!selectedUser.value) return;

  try {
    const response = await deleteMutation.mutateAsync(selectedUser.value.id);
    toast.success(response.message ?? "User deleted successfully");
    showDeleteDialog.value = false;
    selectedUser.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete user"));
  }
}

async function submitAssignRole() {
  if (!selectedUser.value) return;

  if (selectedUser.value?.roles.length > 0) {
      try {
        const response = await updateRoleMutation.mutateAsync({
          id: selectedUser.value.id,
          data: { role: assignRoleForm.role },
        });
        toast.success(response.message ?? "Update Role successfully");
        showAssignRoleDialog.value = false;
        selectedUser.value = null;
      } catch (err) {
        toast.error(getApiErrorMessage(err, "Failed to update role"));
      }
  }else {
    try {
      const response = await assignRoleMutation.mutateAsync({
        id: selectedUser.value.id,
        data: { role: assignRoleForm.role },
      });
      toast.success(response.message ?? "Role assigned successfully");
      showAssignRoleDialog.value = false;
      selectedUser.value = null;
    } catch (err) {
      toast.error(getApiErrorMessage(err, "Failed to assign role"));
    }
  }
}

function cancelAssignRole() {
  showAssignRoleDialog.value = false;
  selectedUser.value = null;
}

function handlePageSizeChange(size: number) {
  filters.perPage = size;
}

function handlePageChange(page: number) {
  filters.page = page;
}

function handleSortingChange(sortingState: SortingState) {
  const [sort] = sortingState;

  if (!sort) {
    filters.sortBy = "createdAt";
    filters.sortOrder = "desc";
    return;
  }

  if (
    sort.id === "name" ||
    sort.id === "email" ||
    sort.id === "createdAt"
  ) {
    filters.sortBy = sort.id as SortField;
    filters.sortOrder = sort.desc ? "desc" : "asc";
  }
}

function resetFilters() {
  searchInput.value = "";
  filters.role = "";
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Organization Users"
      description="Manage users within your organization and assign roles."
    >
      <template #actions>
        <Button
          v-if="hasPermission(PERMISSIONS.CREATE_USER)"
          size="sm"
          class="h-8"
          @click="showCreateDialog = true"
        >
          <Plus class="h-3.5 w-3.5 mr-2" />
          Add User
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search name or email..."
        :show-reset="filters.role !== '' || searchInput !== ''"
        @reset="resetFilters"
      >
        <template #filters>
          <div class="flex items-center gap-2">
            <Select v-model="filters.role">
              <SelectTrigger class="h-9 w-[180px] bg-white border-slate-200">
                <SelectValue placeholder="All Roles" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Roles</SelectItem>
                <SelectItem
                  v-for="role in roleFilterOptions"
                  :key="role"
                  :value="role"
                >
                  {{ role.replace(/_/g, " ") }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
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
              Loading user data...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">Error loading users</p>
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
          :sorting="tableSorting"
          :page="filters.page"
          :page-size="filters.perPage"
          :total-items="totalItems"
          :total-pages="totalPages"
          :compact="true"
          @sorting-change="handleSortingChange"
          @page-size-change="handlePageSizeChange"
          @page-change="handlePageChange"
        />
      </div>
    </div>

    <!-- User Management Dialogs -->
    <CreateUserDialog
      v-model:open="showCreateDialog"
      :is-super-admin="false"
      :is-submitting="createMutation.isPending.value"
      :role-options="createUserRoleOptions"
      @submit="handleCreateUser"
    />

    <EditUserDialog
      v-model:open="showEditDialog"
      :is-submitting="updateMutation.isPending.value"
      :user="selectedUser"
      @submit="handleUpdateUser"
    />

    <DeleteUserDialog
      v-model:open="showDeleteDialog"
      :is-submitting="deleteMutation.isPending.value"
      :user-name="selectedUser?.name"
      @confirm="handleDeleteUser"
    />

    <!-- Assign Role Dialog -->
    <Dialog v-model:open="showAssignRoleDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Assign Role</DialogTitle>
          <DialogDescription>
            Assign a role to {{ selectedUser?.name }}.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="assign-role">Role</Label>
            <Select v-model="assignRoleForm.role">
              <SelectTrigger>
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="organization_admin"
                  >Organization Admin</SelectItem
                >
                <SelectItem value="organization_user"
                  >Organization User</SelectItem
                >
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="cancelAssignRole"> Cancel </Button>
          <Button
            @click="submitAssignRole"
            :disabled="assignRoleMutation.isPending.value"
          >
            {{
              assignRoleMutation.isPending.value
                ? "Assigning..."
                : "Assign Role"
            }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
