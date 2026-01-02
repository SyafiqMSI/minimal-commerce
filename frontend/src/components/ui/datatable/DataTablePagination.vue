<script setup lang="ts">
import type { Table } from "@tanstack/vue-table";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import Button from "@/components/ui/button/Button.vue";
import {
  ChevronRightIcon,
  ArrowRightIcon,
  ChevronLeftIcon,
  ArrowLeftIcon,
} from "lucide-vue-next";

interface DataTablePaginationProps {
  table: Table<any>;
  isServerSide: boolean;
  currentPage?: number;
  lastPage?: number;
  perPage?: number;
  total?: number;
}

const props = defineProps<DataTablePaginationProps>();

const emit = defineEmits<{
  (e: 'update:page', value: number): void;
  (e: 'update:perPage', value: number): void;
}>();

import { ref, watch } from 'vue';

const jumpPage = ref(props.isServerSide ? props.currentPage || 1 : (props.table.getState().pagination.pageIndex + 1));

watch(() => props.currentPage, (val) => {
  if (props.isServerSide && typeof val === 'number') {
    jumpPage.value = val;
  }
});

watch(() => props.table.getState().pagination.pageIndex, (val) => {
  if (!props.isServerSide) {
    jumpPage.value = val + 1;
  }
});

function handleJumpPage() {
  let page = Number(jumpPage.value);
  let min = 1;
  let max = props.isServerSide ? (props.lastPage || 1) : props.table.getPageCount();
  if (isNaN(page)) page = min;
  if (page < min) page = min;
  if (page > max) page = max;
  jumpPage.value = page;
  if (props.isServerSide) {
    emit('update:page', page);
  } else {
    props.table.setPageIndex(page - 1);
  }
}
</script>

<template>
  <div
    class="flex flex-col gap-y-4 px-2 lg:flex-row lg:items-center lg:justify-between"
  >
    <div class="flex items-center gap-4">

      <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">Rows per page</p>
        <Select
            :model-value="props.isServerSide ? `${props.perPage}` : `${table.getState().pagination.pageSize}`"
            @update:model-value="(v) => props.isServerSide ? emit('update:perPage', parseInt(v)) : table.setPageSize(parseInt(v))"
          >
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue
              :placeholder="props.isServerSide ? `${props.perPage}` : `${table.getState().pagination.pageSize}`"
            />
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem
              v-for="pageSize in [5, 10, 20, 30, 40, 50, 100]"
              :key="pageSize"
              :value="`${pageSize}`"
            >
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="text-sm text-muted-foreground">
        {{ table.getFilteredRowModel().rows.length }} rows
      </div>

    </div>

    <div class="flex flex-col gap-y-4 lg:flex-row lg:items-center lg:space-x-6">

      <!-- Page info -->
      <div class="flex items-center gap-2 text-sm font-medium text-center">
        <span>Page</span>
        <input
          v-model="jumpPage"
          @keyup.enter="handleJumpPage"
          @blur="handleJumpPage"
          type="number"
          min="1"
          :max="props.isServerSide ? props.lastPage : table.getPageCount()"
          class="border rounded px-1 py-0.5 text-center focus:outline-none focus:ring focus:border-blue-400"
          :style="`height: 28px; width: ${String(jumpPage).length + 1.5}rem; min-width: 2.5rem; max-width: 8rem; transition:width 0.2s;`"
        />
        <span>of</span>
        <span v-if="props.isServerSide">{{ lastPage }}</span>
        <span v-else>{{ table.getPageCount() }}</span>
      </div>

      <!-- Pagination buttons -->
      <div class="flex items-center justify-center space-x-2">
        <Button
          variant="outline"
          class="hidden h-8 w-8 p-0 lg:flex"
          :disabled="props.isServerSide ? props.currentPage === 1 : !table.getCanPreviousPage()"
          @click="props.isServerSide ? emit('update:page', 1) : table.setPageIndex(0)"
        >
          <span class="sr-only">Go to first page</span>
          <ArrowLeftIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="h-8 w-8 p-0"
          :disabled="props.isServerSide ? props.currentPage === 1 : !table.getCanPreviousPage()"
          @click="props.isServerSide ? emit('update:page', props.currentPage - 1) : table.previousPage()"
        >
          <span class="sr-only">Go to previous page</span>
          <ChevronLeftIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="h-8 w-8 p-0"
          :disabled="props.isServerSide ? props.currentPage === props.lastPage : !table.getCanNextPage()"
          @click="props.isServerSide ? emit('update:page', props.currentPage + 1) : table.nextPage()"
        >
          <span class="sr-only">Go to next page</span>
          <ChevronRightIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="hidden h-8 w-8 p-0 lg:flex"
          :disabled="props.isServerSide ? props.currentPage === props.lastPage : !table.getCanNextPage()"
          @click="props.isServerSide ? emit('update:page', lastPage) : table.setPageIndex(table.getPageCount() - 1)"
        >
          <span class="sr-only">Go to last page</span>
          <ArrowRightIcon class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>
