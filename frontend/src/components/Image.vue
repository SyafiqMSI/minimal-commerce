<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { ImageOff } from 'lucide-vue-next'

const props = defineProps({
  src: {
    type: String,
    required: true
  }
})

const imageUrl = ref(null)
const hasError = ref(false)

const loadImage = async () => {
  hasError.value = false
  imageUrl.value = null

  if (!props.src) {
    hasError.value = true
    return
  }

  imageUrl.value = props.src
}

const handleError = () => {
  hasError.value = true
  imageUrl.value = null
}

onMounted(loadImage)
watch(() => props.src, loadImage)
</script>

<template>
  <div class="relative">
    <img
      v-if="!hasError && imageUrl"
      v-bind="$attrs"
      :src="imageUrl"
      @error="handleError"
    />
    <div v-else class="flex items-center justify-center bg-background/50 rounded-lg w-full h-full" :style="$attrs.style">
      <ImageOff class="w-8 h-8 text-gray-300" />
    </div>
  </div>
</template>