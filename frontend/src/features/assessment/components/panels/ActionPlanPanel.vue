<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { storeToRefs } from "pinia";
import type {
  ActionPlan,
  CreateActionPlanRequest,
  UpdateActionPlanRequest,
} from "../../types/assessment.types";
import { assessmentApi } from "../../api/assessmentApi";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { DatePicker } from "@/components/ui/datepicker";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Plus,
  Edit,
  Trash2,
  Calendar,
  User,
  ClipboardList,
} from "lucide-vue-next";
import type { DateValue } from "@internationalized/date";
import { parseDate } from "@internationalized/date";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { actionPlanSchema } from "@/lib/validation/schemas/assessment";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";

import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";

const props = defineProps<{
  responseId: string;
  assessmentId: string;
  actionPlans?: ActionPlan[];
}>();

const emit = defineEmits<{
  created: [actionPlan: ActionPlan];
  updated: [actionPlan: ActionPlan];
  deleted: [actionPlanId: string];
}>();

const store = useAssessmentStore();
const { saving, activeResponse } = storeToRefs(store);

// Check if action plans can be managed (only when response status is 'active')
const canManageActionPlans = computed(() => {
  return activeResponse.value?.status === 'active';
});

// Dialog state
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

// Edit mode state
const editingActionPlan = ref<ActionPlan | null>(null);
const deleteActionPlanId = ref<string | null>(null);

// Local state for action plans
const localActionPlans = ref<ActionPlan[]>([]);
const isLoadingActionPlans = ref(false);

// Date picker refs for create/edit dialogs
const createSelectedDate = ref<DateValue>();
const editSelectedDate = ref<DateValue>();

// Typed schema for vee-validate
const validationSchema = toTypedSchema(actionPlanSchema);

// Create form with vee-validate
const {
  handleSubmit: handleCreateSubmit,
  setValues: setCreateValues,
  resetForm: resetCreateForm,
  errors: createErrors,
  meta: createMeta,
  defineField: defineCreateField,
} = useForm({
  validationSchema,
  initialValues: {
    title: "",
    actionPlan: "",
    pic: "",
    dueDate: "",
  },
});

// Define create form fields
const [createTitle, createTitleAttrs] = defineCreateField('title');
const [createActionPlanField, createActionPlanFieldAttrs] = defineCreateField('actionPlan');
const [createPic, createPicAttrs] = defineCreateField('pic');

// Edit form with vee-validate
const {
  handleSubmit: handleEditSubmit,
  setValues: setEditValues,
  resetForm: resetEditForm,
  errors: editErrors,
  meta: editMeta,
  defineField: defineEditField,
} = useForm({
  validationSchema,
  initialValues: {
    title: "",
    actionPlan: "",
    pic: "",
    dueDate: "",
  },
});

// Define edit form fields
const [editTitle, editTitleAttrs] = defineEditField('title');
const [editActionPlan, editActionPlanAttrs] = defineEditField('actionPlan');
const [editPic, editPicAttrs] = defineEditField('pic');

// Sync date pickers with form values
watch(createSelectedDate, (newValue) => {
  setCreateValues({ dueDate: newValue ? newValue.toString() : "" });
});

watch(editSelectedDate, (newValue) => {
  setEditValues({ dueDate: newValue ? newValue.toString() : "" });
});

// Fetch action plans for this response
async function fetchActionPlans() {
  if (!props.responseId) return;

  isLoadingActionPlans.value = true;
  try {
    const response = await assessmentApi.getActionPlans({
      assessmentResponseId: props.responseId,
    });
    localActionPlans.value = response.data || [];
  } catch (error) {
    console.error("Failed to fetch action plans:", error);
    localActionPlans.value = [];
  } finally {
    isLoadingActionPlans.value = false;
  }
}

onMounted(() => {
  if (props.actionPlans && props.actionPlans.length > 0) {
    localActionPlans.value = props.actionPlans;
  } else {
    fetchActionPlans();
  }
});

// Watch for responseId changes and refetch action plans
watch(() => props.responseId, (newResponseId, oldResponseId) => {
  if (newResponseId && newResponseId !== oldResponseId) {
    fetchActionPlans();
  }
});

function resetCreateDialog() {
  resetCreateForm();
  createSelectedDate.value = undefined;
}

function openCreateDialog() {
  resetCreateDialog();
  isCreateDialogOpen.value = true;
}

function openEditDialog(actionPlan: ActionPlan) {
  editingActionPlan.value = actionPlan;
  setEditValues({
    title: actionPlan.title,
    pic: actionPlan.pic,
    dueDate: actionPlan.dueDate,
    actionPlan: actionPlan.actionPlan || "",
  });
  if (actionPlan.dueDate) {
    try {
      editSelectedDate.value = parseDate(actionPlan.dueDate.split("T")[0] ?? "");
    } catch {
      editSelectedDate.value = undefined;
    }
  }
  isEditDialogOpen.value = true;
}

function openDeleteDialog(actionPlanId: string) {
  deleteActionPlanId.value = actionPlanId;
  isDeleteDialogOpen.value = true;
}

// Create form submission handler
const createActionPlan = handleCreateSubmit(async (formValues) => {
  try {
    const payload: CreateActionPlanRequest = {
      assessmentId: props.assessmentId,
      assessmentResponseId: props.responseId,
      title: formValues.title,
      pic: formValues.pic,
      dueDate: formValues.dueDate,
      actionPlan: formValues.actionPlan,
    };

    const result = await assessmentApi.createActionPlan(payload);
    localActionPlans.value = [...localActionPlans.value, result];
    emit("created", result);
    isCreateDialogOpen.value = false;
    resetCreateDialog();
    toast.success("Action plan created successfully");
  } catch (error) {
    console.error("Failed to create action plan:", error);
    toast.error(getApiErrorMessage(error, "Failed to create action plan"));
  }
});

// Edit form submission handler
const updateActionPlan = handleEditSubmit(async (formValues) => {
  if (!editingActionPlan.value) return;

  try {
    const payload: UpdateActionPlanRequest = {
      title: formValues.title,
      pic: formValues.pic,
      dueDate: formValues.dueDate,
      actionPlan: formValues.actionPlan,
    };

    const result = await assessmentApi.updateActionPlan(
      editingActionPlan.value.id,
      payload,
    );

    localActionPlans.value = localActionPlans.value.map((ap) =>
      ap.id === result.id ? result : ap,
    );

    emit("updated", result);
    isEditDialogOpen.value = false;
    editingActionPlan.value = null;
    resetEditForm();
    editSelectedDate.value = undefined;
    toast.success("Action plan updated successfully");
  } catch (error) {
    console.error("Failed to update action plan:", error);
    toast.error(getApiErrorMessage(error, "Failed to update action plan"));
  }
});

async function deleteActionPlan() {
  if (!deleteActionPlanId.value) return;

  try {
    await assessmentApi.deleteActionPlan(deleteActionPlanId.value);
    localActionPlans.value = localActionPlans.value.filter(
      (ap) => ap.id !== deleteActionPlanId.value,
    );
    emit("deleted", deleteActionPlanId.value);
    isDeleteDialogOpen.value = false;
    deleteActionPlanId.value = null;
    toast.success("Action plan deleted successfully");
  } catch (error) {
    console.error("Failed to delete action plan:", error);
    toast.error(getApiErrorMessage(error, "Failed to delete action plan"));
  }
}

function formatDate(dateString: string): string {
  if (!dateString) return "-";
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

const isCreateValid = computed(() => createMeta.value.valid);
const isEditValid = computed(() => editMeta.value.valid);
</script>

<template>
  <Card class="flex-1 flex flex-col">
    <CardHeader class="pb-4 border-b">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="p-2 bg-primary/10 rounded-lg">
            <ClipboardList class="w-5 h-5 text-primary" />
          </div>
          <div>
            <CardTitle class="text-sm font-bold uppercase tracking-tight">
              Action Plans
            </CardTitle>
            <CardDescription class="text-[10px]">
              Manage corrective actions
            </CardDescription>
          </div>
        </div>
        <Button 
          size="sm" 
          class="h-8 text-xs px-3" 
          @click="openCreateDialog"
          :disabled="!canManageActionPlans"
        >
          <Plus class="w-3.5 h-3.5 mr-1.5" />
          New Plan
        </Button>
      </div>
    </CardHeader>

    <CardContent class="flex-1 flex flex-col pt-4 min-h-0">
      <!-- Empty State -->
      <div
        v-if="localActionPlans.length === 0"
        class="flex flex-col items-center justify-center py-10 border border-dashed rounded-xl bg-muted/20"
      >
        <div class="p-3 bg-muted/50 rounded-full mb-3">
          <ClipboardList class="w-8 h-8 opacity-30" />
        </div>
        <p class="text-xs font-bold text-muted-foreground">
          No action plans added yet
        </p>
        <p class="text-[10px] text-muted-foreground/60 mt-1 mb-3">
          Plan corrective actions for this requirement
        </p>
        <Button
          variant="outline"
          size="sm"
          @click="openCreateDialog"
          :disabled="!canManageActionPlans"
          class="h-8 text-[10px] font-bold uppercase tracking-wider"
        >
          <Plus class="w-3 h-3 mr-1.5" />
          Add First Plan
        </Button>
      </div>

      <!-- Action Plans List -->
      <ScrollArea v-else class="max-h-[400px]">
        <div class="grid gap-2 pr-4">
          <div
            v-for="actionPlan in localActionPlans"
            :key="actionPlan.id"
            class="group relative border rounded-lg p-3 bg-card hover:border-primary/30 transition-all"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1 min-w-0">
                <h4
                  class="text-sm font-bold truncate group-hover:text-primary transition-colors"
                >
                  {{ actionPlan.title }}
                </h4>

                <p
                  v-if="actionPlan.actionPlan"
                  class="text-[11px] text-muted-foreground line-clamp-2 mt-1 leading-relaxed"
                >
                  {{ actionPlan.actionPlan }}
                </p>

                <div class="flex items-center gap-3 mt-2.5">
                  <div
                    v-if="actionPlan.pic"
                    class="flex items-center gap-1.5 px-2 py-0.5 bg-muted rounded-full"
                  >
                    <User class="w-2.5 h-2.5 text-muted-foreground" />
                    <span class="text-[10px] font-medium">{{
                      actionPlan.pic
                    }}</span>
                  </div>
                  <div
                    class="flex items-center gap-1.5 px-2 py-0.5 bg-primary/5 rounded-full"
                  >
                    <Calendar class="w-2.5 h-2.5 text-primary" />
                    <span class="text-[10px] font-bold text-primary">{{
                      formatDate(actionPlan.dueDate)
                    }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-0.5">
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 opacity-0 group-hover:opacity-100 transition-opacity"
                  @click="openEditDialog(actionPlan)"
                >
                  <Edit class="w-3.5 h-3.5" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 text-destructive hover:text-destructive opacity-0 group-hover:opacity-100 transition-opacity"
                  @click="openDeleteDialog(actionPlan.id)"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </ScrollArea>
    </CardContent>
  </Card>

  <!-- Create Dialog -->
  <Dialog v-model:open="isCreateDialogOpen">
    <DialogContent class="sm:max-w-[420px] p-0 overflow-hidden">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-xl font-bold">New Action Plan</DialogTitle>
        <DialogDescription class="text-xs">
          Plan a corrective action for this requirement.
        </DialogDescription>
      </DialogHeader>
      <form @submit="createActionPlan">
        <ScrollArea class="max-h-[60vh]">
          <div class="px-6 py-4 space-y-3">
            <div class="space-y-1.5">
              <Label
                for="title"
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Title <span class="text-destructive">*</span></Label
              >
              <Input
                id="title"
                v-model="createTitle"
                v-bind="createTitleAttrs"
                placeholder="What needs to be done?"
                :class="['h-9 text-sm', { 'border-destructive': createErrors.title }]"
              />
              <p v-if="createErrors.title" class="text-xs text-destructive">{{ createErrors.title }}</p>
            </div>
            <div class="space-y-1.5">
              <Label
                for="actionPlan"
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Details</Label
              >
              <span class="text-[10px] text-muted-foreground ml-1"
                >(Recommended)</span
              >
              <div class="mt-1">
                <Textarea
                  id="actionPlan"
                  v-model="createActionPlanField"
                  v-bind="createActionPlanFieldAttrs"
                  placeholder="Detailed description..."
                  :rows="3"
                  :class="['text-xs resize-none', { 'border-destructive': createErrors.actionPlan }]"
                />
              </div>
              <p v-if="createErrors.actionPlan" class="text-xs text-destructive">{{ createErrors.actionPlan }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <Label
                  for="pic"
                  class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                  >PIC <span class="text-destructive">*</span></Label
                >
                <Input
                  id="pic"
                  v-model="createPic"
                  v-bind="createPicAttrs"
                  placeholder="Who is responsible?"
                  :class="['h-9 text-xs', { 'border-destructive': createErrors.pic }]"
                />
                <p v-if="createErrors.pic" class="text-xs text-destructive">{{ createErrors.pic }}</p>
              </div>
              <div class="space-y-1.5">
                <Label
                  for="dueDate"
                  class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                  >Due Date <span class="text-destructive">*</span></Label
                >
                <DatePicker
                  v-model="createSelectedDate"
                  :class="{ 'border-destructive': createErrors.dueDate }"
                />
                <p v-if="createErrors.dueDate" class="text-xs text-destructive">{{ createErrors.dueDate }}</p>
              </div>
            </div>
          </div>
        </ScrollArea>
        <DialogFooter class="bg-muted/30 px-6 py-3 border-t">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            @click="isCreateDialogOpen = false"
            class="text-xs"
            >Cancel</Button
          >
          <Button
            type="submit"
            size="sm"
            :disabled="saving || !isCreateValid"
            class="text-xs px-4"
          >
            {{ saving ? "Saving..." : "Create Plan" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>

  <!-- Edit Dialog -->
  <Dialog v-model:open="isEditDialogOpen">
    <DialogContent class="sm:max-w-[420px] p-0 overflow-hidden">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-xl font-bold">Update Action Plan</DialogTitle>
        <DialogDescription class="text-xs">
          Modify the existing corrective action.
        </DialogDescription>
      </DialogHeader>
      <form @submit="updateActionPlan">
        <ScrollArea class="max-h-[60vh]">
          <div class="px-6 py-4 space-y-3">
            <div class="space-y-1.5">
              <Label
                for="edit-title"
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Title <span class="text-destructive">*</span></Label
              >
              <Input
                id="edit-title"
                v-model="editTitle"
                v-bind="editTitleAttrs"
                :class="['h-9 text-sm', { 'border-destructive': editErrors.title }]"
              />
              <p v-if="editErrors.title" class="text-xs text-destructive">{{ editErrors.title }}</p>
            </div>
            <div class="space-y-1.5">
              <Label
                for="edit-actionPlan"
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Details</Label
              >
              <span class="text-[10px] text-muted-foreground ml-1"
                >(Recommended)</span
              >
              <div class="mt-1">
                <Textarea
                  id="edit-actionPlan"
                  v-model="editActionPlan"
                  v-bind="editActionPlanAttrs"
                  :rows="3"
                  :class="['text-xs resize-none', { 'border-destructive': editErrors.actionPlan }]"
                />
              </div>
              <p v-if="editErrors.actionPlan" class="text-xs text-destructive">{{ editErrors.actionPlan }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <Label
                  for="edit-pic"
                  class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                  >PIC <span class="text-destructive">*</span></Label
                >
                <Input
                  id="edit-pic"
                  v-model="editPic"
                  v-bind="editPicAttrs"
                  :class="['h-9 text-xs', { 'border-destructive': editErrors.pic }]"
                />
                <p v-if="editErrors.pic" class="text-xs text-destructive">{{ editErrors.pic }}</p>
              </div>
              <div class="space-y-1.5">
                <Label
                  for="edit-dueDate"
                  class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                  >Due Date <span class="text-destructive">*</span></Label
                >
                <DatePicker
                  v-model="editSelectedDate"
                  :class="{ 'border-destructive': editErrors.dueDate }"
                />
                <p v-if="editErrors.dueDate" class="text-xs text-destructive">{{ editErrors.dueDate }}</p>
              </div>
            </div>
          </div>
        </ScrollArea>
        <DialogFooter class="bg-muted/30 px-6 py-3 border-t">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            @click="isEditDialogOpen = false"
            class="text-xs"
            >Cancel</Button
          >
          <Button
            type="submit"
            size="sm"
            :disabled="saving || !isEditValid"
            class="text-xs px-4"
          >
            {{ saving ? "Updating..." : "Save Changes" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>

  <!-- Delete Confirmation Dialog -->
  <Dialog v-model:open="isDeleteDialogOpen">
    <DialogContent class="sm:max-w-[400px]">
      <DialogHeader>
        <DialogTitle>Remove Action Plan</DialogTitle>
        <DialogDescription class="text-xs">
          Are you sure you want to delete this plan? This cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="mt-4 gap-2">
        <Button
          variant="outline"
          size="sm"
          @click="isDeleteDialogOpen = false"
          class="text-xs"
          >Cancel</Button
        >
        <Button
          variant="destructive"
          size="sm"
          @click="deleteActionPlan"
          class="text-xs px-4"
          >Delete</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
