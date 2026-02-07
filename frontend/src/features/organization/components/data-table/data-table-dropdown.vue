<script setup lang="ts">
import { Edit, Trash } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import type { Organization } from '@/features/organization/types/organization.types'

interface Props {
  organization: Organization
}

const props = defineProps<Props>()

const emit = defineEmits<{
  edit: [organization: Organization]
  delete: [organization: Organization]
  viewMembers: [organization: Organization]
  toggleStatus: [organization: Organization]
}>()

const handleEdit = () => {
  emit('edit', props.organization)
}

const handleDelete = () => {
  emit('delete', props.organization)
}

const buttonClass = 'h-8 w-8 p-0'
</script>

<template>

<TooltipProvider :delay-duration="300">
    <div class="flex items-center gap-1" role="group" aria-label="Organization actions">
      <!-- Edit Button -->
      <Tooltip>
        <TooltipTrigger as-child>
          <Button
            :class="buttonClass"
            variant="ghost"
            size="icon"
            :aria-label="`Edit organization ${organization}`"
            @click="handleEdit"
          >
            <Edit class="w-4 h-4" />
          </Button>
        </TooltipTrigger>
        <TooltipContent>
          <p>Edit</p>
        </TooltipContent>
      </Tooltip>

      <!-- Delete Button -->
      <Tooltip>
        <TooltipTrigger as-child>
          <Button
            :class="buttonClass"
            variant="ghost"
            size="icon"
            :aria-label="`Delete organization ${organization}`"
            @click="handleDelete"
          >
            <Trash class="w-4 h-4" />
          </Button>
        </TooltipTrigger>
        <TooltipContent>
          <p>Delete</p>
        </TooltipContent>
      </Tooltip>
    </div>
  </TooltipProvider>
</template>
