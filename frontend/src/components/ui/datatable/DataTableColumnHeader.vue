<script setup lang="ts">
import type { Column } from "@tanstack/vue-table";
import { ref, watch } from "vue";

import {
  ArrowDownIcon,
  ArrowUpIcon,
  ArrowUpDownIcon,
  EyeClosedIcon,
  CaseSensitiveIcon,
  WholeWordIcon,
  FilterIcon,
} from "lucide-vue-next";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

interface DataTableColumnHeaderProps {
  column: Column<any, any>;
  title: string;
}

const props = defineProps<DataTableColumnHeaderProps>();

const filterValue = ref("");
const matchCase = ref(false);
const matchEntireText = ref(false);

const currentFilter = props.column.getFilterValue() as any;
if (typeof currentFilter === "object" && currentFilter !== null) {
  filterValue.value = currentFilter.value || "";
  matchCase.value = currentFilter.matchCase || false;
  matchEntireText.value = currentFilter.matchEntireText || false;
} else if (typeof currentFilter === "string") {
  filterValue.value = currentFilter;
}

watch([filterValue, matchCase, matchEntireText], () => {
  if (!filterValue.value) {
    props.column.setFilterValue(undefined);
    return;
  }
  props.column.setFilterValue({
    value: filterValue.value,
    matchCase: matchCase.value,
    matchEntireText: matchEntireText.value,
  });
});

const clearFilter = () => {
  filterValue.value = "";
  matchCase.value = false;
  matchEntireText.value = false;
};
</script>

<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<template>
  <div
    v-if="column.getCanSort()"
    :class="cn('flex items-center space-x-2', $attrs.class ?? '')"
  >
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          variant="ghost"
          size="sm"
          class="-ml-3 h-8 data-[state=open]:bg-accent"
        >
          <span>{{ title }}</span>
          <FilterIcon
            v-if="column.getIsFiltered()"
            class="ml-2 h-3.5 w-3.5 text-primary"
          />
          <ArrowDownIcon
            v-if="column.getIsSorted() === 'desc'"
            class="ml-2 h-4 w-4"
          />
          <ArrowUpIcon
            v-else-if="column.getIsSorted() === 'asc'"
            class="ml-2 h-4 w-4"
          />
          <ArrowUpDownIcon v-else class="ml-2 h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="start" class="w-[260px]">
        <div class="p-2" @keydown.stop>
          <Input
            v-model="filterValue"
            placeholder="Filter value..."
            class="h-8 mb-2"
          />
          <div class="flex gap-2 mb-2">
            <Button
              :variant="matchCase ? 'default' : 'outline'"
              size="sm"
              class="h-8 flex-1 px-2 text-xs"
              @click="matchCase = !matchCase"
            >
              <CaseSensitiveIcon class="mr-2 h-3.5 w-3.5" />
              Match Case
            </Button>
            <Button
              :variant="matchEntireText ? 'default' : 'outline'"
              size="sm"
              class="h-8 flex-1 px-2 text-xs"
              @click="matchEntireText = !matchEntireText"
            >
              <WholeWordIcon class="mr-2 h-3.5 w-3.5" />
              Entire Text
            </Button>
          </div>
          <Button
            v-if="filterValue"
            variant="secondary"
            size="sm"
            class="h-8 w-full text-xs"
            @click="clearFilter"
          >
            Clear Filter
          </Button>
        </div>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="column.toggleSorting(false)">
          <ArrowUpIcon class="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
          Asc
        </DropdownMenuItem>
        <DropdownMenuItem @click="column.toggleSorting(true)">
          <ArrowDownIcon class="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
          Desc
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="column.toggleVisibility(false)">
          <EyeClosedIcon class="mr-2 h-3.5 w-3.5 text-muted-foreground/70" />
          Hide
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>

  <div v-else :class="$attrs.class">
    {{ title }}
  </div>
</template>
