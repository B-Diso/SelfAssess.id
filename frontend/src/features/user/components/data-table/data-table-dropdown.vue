<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash2, UserCog } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { User } from '@/features/user/types/user.types'

interface Props {
  user: User
}

const props = defineProps<Props>()

const emit = defineEmits<{
  edit: [user: User]
  delete: [user: User]
  assignRole: [user: User]
}>()

const handleEdit = () => {
  emit('edit', props.user)
}

const handleDelete = () => {
  emit('delete', props.user)
}

const handleAssignRole = () => {
  emit('assignRole', props.user)
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
      <DropdownMenuItem @click="handleAssignRole">
        <UserCog class="mr-2 h-4 w-4" />
        Assign Role
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="handleDelete" class="text-red-600">
        <Trash2 class="mr-2 h-4 w-4" />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
