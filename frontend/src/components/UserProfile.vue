<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { LogOut, Home } from 'lucide-vue-next';

import Badge from './ui/badge/Badge.vue';
import ThemeToggle from './ThemeToggle.vue';
import { useAuthStore } from '@/stores/auth';
import { confirmModal } from '@/components/ui/confirmation-dialog';

const auth = useAuthStore();
const router = useRouter();

const getInitials = computed(() => {
  const name = auth.user?.name || '';
  if (!name) return 'U';
  return name
    .split(' ')
    .map((part: string) => part[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
});

const displayName = computed(() => auth.user?.name || 'User');
const displayRole = computed(() => auth.user?.role || 'Guest');
const displayEmail = computed(() => (auth.user as any)?.email || '');
const dashboardPath = computed(() => {
  return '/';
});

const handleLogout = async () => {
  const confirmed = await confirmModal('Confirm Logout', 'Are you sure you want to logout?');
  if (confirmed) {
    await auth.logout();
    router.push('/login');
  }
};

</script>

<template>
  <div class="flex items-center gap-2">
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" class="relative h-10 w-10 rounded-full p-0">
          <div class="flex items-center justify-center w-10 h-10 rounded-full bg-header text-header-foreground text-sm font-medium border border-foreground/10 hover:bg-accent/70 transition-colors">
            <span class="leading-none">{{ getInitials }}</span>
          </div>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="min-w-48 max-w-72">
        <div class="flex items-start gap-2 p-2">
          <div class="flex flex-col space-y-1 leading-none w-full">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center">
                <p class="font-medium w-full">
                  {{ displayName }}
                </p>
                <Badge variant="secondary">{{ displayRole }}</Badge>
              </div>
            </div>
            <p class="text-sm text-muted-foreground break-words">
              {{ displayEmail }}
            </p>
          </div>
        </div>
        <DropdownMenuItem class="cursor-pointer" @click="router.push('/')">
          <div class="flex items-center gap-2">
            <Home class="w-4 h-4" />
            Landing Page
          </div>
        </DropdownMenuItem>
        <DropdownMenuItem class="cursor-pointer" @click="handleLogout">
          <div class="flex items-center gap-2 text-destructive">
            <LogOut class="w-4 h-4" />
            Logout
          </div>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
    <ThemeToggle />
  </div>
</template>