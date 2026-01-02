<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import SidebarDesktop from './SidebarDesktop.vue';
import SidebarMobile from './SidebarMobile.vue';
import Header from './Header.vue';
import { LogOut } from 'lucide-vue-next';
import Sheet from '@/components/ui/sheet/Sheet.vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute } from 'vue-router';
import { createAdminNavigationItems } from './adminNavigationItems.js';
import { createUserNavigationItems } from './userNavigationItems';
import { confirmModal } from '@/components/ui/confirmation-dialog';

const isSidebarOpen = ref(false);
const isCollapsed = ref(false);
const activeItem = ref('dashboard');
const isMobile = ref(false);

const isHideSidebar = computed(() => {
  return useRoute().meta.hideSidebar;
});

const route = useRoute();
const basePath = computed(() => {
  if (route.path.startsWith('/user')) {
    return '/user';
  }
  return '/admin';
});

const auth = useAuthStore();

const handleLogout = async () => {
  const confirmed = await confirmModal('Confirm Logout', 'Are you sure you want to logout?');
  if (confirmed) {
    await auth.logout();
    window.location.href = '/login';
  }
};

const adminNavigationItems = computed(() =>
  createAdminNavigationItems(basePath.value)
);

const userNavigationItems = computed(() =>
  createUserNavigationItems(basePath.value)
);

const navigationItems = computed(() => {
  if (auth.isAdmin) {
    return adminNavigationItems.value;
  }
  return userNavigationItems.value;
});

const accountItems = computed(() => [
  { id: 'logout', label: 'Logout', icon: LogOut, action: handleLogout },
]);

onMounted(() => {
  checkWindowSize();
  window.addEventListener('resize', checkWindowSize);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkWindowSize);
});

function checkWindowSize() {
  const wasMobile = isMobile.value;
  isMobile.value = window.innerWidth < 768;
  
  if (wasMobile && !isMobile.value) {
    isSidebarOpen.value = false;
  }
}

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value;
}

function toggleMobileSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value;
}

const setActiveItem = (id) => {
  activeItem.value = id;
};
</script>

<template>
  <main>
    <div class="min-h-screen flex bg-background">
      <SidebarDesktop
        v-if="!isHideSidebar"
        :navigationItems="navigationItems"
        :accountItems="accountItems"
        :setActiveItem="setActiveItem"
        :activeItem="activeItem"
        :isCollapsed="isCollapsed"
        :toggleSidebar="toggleSidebar"
        class="flex-shrink-0"
      />

      <div class="flex-1 flex flex-col min-w-0">
        <!-- Single Header for both desktop and mobile -->
        <Header
          :isCollapsed="isCollapsed"
          :toggleSidebar="toggleSidebar"
          :navigationItems="navigationItems"
          :isMobile="isMobile"
          :toggleMobileSidebar="toggleMobileSidebar"
        />
        
        <!-- Mobile Sidebar Overlay -->
        <Sheet
          v-if="isMobile"
          :open="isSidebarOpen"
          @update:open="(val) => (isSidebarOpen = val)"
        >
          <SidebarMobile
            v-if="!isHideSidebar"
            :isMobile="isMobile"
            :navigationItems="navigationItems"
            :accountItems="accountItems"
            :setActiveItem="setActiveItem"
            :activeItem="activeItem"
            :onClose="() => isSidebarOpen = false"
          />
        </Sheet>

        <div class="py-10 flex-1 px-5 sm:px-10 md:px-10 lg:px-10 xl:px-10 pb-10">
          <div class="container mx-auto max-w-6xl ">
            <router-view />
          </div>
        </div>

        <div class="py-8 text-sm text-foreground/70 text-center">
          2026 E-Commerce. All Rights Reserved.
        </div>
      </div>
    </div>
  </main>
</template>
