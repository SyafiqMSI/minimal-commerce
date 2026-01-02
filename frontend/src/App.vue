<script setup>
import { ref, onMounted } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import ConfirmDialog from '@/components/ui/confirmation-dialog/ConfirmDialog.vue';
import DeleteConfirmDialog from '@/components/ui/confirmation-dialog/DeleteConfirmDialog.vue';
import LoadingPage from '@/components/LoadingPage.vue';
import DialogProvider from '@/components/ui/dialog/DialogProvider.vue';
import { useConfigStore } from '@/stores/config';
import "vue-sonner/style.css"

const isLoading = ref(true);
const configStore = useConfigStore();

const handleLoadingComplete = () => {
  isLoading.value = false;
};

onMounted(() => {
  configStore.fetchSurveyBaseUrl();
  configStore.fetchChatbotApiUrl();
});
</script>

<template>
  <LoadingPage v-if="isLoading" @loading-complete="handleLoadingComplete" />
  <template v-else>
    <DialogProvider>
      <Toaster />
      <ConfirmDialog />
      <DeleteConfirmDialog />
      <router-view />
    </DialogProvider>
  </template>
</template>