<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import { CheckCircle2, Info, TriangleAlert, XCircle, Loader2 } from 'lucide-vue-next'

export type NotificationType = 'success' | 'error' | 'warning' | 'info' | 'loading'

const props = withDefaults(defineProps<{
  id?: number
  type: NotificationType
  title: string
  message?: string
  showIcon?: boolean
  duration?: number // ms; if undefined, stays until closed
}>(), {
  showIcon: true,
})

const emit = defineEmits<{ (e: 'close'): void }>()

const timer = ref<ReturnType<typeof setTimeout> | null>(null)
const isLoading = computed(() => props.type === 'loading')
const hasDuration = computed(() => !!props.duration && !isLoading.value)

onMounted(() => {
  if (props.duration && !isLoading.value) {
    timer.value = setTimeout(() => emit('close'), props.duration)
  }
})

onBeforeUnmount(() => {
  if (timer.value) clearTimeout(timer.value)
})

const typeClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-900 dark:text-emerald-100'
    case 'error':
      return 'border-rose-500/30 bg-rose-500/10 text-rose-900 dark:text-rose-100'
    case 'warning':
      return 'border-amber-500/30 bg-amber-500/10 text-amber-900 dark:text-amber-100'
    case 'info':
      return 'border-sky-500/30 bg-sky-500/10 text-sky-900 dark:text-sky-100'
    case 'loading':
      return 'border-zinc-500/30 bg-zinc-500/10 text-zinc-900 dark:text-zinc-100'
    default:
      return 'border-foreground/20 bg-background'
  }
})

function Icon() {
  if (!props.showIcon) return null
  switch (props.type) {
    case 'success': return CheckCircle2
    case 'error': return XCircle
    case 'warning': return TriangleAlert
    case 'info': return Info
    case 'loading': return Loader2
  }
}

// Progress bar color based on type
const progressBarClass = computed(() => {
  switch (props.type) {
    case 'success':
      return 'bg-emerald-500/80'
    case 'error':
      return 'bg-rose-500/80'
    case 'warning':
      return 'bg-amber-500/80'
    case 'info':
      return 'bg-sky-500/80'
    default:
      return 'bg-foreground/50'
  }
})
</script>

<template>
  <div
    v-motion
    :initial="{ opacity: 0, y: 10, scale: 0.98 }"
    :enter="{ opacity: 1, y: 0, scale: 1 }"
    :leave="{ opacity: 0, y: -6, scale: 0.98 }"
    class="relative w-full overflow-hidden rounded-lg border p-4 shadow-lg backdrop-blur-[2px]"
    :class="typeClasses"
  >
    <!-- Progress bar (duration countdown) -->
    <div v-if="hasDuration" class="absolute bottom-0 left-0 h-0.5 w-full bg-transparent">
      <div
        class="h-full origin-left"
        :class="progressBarClass"
        :style="{ animation: 'toast-countdown linear forwards', animationDuration: `${props.duration}ms` }"
      />
    </div>

    <div class="flex items-start gap-3">
      <component :is="Icon()" v-if="Icon()" class="h-5 w-5 shrink-0" :class="{ 'animate-spin': isLoading }" />
      <div class="min-w-0">
        <div class="font-medium leading-tight">{{ title }}</div>
        <div v-if="message" class="mt-0.5 text-sm opacity-80 leading-snug break-words">
          {{ message }}
        </div>
      </div>
      <button
        type="button"
        @click="emit('close')"
        class="ml-auto rounded-md p-1 text-foreground/70 hover:bg-foreground/10 hover:text-foreground focus-visible:outline-none focus-visible:ring-2"
        aria-label="Close"
      >
        <XCircle class="h-4 w-4" />
      </button>
    </div>
  </div>
  
</template>

<style scoped>
@keyframes toast-countdown {
  from { transform: scaleX(0); }
  to { transform: scaleX(1); }
}
</style>
