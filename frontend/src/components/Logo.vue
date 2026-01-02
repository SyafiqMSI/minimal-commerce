<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: null
  }
});

const logoStyle = computed(() => {
  return props.size
    ? { width: 'auto', height: props.size }
    : { width: 'auto', height: '3rem' };
});
</script>

<template>
  <router-link to="/" class="transition-opacity duration-300 backdrop-blur-md bg-white/10 rounded-lg p-2">
    <img src="/logo.png" alt="E-Commerce" :style="logoStyle" />
  </router-link>
</template>
