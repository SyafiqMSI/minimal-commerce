<script setup lang="ts">
import type { Table } from "@tanstack/vue-table";
import { computed, ref, watch } from "vue";
import { XIcon, CheckIcon, ListFilter, Search } from "lucide-vue-next";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuCheckboxItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel
} from "@/components/ui/dropdown-menu";

interface DataTableToolbarProps {
  table: Table<any>;
}

const props = defineProps<DataTableToolbarProps>();
const searchInput = ref("");
let searchTimeout: NodeJS.Timeout | null = null;

const globalFilter = computed({
  get: () => props.table.getState().globalFilter ?? "",
  set: (val: string) => props.table.setGlobalFilter(val),
});

watch(searchInput, (newValue) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  searchTimeout = setTimeout(() => {
    globalFilter.value = newValue;
  }, 300);
});

const isFiltered = computed(() => globalFilter.value.length > 0);

const allColumns = computed(() => {
  return props.table.getAllColumns().filter(column =>
    typeof column.accessorFn !== "undefined" && column.getCanHide()
  );
});

const toggleColumnVisibility = (columnId: string) => {
  const column = props.table.getColumn(columnId);
  if (column) {
    column.toggleVisibility(!column.getIsVisible());
  }
};

const getColumnTitle = (column: any) => {
  const header = column.columnDef.header;
  if (typeof header === 'function') {
    const titleMatch = header.toString().match(/title:\s*"([^"]+)"/);
    return titleMatch ? titleMatch[1] : column.id;
  }
  return header?.toString() || column.id;
};
</script>

<template>
  <div class="flex flex-col gap-2 lg:flex-row lg:items-center justify-between mb-4">
    <div class="flex items-center space-x-2 order-2 lg:order-1 flex-1">
      <div class="relative">
        <Search class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
        <Input
          placeholder="Search..."
          v-model="searchInput"
          class="h-8 w-[150px] lg:w-[250px] pl-8"
        />
      </div>
      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="() => {
          searchInput = '';
          globalFilter = '';
        }"
      >
        Reset
        <XIcon class="ml-2 h-4 w-4" />
      </Button>
      
      <DropdownMenu v-if="allColumns.length > 0">
        <DropdownMenuTrigger as-child>
          <Button variant="outline" size="sm" class="h-8 flex items-center">
            <ListFilter class="h-4 w-4 mr-1" />
            Columns
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
          <DropdownMenuLabel>Show Columns</DropdownMenuLabel>
          <DropdownMenuSeparator />
          <div class="max-h-48 overflow-y-auto">
            <DropdownMenuCheckboxItem
              v-for="column in allColumns"
              :key="column.id"
              :checked="column.getIsVisible()"
              @select="toggleColumnVisibility(column.id)"
            >
              <div class="flex items-center">
                <div class="w-5 h-5 flex items-center justify-center mr-2">
                  <CheckIcon v-if="column.getIsVisible()" class="h-3 w-3" />
                </div>
                {{ getColumnTitle(column) }}
              </div>
            </DropdownMenuCheckboxItem>
          </div>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
    <div class="flex items-center space-x-2 order-1 lg:order-2">
      <slot name="actions"></slot>
    </div>
  </div>
</template>