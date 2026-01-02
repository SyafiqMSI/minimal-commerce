<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { PanelLeftOpen, PanelLeftClose, Menu, AlertTriangle } from "lucide-vue-next";
import UserProfile from "@/components/UserProfile.vue";
import { Button } from "@/components/ui/button";
import GlobalMenuSearch from "@/components/GlobalMenuSearch.vue";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";
import { useRoute } from "vue-router";
import api from "@/lib/axios";

const isScrolled = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 0;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
  handleScroll();
});
onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});

const props = defineProps({
  isCollapsed: Boolean,
  toggleSidebar: Function,
  navigationItems: {
    type: Array,
    default: () => [],
  },
  isMobile: {
    type: Boolean,
    default: false,
  },
  toggleMobileSidebar: {
    type: Function,
    default: () => {},
  },
});

const route = useRoute();

const breadcrumbData = computed(() => {
  const currentPath = route.path;

  const toTitleCase = (text) => {
    if (!text) return "";
    return text.replace(/[\-_]+/g, " ").replace(/\b\w/g, (char) => char.toUpperCase());
  };

  let parent = undefined;
  let title = undefined;
  let parentLink = "/admin";

  for (const main of props.navigationItems) {
    if (Array.isArray(main.children) && main.children.length > 0) {
      const child = main.children.find((it) => it.route === currentPath);
      if (child) {
        parent = main.label;
        title = child.label;
        parentLink = main.route && main.route !== "#" ? main.route : "/admin";
        break;
      }
    }
    if (main.route === currentPath) {
      title = main.label;
      parent = undefined;
      parentLink = "/admin";
      break;
    }
  }

  if (!title) {
    if (currentPath.startsWith("/admin/")) {
      const parts = currentPath.split("/").filter(Boolean);
      const last = parts[parts.length - 1];
      title = toTitleCase(last);
      if (parts.length >= 3) {
        parent = toTitleCase(parts[parts.length - 2]);
        parentLink = `/${parts.slice(0, parts.length - 1).join("/")}`;
      }
    } else {
      title = "Dashboard";
    }
  }

  return { parent, title, parentLink };
});
</script>

<template>
  <div class="pl-0.2 w-full py-0.5 sticky top-0 z-10 pt-1">
    <header
      :class="[
        'relative overflow-hidden rounded-2xl transition-all duration-300 border border-border/50 bg-card/80 backdrop-blur-xl',
        isScrolled
          ? 'shadow-lg shadow-primary/5'
          : 'shadow-sm',
      ]"
    >
      <!-- Gradient Background Layer -->
      <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-transparent"></div>
      
      <!-- Content Container -->
      <div class="relative z-10">
      <div class="flex items-center justify-between h-16 pl-2 pr-6">
        <div class="flex items-center gap-2">
          <!-- Desktop sidebar toggle -->
          <Button
            v-if="!props.isMobile"
            variant="ghost"
            class="p-2 rounded-md mr-2"
            @click="props.toggleSidebar"
            aria-label="Toggle sidebar"
          >
            <PanelLeftClose v-if="!props.isCollapsed" class="w-5 h-5" />
            <PanelLeftOpen v-else class="w-5 h-5" />
          </Button>

          <!-- Mobile menu button -->
          <Button
            v-if="props.isMobile"
            variant="ghost"
            class="p-2 rounded-md mr-2"
            @click="props.toggleMobileSidebar"
            aria-label="Open mobile menu"
          >
            <Menu class="w-5 h-5" />
          </Button>
          <Breadcrumb class="hidden md:block">
            <BreadcrumbList>
              <BreadcrumbItem v-if="breadcrumbData.parent">
                <BreadcrumbLink as-child>
                  <router-link :to="breadcrumbData.parentLink || '/admin'">{{
                    breadcrumbData.parent
                  }}</router-link>
                </BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator v-if="breadcrumbData.parent" />
              <BreadcrumbItem>
                <BreadcrumbPage>{{ breadcrumbData.title }}</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
        <div class="flex-1 flex justify-center items-center">
        </div>
        <div class="flex gap-3">
          <GlobalMenuSearch :navigationItems="props.navigationItems" />
          <UserProfile />
        </div>
      </div>
      </div>
    </header>
  </div>
</template>

<style scoped>
</style>
