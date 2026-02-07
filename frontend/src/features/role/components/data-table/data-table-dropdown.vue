<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { Role } from '@/features/role/types/role.types'

interface Props {
  role: Role
}

const props = defineProps<Props>()

const emit = defineEmits<{
  edit: [role: Role]
  delete: [role: Role]
}>()

const handleEdit = () => {
  emit('edit', props.role)
}

const handleDelete = () => {
  emit('delete', props.role)
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon">
        <span class="sr-only">Open menu</span>
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuItem @click="handleEdit">
        <Pencil class="mr-2 h-4 w-4" />
        Edit
      </DropdownMenuItem>
      <div v-if="!props.role.isSystem">
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="handleDelete" class="text-red-600">
          <Trash2 class="mr-2 h-4 w-4" />
          Delete
        </DropdownMenuItem>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
