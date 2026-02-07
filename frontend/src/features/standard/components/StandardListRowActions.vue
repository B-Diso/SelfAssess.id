<script setup lang="ts">
import { useRouter } from 'vue-router'
import { MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { Standard } from '../types/standard'
import { useAuth } from '@/features/auth/composables/useAuth'

interface Props {
  standard: Standard
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
})

const emit = defineEmits<{
  view: [standard: Standard]
  edit: [standard: Standard]
  delete: [standard: Standard]
}>()

const router = useRouter()
const { isSuperAdmin } = useAuth()

// Only Super Admin can edit and delete standards
const canManageStandard = isSuperAdmin

function handleEdit() {
  emit('edit', props.standard)
}

async function handleDelete() {
  // Confirmation is handled by the parent component
  emit('delete', props.standard)
}

function handleNavigateToDetail() {
  router.push(`/standards/${props.standard.id}`)
}
</script>

<template>
  <DropdownMenu :disabled="disabled">
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" :disabled="disabled">
        <span class="sr-only">Open menu</span>
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-48">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuItem class="cursor-pointer focus:bg-accent focus:text-accent-foreground" @click="handleNavigateToDetail">
        <Eye class="mr-2 h-4 w-4" />
        View Details
      </DropdownMenuItem>
      <DropdownMenuItem
        v-if="canManageStandard"
        class="cursor-pointer focus:bg-accent focus:text-accent-foreground"
        @click="handleEdit"
      >
        <Pencil class="mr-2 h-4 w-4" />
        Edit
      </DropdownMenuItem>
      <DropdownMenuSeparator v-if="canManageStandard" />
      <DropdownMenuItem
        v-if="canManageStandard"
        class="cursor-pointer text-destructive focus:bg-destructive/10 focus:text-destructive"
        @click="handleDelete"
      >
        <Trash2 class="mr-2 h-4 w-4" />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
