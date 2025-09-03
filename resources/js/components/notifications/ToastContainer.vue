<script setup lang="ts">
import Notification from './Notification.vue'

export type NotificationType = 'success' | 'error' | 'warning' | 'info' | 'loading'

export type NotificationItem = {
  id: number
  type: NotificationType
  title: string
  message?: string
  showIcon?: boolean
  duration?: number
}

export type NotificationPosition =
  | 'top-left'
  | 'top-right'
  | 'top-center'
  | 'bottom-left'
  | 'bottom-right'
  | 'bottom-center'

const props = withDefaults(defineProps<{
  items: NotificationItem[]
  position?: NotificationPosition
}>(), {
  position: 'bottom-right',
})

const emit = defineEmits<{ (e: 'close', id: number): void }>()

function positionClasses(p: NotificationPosition): string {
  switch (p) {
    case 'top-left': return 'top-4 left-4'
    case 'top-right': return 'top-4 right-4'
    case 'bottom-left': return 'bottom-4 left-4'
    case 'bottom-right': return 'bottom-4 right-4'
    case 'top-center': return 'top-4 left-1/2 -translate-x-1/2'
    case 'bottom-center': return 'bottom-4 left-1/2 -translate-x-1/2'
    default: return 'bottom-4 right-4'
  }
}
</script>

<template>
  <div class="pointer-events-none fixed z-50 w-full max-w-sm p-4" :class="positionClasses(props.position)">
    <div class="pointer-events-auto flex flex-col gap-2">
      <Notification
        v-for="n in items"
        :key="n.id"
        v-bind="n"
        @close="emit('close', n.id)"
      />
    </div>
  </div>
</template>

