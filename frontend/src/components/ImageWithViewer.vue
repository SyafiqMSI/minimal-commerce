<script setup lang="ts">
import { computed, ref } from 'vue'
import Image from './Image.vue'

interface Props {
  src: string
  alt?: string
  class?: string
  width?: string
  height?: string
}

const props = withDefaults(defineProps<Props>(), {
  alt: 'Image',
  class: '',
  width: 'w-16',
  height: 'h-16'
})

const hasError = ref(false)

const viewerOptions = {
  zIndex: 9999,
  toolbar: false,
  title: false,
  movable: true,
  zoomable: true,
  rotatable: true,
  scalable: true,
  fullscreen: true,
}

const imageClass = computed(() => `${props.width} ${props.height} object-cover rounded ${hasError.value ? '' : ''} ${props.class}`)

const handleError = () => {
  hasError.value = true
}
</script>

<template>
  <div v-viewer="hasError ? null : viewerOptions" class="image-wrapper">
    <Image
      :src="src"
      :alt="alt"
      :class="imageClass"
      class="cursor-pointer"
      @error="handleError"
    />
  </div>
</template>