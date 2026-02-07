<script setup lang="ts">
import { reactive, ref, computed, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import type { SortingState } from "@tanstack/vue-table";
import { Plus, Loader2 } from "lucide-vue-next";
import { useRouter } from "vue-router";
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
import DataTable from "@/components/ui/DataTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import {
  useAssessments,
  useCreateAssessment,
  useDeleteAssessment,
  useUpdateAssessment,
  useTransitionAssessment,
} from "../composables/useAssessments";
import type { Assessment, AssessmentFilters } from "../types/assessment.types";
import { useAssessmentColumns } from "../components/data-table/columns";
import CreateAssessmentDialog from "../components/workflow/create-assessment-dialog.vue";
import EditAssessmentDialog from "../components/workflow/edit-assessment-dialog.vue";
import CancelAssessmentDialog from "../components/cancel-assessment-dialog.vue";
import { useOrganizations } from "@/features/organization/composables/useOrganizations";
import { useAuth } from "@/features/auth/composables/useAuth";

const router = useRouter();
const { isOrganizationAdmin, isSuperAdmin } = useAuth();

// Check if user can create assessments (only Org Admin & Super Admin)
const canCreateAssessment = computed(() => isOrganizationAdmin || isSuperAdmin);

const searchInput = ref("");
const filters = reactive<AssessmentFilters>({
  page: 1,
  perPage: 15,
  status: "",
  organizationId: "",
  search: "",
});

// Fetch organizations for filter dropdown
const orgFilters = computed(() => ({
  page: 1,
  perPage: 100, // Get all organizations for filter
}));
const { data: organizationsData } = useOrganizations(orgFilters);
const organizations = computed(() => organizationsData.value?.data || []);

const filtersRef = computed(() => ({ ...filters }));
const { data, isLoading, error, refetch } = useAssessments(filtersRef);

const createMutation = useCreateAssessment();
const deleteMutation = useDeleteAssessment();
const updateMutation = useUpdateAssessment();
const transitionMutation = useTransitionAssessment();

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showCancelDialog = ref(false);
const selectedAssessment = ref<Assessment | null>(null);
const tableSorting = ref<SortingState>([]);

// Data for data table
const tableData = computed(() => data.value?.data || []);
const totalItems = computed(() => data.value?.meta?.total ?? 0);
const totalPages = computed(() => data.value?.meta?.lastPage ?? 1);

// Status filter options - matches new AssessmentStatus enum
const statusFilterOptions = [
  { value: "", label: "All Statuses" },
  { value: "draft", label: "Draft" },
  { value: "active", label: "Active" },
  { value: "pending_review", label: "Pending Review" },
  { value: "reviewed", label: "Reviewed" },
  { value: "pending_finish", label: "Pending Finish" },
  { value: "finished", label: "Finished" },
  { value: "cancelled", label: "Cancelled" },
];

// Table columns with tooltip support
const tableColumns = computed(() =>
  useAssessmentColumns({
    onView: handleView,
    onEdit: handleEdit,
    onEditDetail: handleEdit,
    onDelete: handleDelete,
    onActivate: handleActivate,
    onCancel: handleCancel,
    onSubmit: handleSubmit,
    onApprove: handleApprove,
    onReject: handleReject,
    onRequestFinish: handleRequestFinish,
    onFinalize: handleFinalize,
    onRevert: handleRevert,
  }),
);

// Debounced search with 300ms delay
watchDebounced(
  searchInput,
  (newValue) => {
    filters.search = newValue || undefined;
    filters.page = 1;
  },
  { debounce: 300 },
);

watch(
  () => filters.status,
  () => {
    filters.page = 1;
  },
);

watch(
  () => filters.organizationId,
  () => {
    filters.page = 1;
  },
);

watch(
  () => data.value?.meta?.currentPage,
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

// Handlers
function handleView(assessment: Assessment) {
  router.push(`/assessments/${assessment.id}`);
}

function handleEdit(assessment: Assessment) {
  selectedAssessment.value = assessment;
  showEditDialog.value = true;
}

function handleDelete(assessment: Assessment) {
  selectedAssessment.value = assessment;
  // Use confirm dialog for delete
  if (!confirm(`Are you sure you want to delete "${assessment.name}"?`)) {
    return;
  }
  doDelete(assessment);
}

async function doDelete(assessment: Assessment) {
  try {
    await deleteMutation.mutateAsync(assessment.id);
    toast.success("Assessment deleted successfully");
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to delete assessment"));
  }
}

async function handleActivate(assessment: Assessment) {
  if (
    !confirm(
      `Activate assessment "${assessment.name}"?\n\nThis will change the status from Draft to Active.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "active", note: "Activated from assessment list" },
    });
    toast.success(`Assessment "${assessment.name}" activated successfully`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to activate assessment"));
  }
}

function handleCancel(assessment: Assessment) {
  selectedAssessment.value = assessment;
  showCancelDialog.value = true;
}

function handleCancelSuccess() {
  showCancelDialog.value = false;
  selectedAssessment.value = null;
}

// Workflow Handlers
async function handleSubmit(assessment: Assessment) {
  if (
    !confirm(
      `Submit assessment "${assessment.name}" for review?\n\nThis will change the status from Active to Pending Review.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: {
        status: "pending_review",
        note: "Submitted for review from assessment list",
      },
    });
    toast.success(`Assessment "${assessment.name}" submitted for review`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to submit assessment"));
  }
}

async function handleApprove(assessment: Assessment) {
  if (
    !confirm(
      `Approve review for assessment "${assessment.name}"?\n\nThis will change the status from Pending Review to Reviewed.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "reviewed", note: "Review approved" },
    });
    toast.success(`Assessment "${assessment.name}" review approved`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to approve review"));
  }
}

async function handleReject(assessment: Assessment) {
  if (
    !confirm(
      `Reject/Return assessment "${assessment.name}"?\n\nThis will return the assessment to Active status for revisions.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "active", note: "Returned for revisions" },
    });
    toast.success(`Assessment "${assessment.name}" returned for revisions`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to reject assessment"));
  }
}

async function handleRequestFinish(assessment: Assessment) {
  if (
    !confirm(
      `Request finish for assessment "${assessment.name}"?\n\nThis will change the status from Reviewed to Pending Finish.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "pending_finish", note: "Requested for finalization" },
    });
    toast.success(`Assessment "${assessment.name}" finish requested`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to request finish"));
  }
}

async function handleFinalize(assessment: Assessment) {
  if (
    !confirm(
      `Finalize assessment "${assessment.name}"?\n\nThis will change the status to Finished and complete the assessment. This action cannot be undone.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "finished", note: "Assessment finalized" },
    });
    toast.success(`Assessment "${assessment.name}" finalized successfully`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to finalize assessment"));
  }
}

async function handleRevert(assessment: Assessment) {
  if (
    !confirm(
      `Revert assessment "${assessment.name}" to Active?\n\nThis will return the assessment to Active status.`,
    )
  ) {
    return;
  }
  try {
    await transitionMutation.mutateAsync({
      id: assessment.id,
      payload: { status: "active", note: "Reverted to active" },
    });
    toast.success(`Assessment "${assessment.name}" reverted to active`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to revert assessment"));
  }
}

async function handleCreate(payload: any) {
  try {
    const result = await createMutation.mutateAsync(payload);
    toast.success("Assessment created");
    showCreateDialog.value = false;
    router.push(`/assessments/${result.id}`);
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to create assessment"));
  }
}

async function handleUpdate(payload: {
  name: string;
  startDate?: string;
  endDate?: string;
}) {
  if (!selectedAssessment.value) return;

  try {
    await updateMutation.mutateAsync({
      id: selectedAssessment.value.id,
      data: payload,
    });
    toast.success("Assessment updated successfully");
    showEditDialog.value = false;
    selectedAssessment.value = null;
  } catch (err: any) {
    toast.error(getApiErrorMessage(err, "Failed to update assessment"));
  }
}

function handlePageSizeChange(size: number) {
  filters.perPage = size;
}

function handlePageChange(page: number) {
  filters.page = page;
}

function handleSortingChange(sortingState: SortingState) {
  // Backend doesn't support sorting yet, but this is prepared for future
  tableSorting.value = sortingState;
}

function resetFilters() {
  searchInput.value = "";
  filters.search = undefined;
  filters.status = "";
  filters.organizationId = "";
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Assessments"
      description="Track and manage compliance assessments across multiple quality standards"
    >
      <template #actions>
        <Button
          v-if="canCreateAssessment"
          size="sm"
          class="h-8"
          @click="showCreateDialog = true"
        >
          <Plus class="h-3.5 w-3.5 mr-2" />
          New Assessment
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search assessments..."
        :show-reset="
          filters.status !== '' ||
          filters.organizationId !== '' ||
          searchInput !== ''
        "
        @reset="resetFilters"
      >
        <template #filters>
          <div class="flex items-center gap-2">
            <Select v-model="filters.organizationId" class="w-[180px]">
              <SelectTrigger class="h-9 bg-white border-slate-200">
                <SelectValue placeholder="All Organizations" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">
                  All Organizations
                </SelectItem>
                <SelectItem
                  v-for="org in organizations"
                  :key="org.id"
                  :value="org.id"
                >
                  {{ org.name }}
                </SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="filters.status" class="w-[180px]">
              <SelectTrigger class="h-9 bg-white border-slate-200">
                <SelectValue placeholder="All Statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="option in statusFilterOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
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
              Loading assessments...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">
              Error loading assessments
            </p>
            <p class="text-xs text-muted-foreground mt-1">
              Please check your connection and try again.
            </p>
            <Button variant="outline" size="sm" class="mt-4" @click="refetch">
              Retry
            </Button>
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

    <!-- Assessment Management Dialogs -->
    <CreateAssessmentDialog
      v-model:open="showCreateDialog"
      :is-submitting="createMutation.isPending.value"
      @submit="handleCreate"
    />

    <EditAssessmentDialog
      v-model:open="showEditDialog"
      :is-submitting="updateMutation.isPending.value"
      :assessment="selectedAssessment"
      @submit="handleUpdate"
    />

    <CancelAssessmentDialog
      v-model:open="showCancelDialog"
      :assessment="selectedAssessment"
      @success="handleCancelSuccess"
    />
  </div>
</template>
