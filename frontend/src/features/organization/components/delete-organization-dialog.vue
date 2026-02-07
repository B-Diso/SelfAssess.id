<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

defineProps<{
  open: boolean
  isSubmitting: boolean
  organizationName?: string
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'confirm'): void
}>()

function closeDialog() {
  emit('update:open', false)
}

function handleConfirm() {
  emit('confirm')
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Delete Organization</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete {{ organizationName ?? 'this organization' }}? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="closeDialog">Cancel</Button>
        <Button variant="destructive" :disabled="isSubmitting" @click="handleConfirm">
          {{ isSubmitting ? 'Deleting...' : 'Delete Organization' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
