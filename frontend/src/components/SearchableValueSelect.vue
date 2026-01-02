<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { ChevronsUpDown, Search, Loader2, Check } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps<{
  items: {
    label: string;
    value: string | undefined;
  }[];
  modelValue: string | undefined;
  loading?: boolean;
  disabled?: boolean;
  triggerStyle?: string;
  placeholder?: string;
}>();
const emit = defineEmits(["update:modelValue"]);

const search = ref("");
const isOpen = ref(false);
const anchorRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const listWidth = ref<string>('auto');
const dropdownAbove = ref(false);

const resetSearch = () => {
  search.value = "";
};

const filteredItems = computed(() => {
  if (!search.value) return props.items;
  const s = search.value.toLowerCase();
  return props.items.filter((item) => {
    const label = (item.label ?? "").toLowerCase();
    const value = typeof item.value === "string" ? item.value.toLowerCase() : "";
    return label.includes(s) || value.includes(s);
  });
});

const ITEM_HEIGHT = 40;
const VISIBLE_ITEMS = 5;
const BUFFER_ITEMS = 2;
const scrollTop = ref(0);

const uniqueItems = computed(() => {
  return filteredItems.value.map((item, index) => ({
    ...item,
    uniqueKey: `${item.value}-${index}`
  }));
});

const totalHeight = computed(() => uniqueItems.value.length * ITEM_HEIGHT);
const startIndex = computed(() => Math.max(0, Math.floor(scrollTop.value / ITEM_HEIGHT) - BUFFER_ITEMS));
const endIndex = computed(() => Math.min(
  uniqueItems.value.length,
  startIndex.value + VISIBLE_ITEMS + (BUFFER_ITEMS * 2)
));
const visibleItems = computed(() => uniqueItems.value.slice(startIndex.value, endIndex.value));
const offsetY = computed(() => startIndex.value * ITEM_HEIGHT);

const visibleContainerHeight = computed(() => {
  const count = Math.min(visibleItems.value.length, VISIBLE_ITEMS);
  return (count + 0.5) * ITEM_HEIGHT;
});

const handleScroll = (e: Event) => {
  const target = e.target as HTMLElement;
  scrollTop.value = target.scrollTop;
};

const selectedLabel = computed(() => {
  const selected = props.items.find(item => item.value === props.modelValue);
  return selected ? selected.label : (props.placeholder ?? "Select Item");
});

const isPlaceholder = computed(() => {
  return !props.items.find(item => item.value === props.modelValue);
});

const updateListWidth = () => {
  if (anchorRef.value) {
    listWidth.value = anchorRef.value.offsetWidth + 'px';
  }
};

const checkDropdownPosition = () => {
  if (!anchorRef.value) return;
  const rect = anchorRef.value.getBoundingClientRect();
  const dropdownHeight = visibleContainerHeight.value || 200; // fallback 200px
  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;
  dropdownAbove.value = spaceBelow < dropdownHeight && spaceAbove > spaceBelow;
};

const handleClickOutside = (event: MouseEvent) => {
  if (
    anchorRef.value && !anchorRef.value.contains(event.target as Node) &&
    dropdownRef.value && !dropdownRef.value.contains(event.target as Node)
  ) {
    isOpen.value = false;
  }
};

onMounted(() => {
  updateListWidth();
  window.addEventListener('resize', updateListWidth);
  window.addEventListener('resize', checkDropdownPosition);
  document.addEventListener('mousedown', handleClickOutside);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateListWidth);
  window.removeEventListener('resize', checkDropdownPosition);
  document.removeEventListener('mousedown', handleClickOutside);
});
watch(isOpen, (open) => {
  if (open) {
    nextTick(() => {
      updateListWidth();
      checkDropdownPosition();
      const selectedIndex = uniqueItems.value.findIndex(item => item.value === props.modelValue);
      if (dropdownRef.value) {
        const el = dropdownRef.value.querySelector('.virtual-scroll');
        if (el) {
          if (selectedIndex !== -1) {
            const targetScroll = Math.max(0, (selectedIndex - BUFFER_ITEMS) * ITEM_HEIGHT);
            el.scrollTop = targetScroll;
            scrollTop.value = targetScroll;
          } else {
            el.scrollTop = 0;
            scrollTop.value = 0;
          }
        }
      }
    });
  }
});

function selectItem(item: { label: string; value: string }) {
  if (!props.disabled && !props.loading) {
    emit('update:modelValue', item.value);
    isOpen.value = false;
    search.value = "";
    scrollTop.value = 0;
  }
}
</script>

<template>
  <div class="relative w-full">
    <button
      ref="anchorRef"
      :class="cn('bg-card text-foreground shadow-xs border border-input justify-between w-full flex items-center px-3 py-1 min-h-9 h-9 text-sm rounded-lg', triggerStyle, { 'text-muted-foreground': disabled, 'hover:bg-accent': !disabled })"
      :disabled="loading || disabled"
      @click="isOpen = !isOpen; if (isOpen) { resetSearch(); nextTick(updateListWidth); }"
      type="button"
    >
      <div class="flex items-center gap-2 truncate overflow-hidden whitespace-nowrap flex-1">
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        <span :class="{ 'text-muted-foreground': isPlaceholder || disabled }">
          {{ selectedLabel }}
        </span>
      </div>
      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
    </button>
    <transition name="fade">
      <div
        v-if="isOpen"
        ref="dropdownRef"
        class="absolute z-50 mt-1 rounded-lg border bg-popover text-popover-foreground shadow-md outline-none"
        :class="{ 'dropdown-above': dropdownAbove }"
        :style="{
          width: listWidth,
          minWidth: '0',
          maxWidth: 'none',
          top: dropdownAbove ? 'auto' : '100%',
          bottom: dropdownAbove ? '100%' : 'auto',
          marginBottom: dropdownAbove ? '0.5rem' : '',
          marginTop: dropdownAbove ? '' : '0.25rem'
        }"
      >
        <div class="relative w-full items-center">
          <input
            class="focus-visible:ring-0 rounded-md h-10 w-full border-b pl-9 pr-3 py-1 outline-none text-sm"
            placeholder="Search..."
            :disabled="loading || disabled"
            v-model="search"
            @keydown.esc="isOpen = false"
            @keydown.down.prevent="() => { const el = dropdownRef?.querySelector('.virtual-scroll'); if (el) el.scrollTop += ITEM_HEIGHT; }"
            @keydown.up.prevent="() => { const el = dropdownRef?.querySelector('.virtual-scroll'); if (el) el.scrollTop -= ITEM_HEIGHT; }"
          />
          <span class="absolute left-0 inset-y-0 flex items-center justify-center pl-3 pointer-events-none">
            <Search class="size-4 text-muted-foreground" />
          </span>
        </div>
        <div v-if="filteredItems.length === 0" class="p-3 text-center text-muted-foreground text-sm">No items found</div>
        <div
          v-else
          class="virtual-scroll relative h-[200px] overflow-y-auto"
          :style="{ height: visibleContainerHeight + 'px' }"
          @scroll="handleScroll"
        >
          <div :style="{ height: totalHeight + 'px' }">
            <div :style="{ transform: `translateY(${offsetY}px)` }">
              <div
                v-for="item in visibleItems"
                :key="item.uniqueKey"
                class="min-h-9 text-sm px-3 py-1.5 border-b border-border last:border-b-0 flex items-center cursor-pointer"
                :class="{ 'opacity-50 pointer-events-none': loading || disabled, 'hover:bg-accent': !disabled }"
                @click="selectItem(item)"
              >
                <span class="flex-1" :class="{ 'text-muted-foreground': disabled }">{{ item.label }}</span>
                <Check v-if="item.value === modelValue" class="ml-auto h-4 w-4" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>