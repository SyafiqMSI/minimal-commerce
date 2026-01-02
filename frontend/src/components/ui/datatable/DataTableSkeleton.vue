<script setup>
import { Skeleton } from '@/components/ui/skeleton'
const props = defineProps({
  columns: { type: Number, required: true },
  rows: { type: Number, required: false, default: 5 }
})
</script>
<template>
  <div class="w-full space-y-4">
    <div class="flex flex-wrap sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center gap-2">
        <Skeleton class="h-8 w-full max-w-[250px] sm:w-[250px]" />
      </div>
      <div class="flex items-center gap-2 justify-end sm:justify-start">
        <Skeleton class="h-8 w-[80px] sm:w-[100px]" />
        <Skeleton class="h-8 w-[80px] sm:w-[100px]" />
      </div>
    </div>

    <div class="rounded-md border overflow-hidden">
      <div class="border-b bg-foreground/10">
        <div class="hidden md:flex items-center gap-4 p-4">
          <Skeleton v-for="i in columns" :key="i" class="h-4 flex-1 min-w-[80px]" />
        </div>
        <div class="md:hidden p-4">
          <Skeleton class="h-4 w-full" />
        </div>
      </div>

      <div class="divide-y">
        <div v-for="i in rows" :key="i" class="p-4">
          <div class="flex items-center gap-4">
            <Skeleton v-for="j in columns" :key="j" class="h-4 flex-1 min-w-[80px]" />
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4">
      <Skeleton class="h-4 w-full max-w-[200px] sm:w-[150px]" />
      <div class="flex items-center gap-2 justify-center sm:justify-end">
        <Skeleton class="h-8 w-8" />
        <Skeleton class="h-8 w-8" />
        <Skeleton class="h-8 w-8" />
        <Skeleton class="h-8 w-8" />
      </div>
    </div>
  </div>
</template>