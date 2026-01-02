<script setup>
import { ChevronLeft, ChevronDown, ChevronRight } from "lucide-vue-next";
import { ref, watchEffect, computed } from "vue";
import { useRoute } from "vue-router";
import { Button } from "@/components/ui/button";
import { useAuthStore } from '@/stores/auth';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import Logo from "@/components/Logo.vue";
import LogoOnly from "@/components/LogoOnly.vue";

const props = defineProps({
  navigationItems: {
    type: Array,
    default: [], 
  },
  accountItems: {
    type: Array,
    default: [],
  },
  activeItem: {
    type: String,
    default: null,
  },
  isCollapsed: Boolean,
  toggleSidebar: Function,
});

const route = useRoute();
const expanded = ref({});

const auth = useAuthStore();  

const floatingCardPosition = ref({ x: 0, y: 0 });
const floatingCardItem = ref(null);
const hoveredItem = ref(null);

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

function handleFloatingEnter(event, item) {
  if (props.isCollapsed) {
    hoveredItem.value = getExpandKey(item);
    floatingCardItem.value = item;

    const rect = event.currentTarget.getBoundingClientRect();
    floatingCardPosition.value = {
      x: rect.right,
      y: rect.top,
    };
  }
}

function handleFloatingLeave() {
  hoveredItem.value = null;
  floatingCardItem.value = null;
}
</script>

<template>
  <TooltipProvider>
    <div class="p-1 h-screen sticky top-0 left-0 hidden md:flex flex-col z-20">  
      <div
        :class="[
          'relative overflow-hidden rounded-2xl h-full transition-all duration-300 ease-in-out overflow-y-auto overflow-x-hidden font-medium flex flex-col border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm',
          props.isCollapsed ? 'w-24' : 'w-64'
        ]"
      >
        <div class="fixed inset-y-0 left-1 bg-gradient-to-b from-primary/10 via-primary/5 to-transparent rounded-2xl pointer-events-none"
          :class="[props.isCollapsed ? 'w-24' : 'w-64']"
        ></div>
        
        <div class="relative z-10 h-full flex flex-col">
        <div class="p-4 flex justify-center items-center min-h-16">
          <Logo size="3.5rem" :class="{ 'opacity-0 scale-75': props.isCollapsed }" class="transition-all duration-300" />
          <LogoOnly class="text-foreground dark:text-white absolute z-10 transition-all duration-300" :class="{ 'opacity-0 scale-125': !props.isCollapsed }" />
        </div>

        <div class="flex-1 flex flex-col gap-2.5 p-2">
          <p class="text-xs font-medium text-foreground dark:text-white px-3 py-2 transition-opacity duration-300" :class="{ 'text-center px-0': props.isCollapsed }">Navigation</p>

          <template v-for="item in navigationItems" :key="getExpandKey(item)">
            <div
              v-if="item.children"
              class="relative flex flex-col"
              @mouseenter="handleFloatingEnter($event, item)"
              @mouseleave="handleFloatingLeave"
            >
              <Tooltip v-if="props.isCollapsed">
                <TooltipTrigger as-child>
                  <button
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70 transition-all duration-300 w-full justify-center"
                    @click="null"
                  >
                    <div class="flex items-center gap-3">
                      <component :is="item.icon" class="w-5 h-5" />
                    </div>
                  </button>
                </TooltipTrigger>
                <TooltipContent side="top">
                  <p>{{ item.label }}</p>
                </TooltipContent>
              </Tooltip>

              <button
                v-else
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70 transition-all duration-300 w-full"
                @click="toggleExpand(item)"
              >
                <div class="flex items-center gap-3">
                  <component :is="item.icon" class="w-5 h-5" />
                  <span class="transition-opacity duration-300 text-start text-sm">{{ item.label }}</span>
                </div>
                <span class="transition-opacity duration-300">
                  <ChevronDown v-if="isExpanded(item)" class="w-4 h-4" />
                  <ChevronRight v-else class="w-4 h-4" />
                </span>
              </button>

              <div 
                v-if="!props.isCollapsed" 
                class="ml-5 pl-1 border-l-3 border-border dark:border-white/30 transition-all duration-300 overflow-hidden"
                :class="[
                  isExpanded(item) ? 'mt-2 max-h-[500px] opacity-100' : 'max-h-0 opacity-0'
                ]"
              > 
                <router-link
                  v-for="child in item.children"
                  :key="child.route"
                  :to="child.route"
                  class="flex items-center mb-1 gap-3 px-3 py-2 rounded-md text-foreground dark:text-white transition-all duration-300"
                  :class="{
                    'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': isActiveRoute(route.path, child.route),
                    'hover:bg-primary/10 dark:hover:bg-accent/70': !isActiveRoute(route.path, child.route),
                  }"
                >
                  <component :is="child.icon" class="w-4 h-4" />
                  <span class="transition-opacity duration-300 text-sm">{{ child.label }}</span>
                </router-link>
              </div>

              <div
                v-if="props.isCollapsed && hoveredItem === getExpandKey(item)"
                class="fixed w-48 bg-card dark:bg-gradient-to-b dark:from-sidebar dark:to-sidebar/90 border rounded-lg shadow-lg z-50 transition-opacity duration-200"
                :style="{
                  top: `${floatingCardPosition.y}px`,
                  left: `${floatingCardPosition.x}px`,
                  opacity: hoveredItem === getExpandKey(item) ? 1 : 0
                }"
              >
                <div class="p-2">
                  <router-link
                    v-for="child in floatingCardItem?.children"
                    :key="child.route"
                    :to="child.route"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white transition-all duration-300"
                    :class="{ 
                      'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': isActiveRoute(route.path, child.route),
                      'hover:bg-primary/10 dark:hover:bg-accent/70': !isActiveRoute(route.path, child.route),
                      }"
                  >
                    <component :is="child.icon" class="w-4 h-4" />
                    <span class="text-sm">{{ child.label }}</span>
                  </router-link>
                </div>
              </div>
            </div>

            <template v-else>
              <Tooltip v-if="props.isCollapsed">
                <TooltipTrigger as-child>
                  <router-link
                    :to="item.route"
                    class="relative flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white transition-all duration-300 justify-center"
                    :class="[
                      isActive(item) ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm' : 'hover:bg-primary/10 dark:hover:bg-accent/70',
                    ]"
                  >
                    <span class="w-5 h-5 flex items-center justify-center">
                      <component :is="item.icon" class="w-5 h-5" />
                    </span>
                  </router-link>
                </TooltipTrigger>
                <TooltipContent side="right">
                  <p>{{ item.label }}</p>
                </TooltipContent>
              </Tooltip>

              <router-link
                v-else
                :to="item.route"
                class="relative flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white transition-all duration-300"
                :class="[
                  isActiveRoute(route.path, item.route) ? 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm' : 'hover:bg-primary/10 dark:hover:bg-accent/70',
                ]"
              >
                <span class="w-5 h-5 flex items-center justify-center">
                  <component :is="item.icon" class="w-5 h-5" />
                </span>
                <span class="transition-opacity duration-300 text-sm">{{ item.label }}</span>
              </router-link>
            </template>
          </template>
        </div>

        <div class="flex flex-col gap-2.5 p-2 mt-auto">
          <p class="text-xs font-medium text-foreground dark:text-white px-3 py-2 transition-opacity duration-300" :class="{ 'text-center px-0': props.isCollapsed }">Account</p>
          <template v-for="item in accountItems" :key="item.id">
            <template v-if="item.route">
              <Tooltip v-if="props.isCollapsed">
                <TooltipTrigger as-child>
                  <router-link
                    :to="item.route"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70 transition-all duration-300 justify-center"
                    :class="{ 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': props.activeItem === item.id }"
                    @click="() => { props.activeItem = item.id; }"
                  >
                    <span class="w-5 h-5 flex items-center justify-center">
                      <component :is="item.icon" class="w-5 h-5" />
                    </span>
                  </router-link>
                </TooltipTrigger>
                <TooltipContent side="right">
                  <p>{{ item.label }}</p>
                </TooltipContent>
              </Tooltip>

              <router-link
                v-else
                :to="item.route"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70 transition-all duration-300"
                :class="{ 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': props.activeItem === item.id }"
                @click="() => { props.activeItem = item.id; }"
              >
                <span class="w-5 h-5 flex items-center justify-center">
                  <component :is="item.icon" class="w-5 h-5" />
                </span>
                <span class="transition-opacity duration-300 text-sm">{{ item.label }}</span>
              </router-link>
            </template>

            <template v-else-if="item.action">
              <Tooltip v-if="props.isCollapsed">
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    class="w-full justify-center gap-3 px-3 py-2 h-auto text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70"
                    :class="{ 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': props.activeItem === item.id }"
                    @click="() => { item.action(); }"
                  >
                    <span class="w-5 h-5 flex items-center justify-center">
                      <component :is="item.icon" class="w-5 h-5" />
                    </span>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="right">
                  <p>{{ item.label }}</p>
                </TooltipContent>
              </Tooltip>

              <Button
                v-else
                variant="ghost"
                class="w-full justify-start gap-3 px-3 py-2 h-auto text-foreground dark:text-white hover:bg-primary/10 dark:hover:bg-accent/70"
                :class="{ 'bg-primary/15 text-primary dark:bg-white/20 dark:text-white backdrop-blur-md border border-primary/20 dark:border-white/30 shadow-sm': props.activeItem === item.id }"
                @click="() => { item.action(); }"
              >
                <span class="w-5 h-5 flex items-center justify-center">
                  <component :is="item.icon" class="w-5 h-5" />
                </span>
                <span class="transition-opacity duration-300 text-sm">{{ item.label }}</span>
              </Button>
            </template>
          </template>
        </div>
        </div>
      </div>
    </div>
  </TooltipProvider>
</template>