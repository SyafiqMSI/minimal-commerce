<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import CommandDialog from '@/components/ui/command/CommandDialog.vue';
import CommandInput from '@/components/ui/command/CommandInput.vue';
import CommandItem from '@/components/ui/command/CommandItem.vue';
import CommandList from '@/components/ui/command/CommandList.vue';
import CommandEmpty from '@/components/ui/command/CommandEmpty.vue';
import CommandGroup from '@/components/ui/command/CommandGroup.vue';
import { Search } from 'lucide-vue-next';

const props = defineProps({
  navigationItems: {
    type: Array,
    default: () => [],
  },
});

const open = ref(false);
const router = useRouter();

function flattenMenu(items) {
  const flat = [];
  for (const item of items) {
    if (item.children && item.children.length) {
      for (const child of item.children) {
        flat.push({ ...child, isChild: true, parentLabel: item.label });
      }
    } else {
      flat.push({ ...item, isChild: false });
    }
  }
  return flat;
}

const menuOptions = computed(() => flattenMenu(props.navigationItems));

function onSelectMenu(val) {
  const found = menuOptions.value.find((item) => item.route === val);
  if (found) {
    router.push(found.route);
    open.value = false;
  }
}
</script>

<template>
  <slot name="trigger">
    <button
      class="flex items-center gap-2 px-3 py-2 rounded-lg bg-header/40 border border-foreground/10 hover:bg-accent transition md:w-72 justify-start cursor-pointer dark:bg-muted"
      @click="open = true"
      aria-label="Search menu"
      type="button"
    >
      <Search class="w-4 h-4" />
      <span class="text-sm md:hidden">Search</span>
      <span class="text-sm hidden md:inline">Search menu...</span>
    </button>
  </slot>
  <CommandDialog v-model:open="open" title="Search Menu" description="Type to search menu...">
    <CommandInput placeholder="Search menu..." />
    <CommandList>
      <CommandEmpty>No menu found.</CommandEmpty>
      <CommandGroup heading="Menu">
        <template v-for="(item, index) in menuOptions" :key="item.route">
          <CommandItem
            :value="item.route"
            @select="onSelectMenu(item.route)"
          >
            <span class="flex items-center gap-2 px-3 py-1 font- text-foreground">
              <component :is="item.icon" class="w-4 h-4 text-foreground" />
              <span>{{ item.isChild ? `${item.parentLabel} / ${item.label}` : item.label }}</span>
            </span>
          </CommandItem>
          <div v-if="index < menuOptions.length - 1" class="h-px bg-border mx-2"></div>
        </template>
      </CommandGroup>
    </CommandList>
  </CommandDialog>
</template>

