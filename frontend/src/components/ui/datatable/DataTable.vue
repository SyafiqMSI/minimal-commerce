<script setup lang="ts">
import type {
  Column,
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
} from "@tanstack/vue-table";
import {
  FlexRender,
  getCoreRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table";
import { ref, computed, h } from "vue";
import { Checkbox } from "@/components/ui/checkbox";
import { valueUpdater } from "@/lib/utils";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import DataTablePagination from "./DataTablePagination.vue";
import DataTableToolbar from "./DataTableToolbar.vue";
import DataTableSkeleton from "./DataTableSkeleton.vue";

interface DataTableProps {
  columns: ColumnDef<any, any>[];
  data: any[];
  hideToolbar?: boolean;
  isLoading?: boolean;
  currentPage?: number;
  lastPage?: number;
  perPage?: number;
  total?: number;
  isServerSide?: boolean;
  enableBulkActions?: boolean;
}

const props = withDefaults(defineProps<DataTableProps>(), {
  isServerSide: false,
  enableBulkActions: false,
});

const emit = defineEmits<{
  (e: "update:page", value: number): void;
  (e: "update:perPage", value: number): void;
}>();

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref<Record<string, boolean>>({});

const selectedRows = computed(() => {
  const selected = Object.keys(rowSelection.value).map(
    (key) => props.data[parseInt(key)]
  );
  return selected;
});

const hasSelectedRows = computed(() => {
  const count = Object.keys(rowSelection.value).length;
  return count > 0;
});

const advancedFilter = (row: any, columnId: string, filterValue: any) => {
  const cellValue = row.getValue(columnId);

  if (cellValue === null || cellValue === undefined) {
    return false;
  }

  if (typeof filterValue === "string") {
    return String(cellValue).toLowerCase().includes(filterValue.toLowerCase());
  }

  if (
    typeof filterValue === "object" &&
    filterValue !== null &&
    filterValue.value !== undefined
  ) {
    let rowValue = String(cellValue);
    let searchValue = filterValue.value;

    if (!filterValue.matchCase) {
      rowValue = rowValue.toLowerCase();
      searchValue = searchValue.toLowerCase();
    }

    if (filterValue.matchEntireText) {
      return rowValue === searchValue;
    } else {
      return rowValue.includes(searchValue);
    }
  }

  return true;
};

const computedColumns = computed(() => {
  const baseColumns = props.columns.map((col) => ({
    ...col,
    filterFn: advancedFilter,
  }));

  if (!props.enableBulkActions) {
    return baseColumns;
  }

  const selectColumn: ColumnDef<any, any> = {
    id: "select",
    header: ({ table }) =>
      h("div", { class: "px-1" }, [
        h(Checkbox, {
          modelValue: table.getIsAllPageRowsSelected(),
          "onUpdate:modelValue": (value: boolean) => {
            table.toggleAllPageRowsSelected(!!value);
          },
          "aria-label": "Select all",
          indeterminate:
            table.getIsSomePageRowsSelected() &&
            !table.getIsAllPageRowsSelected(),
        }),
      ]),
    cell: ({ row }) =>
      h("div", { class: "px-1" }, [
        h(Checkbox, {
          modelValue: row.getIsSelected(),
          "onUpdate:modelValue": (value: boolean) => {
            row.toggleSelected(!!value);
          },
          "aria-label": "Select row",
        }),
      ]),
    enableSorting: false,
    enableHiding: false,
    size: 50,
  };

  return [selectColumn, ...baseColumns];
});

const table = useVueTable({
  get data() {
    return props.data;
  },
  get columns() {
    return computedColumns.value;
  },
  initialState: {
    pagination: {
      pageSize: 5,
    },
  },
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get rowSelection() {
      return rowSelection.value;
    },
  },
  enableRowSelection: props.enableBulkActions,
  manualPagination: props.isServerSide,
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => {
    valueUpdater(updaterOrValue, rowSelection);
  },
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFacetedRowModel: getFacetedRowModel(),
  getFacetedUniqueValues: getFacetedUniqueValues(),
  ...(props.isServerSide
    ? {}
    : {
        getPaginationRowModel: getPaginationRowModel(),
      }),
});

const clearSelection = () => {
  rowSelection.value = {};
};

const executeBulkAction = async (action: (selectedRows: any[]) => Promise<void>) => {
  try {
    await action(selectedRows.value)
    clearSelection()
  } catch (error) {
    console.error('Bulk action failed:', error)
  }
};

const isActionColumn = (column?: Column<any, any>) => {
  if (!column) return false;
  const id = (column.id ?? "").toString().toLowerCase();
  const meta = column.columnDef?.meta as { isActionColumn?: boolean; stickyActionColumn?: boolean } | undefined;
  return Boolean(meta?.isActionColumn || meta?.stickyActionColumn || id === "actions" || id === "action" || id === "aksi");
};

const getStickyColumnClass = (column?: Column<any, any>) => {
  return isActionColumn(column) ? "sticky-action-column" : undefined;
};

defineExpose({
  table,
  selectedRows,
  hasSelectedRows,
  executeBulkAction,
});
</script>

<template>
  <div class="space-y-4">
    <DataTableToolbar v-if="!hideToolbar && !isLoading" :table="table">
      <template #actions>
        <!-- Bulk Actions -->
        <div v-if="enableBulkActions && hasSelectedRows" class="lg:flex justify-center items-center gap-4">
          <span class="text-sm text-muted-foreground block mb-2 lg:mb-0">
            {{ Object.keys(rowSelection).length }} item terpilih
          </span>
          <div class="flex flex-wrap sm:flex-row sm:items-center gap-2 mr-4">          
            <slot
              name="bulk-actions"
              :selectedRows="selectedRows"
              :executeBulkAction="executeBulkAction"
            >
            </slot>
          </div>
        </div>

        <!-- Regular toolbar actions -->
        <div v-else class="h-full flex flex-wrap sm:flex-row sm:items-start justify-start items-center gap-2">
          <slot name="toolbar-actions"></slot>
        </div>
      </template>
    </DataTableToolbar>

    <DataTableSkeleton v-if="isLoading" :columns="computedColumns.length" :rows="5" />

    <div v-else class="rounded-md border">
      <div class="datatable-scroll">
        <Table class="datatable-table">
          <TableHeader class="sticky-header">
            <TableRow
              v-for="headerGroup in table.getHeaderGroups()"
              :key="headerGroup.id"
            >
              <TableHead
                v-for="header in headerGroup.headers"
                :key="header.id"
                :class="getStickyColumnClass(header.column)"
              >
                <FlexRender
                  v-if="!header.isPlaceholder"
                  :render="header.column.columnDef.header"
                  :props="header.getContext()"
                />
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="table.getRowModel().rows?.length">
              <TableRow
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                :data-state="row.getIsSelected() && 'selected'"
              >
                <TableCell
                  v-for="cell in row.getVisibleCells()"
                  :key="cell.id"
                  :class="getStickyColumnClass(cell.column)"
                >
                  <FlexRender
                    :render="cell.column.columnDef.cell"
                    :props="cell.getContext()"
                  />
                </TableCell>
              </TableRow>
            </template>
            <TableRow v-else>
              <TableCell :colspan="computedColumns.length" class="h-24 text-center">
                Tidak ada data
              </TableCell>
            </TableRow>
          </TableBody>
          <TableHeader>
            <TableRow
              v-for="headerGroup in table.getHeaderGroups()"
              :key="`bottom-${headerGroup.id}`"
            >
              <TableHead
                v-for="header in headerGroup.headers"
                :key="`bottom-${header.id}`"
                :class="getStickyColumnClass(header.column)"
              >
                <FlexRender
                  v-if="!header.isPlaceholder"
                  :render="header.column.columnDef.header"
                  :props="header.getContext()"
                />
              </TableHead>
            </TableRow>
          </TableHeader>
        </Table>
      </div>
    </div>

    <!-- Conditional pagination component -->
    <DataTablePagination
      :table="table"
      :is-server-side="props.isServerSide"
      v-bind="props.isServerSide
        ? {
            currentPage: props.currentPage,
            lastPage: props.lastPage,
            perPage: props.perPage,
            total: props.total,
          }
        : {}"
      @update:page="emit('update:page', $event)"
      @update:perPage="emit('update:perPage', $event)"
    />
  </div>
</template>

<style scoped>
.datatable-scroll {
  position: relative;
  --datatable-action-width: 4rem;
  --datatable-header-vertical-padding: 0.75rem;
}

:deep(.datatable-scroll > div) {
  max-height: 70vh;
  overflow-y: auto;
}

:deep(.sticky-header) {
  padding-right: var(--datatable-header-extra-width);
}

:deep(.sticky-header th) {
  position: sticky;
  top: 0;
  z-index: 6;
  background-color: var(--muted);
  border-bottom: 1px solid var(--border);
  padding-top: var(--datatable-header-vertical-padding);
  padding-bottom: var(--datatable-header-vertical-padding);
}

:deep(th.sticky-action-column),
:deep(td.sticky-action-column) {
  position: sticky;
  right: 0;
  background-color: var(--muted);
  border-left: 1px solid var(--border);
  width: var(--datatable-action-width);
  min-width: var(--datatable-action-width);
  max-width: var(--datatable-action-width);
}

:deep(th.sticky-action-column) {
  z-index: 8;
  box-shadow: -8px 0 12px -10px rgba(48, 47, 47, 0.35);
  border-bottom: 1px solid var(--border);
}

:deep(td.sticky-action-column) {
  z-index: 5;
  box-shadow: -8px 0 12px -10px rgba(48, 47, 47, 0.3);
  border-bottom: 1px solid var(--border);
}
</style>