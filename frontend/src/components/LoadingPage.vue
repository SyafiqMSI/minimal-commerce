<template>
  <div class="app-wrapper">
    <!-- Overlay Loading -->
    <div class="loading-container" :style="loadingStyle" @transitionend="onTransitionEnd">
      <div class="loader-container">
        <!-- Logo -->
        <div class="logo-container">
          <img src="/logo.png" alt="Logo" class="logo" />
        </div>
        <!-- Progress Bar -->
        <div class="progress-container">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: progress + '%' }"></div>
          </div>
          <div class="progress-text">{{ Math.round(progress) }}%</div>
        </div>
      </div>
    </div>

    <!-- Konten Halaman -->
    <main class="main-content" v-if="!hideContent">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from "vue";

const props = defineProps({
  duration: {
    type: Number,
    default: 2000
  },
  hideContent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['loading-complete']);

const isAnimating = ref(false);
const progress = ref(0);

const loadingStyle = computed(() => {
  return isAnimating.value
    ? {
        transform: 'translateY(-100%)',
        opacity: '0',
        backdropFilter: 'blur(0px)',
        WebkitBackdropFilter: 'blur(0px)',
        background: 'transparent',
        transition:
          'transform 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94), backdrop-filter 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94), background 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)'
      }
    : {
        transform: 'none',
        opacity: '1'
      }
})

const onTransitionEnd = () => {
  if (isAnimating.value) {
    emit('loading-complete');
  }
};

onMounted(() => {
  const interval = setInterval(() => {
    if (progress.value < 100) {
      progress.value += 2;
    } else {
      clearInterval(interval);
      setTimeout(() => {
        isAnimating.value = true;
      }, 200);
    }
  }, props.duration / 50);
});
</script>

<style scoped>
.app-wrapper {
  position: relative;
}

.loading-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: color-mix(in oklch, var(--color-background) 70%, transparent);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;

  transition:
    transform 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94),
    opacity 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94),
    backdrop-filter 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94),
    background 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);

  opacity: 1;
}

.loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding: 1rem;
  box-sizing: border-box;
  width: 100%;
  max-width: 90vw;
}

.logo-container {
  margin: 0;
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo {
  width: 260px;
  height: auto;
  max-height: 260px;
  object-fit: contain;
}

.progress-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  width: 260px;
  max-width: 100%;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: var(--color-muted);
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(
    90deg,
    var(--color-primary),
    color-mix(in oklch, var(--color-primary) 70%, var(--color-foreground) 30%)
  );
  border-radius: 4px;
  transition: width 0.3s ease;
  animation: shimmer 1.5s infinite;
}

.progress-text {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-primary);
}

@keyframes shimmer {
  0% { box-shadow: 0 0 5px var(--color-primary); }
  50% { box-shadow: 0 0 15px var(--color-primary), 0 0 20px var(--color-primary); }
  100% { box-shadow: 0 0 5px var(--color-primary);   }
}

@media (max-width: 768px) {
  .loader-container {
    gap: 1.25rem;
    padding: 1.5rem;
  }

  .logo {
    width: 180px;
    max-height: 180px;
  }

  .progress-container {
    width: 200px;
  }

  .progress-bar {
    height: 6px;
  }

  .progress-text {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .loader-container {
    gap: 1rem;
    padding: 1rem;
  }

  .logo {
    width: 140px;
    max-height: 140px;
  }

  .progress-container {
    width: 160px;
    gap: 0.5rem;
  }

  .progress-bar {
    height: 5px;
  }

  .progress-text {
    font-size: 12px;
  }
}

@media (max-width: 320px) {
  .loader-container {
    gap: 0.75rem;
    padding: 0.75rem;
  }

  .logo {
    width: 120px;
    max-height: 120px;
  }

  .progress-container {
    width: 140px;
  }

  .progress-text {
    font-size: 11px;
  }
}

@media (max-height: 500px) and (orientation: landscape) {
  .loader-container {
    gap: 0.75rem;
  }

  .logo {
    width: 100px;
    max-height: 100px;
  }

  .progress-container {
    width: 180px;
  }
}

@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .progress-bar {
    height: 8px; /* Slightly thicker on retina displays */
  }
}

@media (prefers-reduced-motion: reduce) {
  .loading-container {
    transition: opacity 0.3s ease;
  }
  
  .progress-fill {
    animation: none;
  }
}
</style>