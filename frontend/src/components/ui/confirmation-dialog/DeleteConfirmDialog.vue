<script setup>
import { ref, watch } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

import { deleteConfirmDialogState } from './index'

const inputValue = ref('')

watch(() => deleteConfirmDialogState.isOpen.value, (newVal) => {
  if (!newVal) {
    inputValue.value = ''
  }
})

const handleConfirm = () => {
  if (inputValue.value === 'DELETE') {
    deleteConfirmDialogState.onConfirm()
    inputValue.value = ''
  }
}

const handleCancel = () => {
  deleteConfirmDialogState.onCancel()
  inputValue.value = ''
}
</script>

<template>
  <AlertDialog :open="deleteConfirmDialogState.isOpen.value" @update:open="deleteConfirmDialogState.isOpen.value = $event">
    <AlertDialogContent class="bg-card max-w-md">
      <AlertDialogHeader>
        <AlertDialogTitle>{{ deleteConfirmDialogState.title }}</AlertDialogTitle>
        <AlertDialogDescription class="space-y-4">
          <p>{{ deleteConfirmDialogState.description }}</p>
          <div v-if="deleteConfirmDialogState.itemName" class="text-sm text-foreground font-medium bg-muted p-3 rounded-md">
            {{ deleteConfirmDialogState.itemName }}
          </div>
        </AlertDialogDescription>
      </AlertDialogHeader>
      
      <div class="space-y-2">
        <Label for="delete-confirm" class="text-sm font-medium">
          Type <span class="font-bold text-destructive">DELETE</span> to confirm
        </Label>
        <Input
          id="delete-confirm"
          v-model="inputValue"
          type="text"
          placeholder="DELETE"
          class="font-mono"
          @keydown.enter="handleConfirm"
        />
      </div>

      <AlertDialogFooter>
        <AlertDialogCancel class="cursor-pointer" @click="handleCancel">
          Cancel
        </AlertDialogCancel>
        <AlertDialogAction
          class="bg-destructive hover:bg-destructive/90 text-white px-6"
          :disabled="inputValue !== 'DELETE'"
          @click="handleConfirm"
        >
          Delete
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
