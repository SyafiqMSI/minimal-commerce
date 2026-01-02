<script setup>
import { SheetContent } from '@/components/ui/sheet';
import { ref, watchEffect, computed } from "vue";
import { useRoute } from "vue-router";
import { Button } from '@/components/ui/button';
import Logo from '@/components/Logo.vue';
import { useAuthStore } from '@/stores/auth';
import { ChevronDown, ChevronRight } from "lucide-vue-next";

const props = defineProps({
  navigationItems: {
    type: Array,
    default: []
  },
  accountItems: {
    type: Array,
    default: []
  },
  setActiveItem: {
    type: Function,
    default: () => {}
  },
  activeItem: {
    type: String,
    default: ''
  },
  onClose: {
    type: Function,
    default: () => {},
  },
});

const route = useRoute();
const expanded = ref({});
const auth = useAuthStore();
const isFintech = computed(() => auth.user?.role === 'Fintech');

function isActiveRoute(currentPath, targetRoute) {
  if (currentPath === targetRoute) return true;
  
  const segments = targetRoute.split('/').filter(Boolean);
  if (segments.length <= 1) {
    return false;
  }
  
  if (currentPath.startsWith(targetRoute + "/")) {
    const remainder = currentPath.slice(targetRoute.length + 1);
    const nextSegment = remainder.split('/')[0];
    if (nextSegment === 'create') {
      return false;
    }
    return true;
  }
  
  return false;
}

function isActive(item) {
  if (item.children && item.children.length) {
    return item.children.some((child) =>
      isActiveRoute(route.path, child.route)
    );
  }
  return isActiveRoute(route.path, item.route);
}

function getExpandKey(item) {
  return item.id ?? item.route + item.label;
}

function setExpanded() {
  props.navigationItems.forEach((item) => {
    if (item.children && item.children.length) {
      expanded.value[getExpandKey(item)] = isActive(item);
    }
  });
}

watchEffect(() => {
  setExpanded();
});

function toggleExpand(item) {
  const key = getExpandKey(item);
  expanded.value[key] = !expanded.value[key];
}

function isExpanded(item) {
  return expanded.value[getExpandKey(item)];
}
</script>

<template>
  <SheetContent
    side="left"
    class="p-0 w-72 border-none gap-0 h-full min-h-[100vh] supports-[height:100svh]:h-[100svh] supports-[height:100dvh]:h-[100dvh] supports-[min-height:100svh]:min-h-[100svh] supports-[min-height:100dvh]:min-h-[100dvh] bg-card"
  >
    <div
      class="relative flex flex-col h-full min-h-full overflow-y-auto bg-gradient-to-b from-primary/10 via-primary/5 to-transparent"
    >
      <div class="relative z-10 flex flex-col h-full">
        <div class="p-4 flex justify-center">
          <Logo :variant="$root.theme === 'dark' ? 'white' : 'colored'" size="3.5rem"/>
        </div>

        <div class="flex flex-col gap-2 p-2">
          <p class="text-xs font-medium text-foreground dark:text-white px-3 py-2 transition-opacity duration-300">Navigation</p>

          <template v-for="item in navigationItems" :key="getExpandKey(item)">
            <div v-if="item.children" class="flex flex-col">
              <button
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-foreground dark:text-white transition-colors w-full hover:bg-primary/10 dark:hover:bg-accent/70"
                @click="toggleExpand(item)"
              >
                <div class="flex items-center gap-3">
                  <component :is="item.icon" class="w-5 h-5" />
                  <span class="text-sm">{{ item.label }}</span>
                </div>
                <span>
                  <ChevronDown v-if="isExpanded(item)" class="w-4 h-4" />
                  <ChevronRight v-else class="w-4 h-4" />
                </span>
              </button>
              <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-[500px] opacity-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="max-h-[500px] opacity-100"
                leave-to-class="max-h-0 opacity-0"
              >
                <div v-if="isExpanded(item)" class="ml-5 pl-1 mt-2 border-l-3 border-primary/30 dark:border-white/30 overflow-hidden">
                  <router-link
                    v-for="child in item.children"
                    :key="child.route"
                    :to="isFintech ? child.route.replace('/admin', '/enumerator') : child.route"
                    class="flex items-center mb-1 gap-3 px-3 py-2 rounded-md transition-all duration-300"
                    :class="[
                      isActiveRoute(route.path, (isFintech ? child.route.replace('/admin', '/enumerator') : child.route))
                        ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/30 dark:border-white/30 shadow-[inset_0_1px_0_rgba(59,130,246,0.2)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]'
                        : 'text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70'
                    ]"
                    @click="props.onClose"
                  >
                    <component :is="child.icon" class="w-4 h-4" />
                    <span class="transition-opacity duration-300 text-sm">{{ child.label }}</span>
                  </router-link>
                </div>
              </Transition>
            </div>

            <router-link
              v-else
              :to="isFintech ? item.route.replace('/admin', '/enumerator') : item.route"
              class="flex items-center justify-between gap-3 px-3 py-2 rounded-md transition-all duration-300"
              :class="[
                props.isCollapsed ? 'justify-center' : '',
                isActiveRoute(route.path, (isFintech ? item.route.replace('/admin', '/enumerator') : item.route))
                  ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/30 dark:border-white/30 shadow-[inset_0_1px_0_rgba(59,130,246,0.2)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]'
                  : 'text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70',
              ]"
              @click="props.onClose"
            >
              <div class="flex items-center gap-3">
                <span class="w-5 h-5 flex items-center justify-center">
                  <component :is="item.icon" class="w-5 h-5" />
                </span>
                <span v-if="!props.isCollapsed" class="transition-opacity duration-300 text-sm">{{ item.label }}</span>
              </div>
              <span
                v-if="item.badge && item.badge.value && !props.isCollapsed"
                class="bg-primary text-primary-foreground text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 ml-auto"
              >
                {{ item.badge.value > 99 ? '99+' : item.badge.value }}
              </span>
              <span
                v-if="item.badge && item.badge.value && props.isCollapsed"
                class="absolute -top-1 -right-1 bg-primary text-primary-foreground text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"
              >
                {{ item.badge.value > 99 ? '99+' : item.badge.value }}
              </span>
            </router-link>
          </template>
        </div>

        <div class="flex flex-col gap-2 p-2 mt-auto">
          <p class="text-xs font-medium text-foreground dark:text-white px-3 py-2 transition-opacity duration-300">Account</p>
          <template v-for="item in accountItems" :key="item.id">
            <router-link
              v-if="item.route"
              :to="item.route"
              class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-300"
              :class="[
                activeItem === item.id
                  ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/30 dark:border-white/30 shadow-[inset_0_1px_0_rgba(59,130,246,0.2)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]'
                  : 'text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70'
              ]"
              @click="() => { setActiveItem(item.id); props.onClose(); }"
            >
              <span class="w-5 h-5 flex items-center justify-center">
                <component :is="item.icon" class="w-5 h-5" />
              </span>
              <span class="text-sm">{{ item.label }}</span>
            </router-link>

            <Button
              v-else-if="item.action"
              variant="ghost"
              class="w-full justify-start gap-3 px-3 py-2 h-auto"
              :class="[
                activeItem === item.id
                  ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/30 dark:border-white/30 shadow-[inset_0_1px_0_rgba(59,130,246,0.2)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]'
                  : 'text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70'
              ]"
              @click="() => { item.action(); props.onClose(); }"
            >
              <span class="w-5 h-5 flex items-center justify-center">
                <component :is="item.icon" class="w-5 h-5" />
              </span>
              <span class="text-sm">{{ item.label }}</span>
            </Button>
          </template>
        </div>
      </div>
    </div>
  </SheetContent>
</template>