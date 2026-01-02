<script setup>
import { Moon, Sun } from "lucide-vue-next";
import { ref, onMounted, computed, inject } from "vue";
import { Toggle } from "@/components/ui/toggle";
import { useSidebar } from "@/components/ui/sidebar";

const theme = ref("dark");

let sidebarState = ref('expanded');
try {
  const { state } = useSidebar();
  sidebarState = state;
} catch (error) {
  console.log('Sidebar context not available, using default state');
}

const toggleTheme = () => {
  theme.value = theme.value === "light" ? "dark" : "light";
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", theme.value);
};

const toggleClasses = computed(() => {
  const baseClasses = sidebarState.value === 'collapsed' ? "w-full justify-center h-9" : "w-full justify-start h-9";
  const lightModeClasses = theme.value === "light" 
    ? "hover:bg-transparent hover:text-foreground !font-normal" 
    : "";
  return `${baseClasses} ${lightModeClasses}`;
});

onMounted(() => {
  const savedTheme = localStorage.getItem("theme") || "dark";
  theme.value = savedTheme;
  if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
  }
});
</script>

<template>
  <button
    @click="toggleTheme"
    class="flex items-center justify-center w-10 h-10 rounded-full bg-header text-header-foreground border border-foreground/10 hover:bg-accent/70 transition-colors p-0"
  >
    <Sun v-if="theme === 'light'" class="size-4" />
    <Moon v-else class="size-4" />
  </button>
</template>