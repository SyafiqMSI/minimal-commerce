<script setup lang="ts">
import { ref, computed } from "vue";
import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Checkbox } from "@/components/ui/checkbox";
import { Badge } from "@/components/ui/badge";
import { ChevronDown, X } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";

interface Props {
  modelValue: string[];
  items: string[];
  placeholder?: string;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: "Select...",
  loading: false,
});

const emit = defineEmits<{
  (e: "update:modelValue", value: string[]): void;
}>();

const open = ref(false);
const searchQuery = ref("");

const filteredItems = computed(() => {
  if (!searchQuery.value) return props.items;
  return props.items.filter((item) => item.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const toggleItem = (item: string) => {
  const newValue = [...props.modelValue];
  const index = newValue.indexOf(item);

  if (index > -1) {
    newValue.splice(index, 1);
  } else {
    newValue.push(item);
  }

  emit("update:modelValue", newValue);
};

const removeItem = (item: string) => {
  const newValue = props.modelValue.filter((v) => v !== item);
  emit("update:modelValue", newValue);
};

const clearAll = () => {
  emit("update:modelValue", []);
};

const selectAll = () => {
  emit("update:modelValue", [...props.items]);
};

const displayText = computed(() => {
  if (props.modelValue.length === 0) return props.placeholder;
  if (props.modelValue.length === 1) return props.modelValue[0];
  return `${props.modelValue.length} selected`;
});
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button variant="outline" role="combobox" :aria-expanded="open" class="w-full justify-between" :disabled="loading">
        <span class="truncate">{{ displayText }}</span>
        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[300px] p-0" align="start">
      <div class="flex flex-col">
        <!-- Search -->
        <div class="p-2 border-b">
          <input v-model="searchQuery" type="text" placeholder="Search..." class="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between p-2 border-b">
          <Button variant="ghost" size="sm" @click="selectAll" class="h-7 text-xs"> Select All </Button>
          <Button variant="ghost" size="sm" @click="clearAll" class="h-7 text-xs"> Clear All </Button>
        </div>

        <!-- Items -->
        <ScrollArea class="h-[200px]">
          <div class="p-2">
            <div v-for="item in filteredItems" :key="item" class="flex items-center space-x-2 p-2 hover:bg-accent rounded-md cursor-pointer" @click="toggleItem(item)">
              <Checkbox :model-value="modelValue.includes(item)" />
              <span class="text-sm">{{ item }}</span>
            </div>

            <div v-if="filteredItems.length === 0" class="p-4 text-center text-sm text-muted-foreground">No data found</div>
          </div>
        </ScrollArea>
      </div>
    </PopoverContent>
  </Popover>

  <!-- Selected badges -->
  <div v-if="modelValue.length > 0" class="flex flex-wrap gap-1 mt-2">
    <Badge v-for="item in modelValue" :key="item" variant="secondary" class="text-xs">
      {{ item }}
      <button @click.stop="removeItem(item)" class="ml-1 hover:text-destructive">
        <X class="h-3 w-3" />
      </button>
    </Badge>
  </div>
</template>
