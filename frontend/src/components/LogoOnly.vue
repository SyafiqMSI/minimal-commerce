<script setup>
import Image from "@/components/Image.vue";
const LogoOnly = "/logo.png";
</script>

<template>
  <router-link to="/" class="transition-opacity duration-300 backdrop-blur-md bg-white/10 rounded-lg p-1.5">
    <Image :src="LogoOnly" alt="Logo" class="h-8 rounded-lg" />
  </router-link>
</template>