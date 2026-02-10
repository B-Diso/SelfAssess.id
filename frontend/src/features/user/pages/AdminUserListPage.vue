<script setup lang="ts">
import { reactive, ref, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import { type SortingState } from "@tanstack/vue-table";
import { Plus, Loader2, Search } from "lucide-vue-next";
import {
  useAdminUsers,
  useAssignAdminRole,
  useCreateAdminUser,
  useDeleteAdminUser,
  useUpdateAdminUser,
} from "../composables/useAdminUsers";
import { useAuth } from "@/features/auth/composables/useAuth";
import { useOrganizations } from "@/features/organization/composables/useOrganizations";
import type { OrganizationFilters } from "@/features/organization/types/organization.types";
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

const { hasPermission, hasRole } = useAuth();

type SortField = "name" | "email" | "createdAt" | "organizationName";

const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  search: "",
  organizationId: "" as string,
  role: "" as string,
  sortBy: "createdAt" as SortField,
  sortOrder: "desc" as "asc" | "desc",
});

// Fetch organizations for filter (no params to get all orgs from backend)
const { data: orgData } = useOrganizations(ref({} as OrganizationFilters));
const organizations = computed(() => orgData.value?.data || []);

const filtersRef = computed(() => ({ ...filters }));
const { data: users, isLoading, error, refetch } = useAdminUsers(filtersRef);
const createAdminMutation = useCreateAdminUser();
const updateAdminMutation = useUpdateAdminUser();
const deleteAdminMutation = useDeleteAdminUser();
const assignAdminRoleMutation = useAssignAdminRole();
const tableSorting = ref<SortingState>([]);

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showAssignRoleDialog = ref(false);
const selectedUser = ref<User | null>(null);

const assignRoleForm = reactive({
  role: "",
});

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
  if (existingRole && assignRoleOptions.value.includes(existingRole)) {
    assignRoleForm.role = existingRole;
  } else {
    assignRoleForm.role = assignRoleOptions.value[0] || "";
  }
  showAssignRoleDialog.value = true;
}

const tableData = computed(() => users.value?.data || []);
const totalItems = computed(
  () => users.value?.meta?.total ?? tableData.value.length,
);
const totalPages = computed(() => users.value?.meta?.lastPage ?? 1);
const hasData = computed(() => (users.value?.data?.length ?? 0) > 0);

const tableColumns = computed(() =>
  createColumns({
    onEdit: handleEdit,
    onDelete: handleDelete,
    onAssignRole: handleAssignRole,
  }),
);

const roleFilterOptions = [
  ROLES.SUPER_ADMIN,
  ROLES.ORGANIZATION_ADMIN,
  ROLES.ORGANIZATION_USER,
] as const;

const createUserRoleOptions = [
  { label: "Super Admin", value: ROLES.SUPER_ADMIN },
  { label: "Organization Admin", value: ROLES.ORGANIZATION_ADMIN },
  { label: "Organization User", value: ROLES.ORGANIZATION_USER },
];

const assignRoleOptions = computed(() => {
  const allRoles: string[] = [
    ROLES.SUPER_ADMIN,
    ROLES.ORGANIZATION_ADMIN,
    ROLES.ORGANIZATION_USER,
  ];

  if (!selectedUser.value) {
    return allRoles;
  }

  if (selectedUser.value.isMasterOrganization) {
    return allRoles;
  }

  return allRoles.filter((role) => role !== ROLES.SUPER_ADMIN);
});

const isSuperAdmin = computed(() => hasRole(ROLES.SUPER_ADMIN));

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
  () => [filters.organizationId, filters.role, filters.perPage],
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

watch(showAssignRoleDialog, (open) => {
  if (!open) {
    assignRoleForm.role = "";
    selectedUser.value = null;
  }
});

async function handleCreateUser(
  payload: CreateUserRequest & { organizationId?: string },
) {
  if (isSuperAdmin.value && !payload.organizationId) {
    toast.error("Please select an organization before creating the user.");
    return;
  }

  try {
    const response = await createAdminMutation.mutateAsync({
      name: payload.name,
      email: payload.email,
      password: payload.password,
      role: payload.role,
      organizationId: payload.organizationId!,
    });
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
    const response = await updateAdminMutation.mutateAsync({
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
    const response = await deleteAdminMutation.mutateAsync(
      selectedUser.value.id,
    );
    toast.success(response.message ?? "User deleted successfully");
    showDeleteDialog.value = false;
    selectedUser.value = null;
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete user"));
  }
}

async function submitAssignRole() {
  if (!selectedUser.value) return;

  if (!assignRoleForm.role) {
    toast.error("Please choose a role before assigning.");
    return;
  }

  if (
    assignRoleForm.role === ROLES.SUPER_ADMIN &&
    !selectedUser.value.isMasterOrganization
  ) {
    toast.error(
      "Super Admin role can only be assigned within the Master Organization.",
    );
    return;
  }

  try {
    const response = await assignAdminRoleMutation.mutateAsync({
      id: selectedUser.value.id,
      data: { role: assignRoleForm.role },
    });
    toast.success(response.message ?? "Role updated successfully");
    showAssignRoleDialog.value = false;
    selectedUser.value = null;
    assignRoleForm.role = "";
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to update role"));
  }
}

function cancelAssignRole() {
  showAssignRoleDialog.value = false;
  selectedUser.value = null;
  assignRoleForm.role = "";
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
    sort.id === "organizationName" ||
    sort.id === "createdAt"
  ) {
    filters.sortBy = sort.id as SortField;
    filters.sortOrder = sort.desc ? "desc" : "asc";
  }
}

function handleRefresh() {
  refetch();
}

function resetFilters() {
  searchInput.value = "";
  filters.search = "";
  filters.organizationId = "";
  filters.role = "";
  filters.page = 1;
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Admin Users"
      description="Manage platform-wide users and their respective system roles."
    >
      <template #actions>
        <Button
          v-if="
            hasPermission(PERMISSIONS.CREATE_USER) && hasRole(ROLES.SUPER_ADMIN)
          "
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
        search-placeholder="Search name, email, organization..."
        :show-reset="filters.organizationId !== '' || filters.role !== '' || searchInput !== ''"
        @reset="resetFilters"
      >
        <template #filters>
          <div class="flex items-center gap-2">
            <Select v-model="filters.organizationId">
              <SelectTrigger class="h-9 w-[180px] bg-white border-slate-200">
                <SelectValue placeholder="All Organizations" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Organizations</SelectItem>
                <SelectItem
                  v-for="org in organizations"
                  :key="org.id"
                  :value="org.id"
                >
                  {{ org.name }}
                </SelectItem>
              </SelectContent>
            </Select>

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
      <div class="flex-1 min-h-0 p-2 flex flex-col">
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
            <Button
              variant="outline"
              size="sm"
              class="mt-4"
              @click="handleRefresh"
              >Retry</Button
            >
          </div>
        </div>

        <div
          v-else-if="!hasData"
          class="h-full flex flex-col items-center justify-center gap-3 py-12"
        >
          <div class="p-4 rounded-full bg-slate-50">
            <Search class="h-8 w-8 text-slate-300" />
          </div>
          <div class="text-center">
            <p class="text-sm font-semibold text-slate-900">No users found</p>
            <p class="text-xs text-slate-500 max-w-[200px] mt-1">
              Try adjusting your search or filters to find what you're looking
              for.
            </p>
          </div>
          <Button variant="outline" size="sm" class="mt-2" @click="resetFilters"
            >Clear all filters</Button
          >
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

    <CreateUserDialog
      v-model:open="showCreateDialog"
      :is-super-admin="isSuperAdmin"
      :is-submitting="createAdminMutation.isPending.value"
      :role-options="createUserRoleOptions"
      @submit="handleCreateUser"
    />

    <EditUserDialog
      v-model:open="showEditDialog"
      :is-submitting="updateAdminMutation.isPending.value"
      :user="selectedUser"
      @submit="handleUpdateUser"
    />

    <DeleteUserDialog
      v-model:open="showDeleteDialog"
      :is-submitting="deleteAdminMutation.isPending.value"
      :user-name="selectedUser?.name"
      @confirm="handleDeleteUser"
    />

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
                <SelectItem
                  v-for="role in assignRoleOptions"
                  :key="role"
                  :value="role"
                >
                  {{ role.replace(/_/g, " ") }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="cancelAssignRole"> Cancel </Button>
          <Button
            @click="submitAssignRole"
            :disabled="assignAdminRoleMutation.isPending.value"
          >
            {{
              assignAdminRoleMutation.isPending.value
                ? "Assigning..."
                : "Assign Role"
            }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
