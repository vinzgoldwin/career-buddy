<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { X } from 'lucide-vue-next'
import { onMounted, onUnmounted } from 'vue'

interface Props {
  open: boolean
  title?: string
  description?: string
}

interface Emits {
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  title: '',
  description: ''
})

const emit = defineEmits<Emits>()

// Handle escape key to close dialog
const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.open) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  // Prevent background scrolling when dialog is open
  if (props.open) {
    document.body.style.overflow = 'hidden'
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})

// Close dialog when clicking on the backdrop
const handleBackdropClick = (e: MouseEvent) => {
  if (e.target === e.currentTarget) {
    emit('close')
  }
}
</script>

<template>
  <Teleport to="body">
    <div 
      v-if="open" 
      class="fixed inset-0 z-50 flex items-center justify-center"
      @click="handleBackdropClick"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
      
      <!-- Dialog Content -->
      <div class="fixed inset-0 flex flex-col bg-background border-l border-border md:m-4 md:rounded-xl md:overflow-hidden shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b p-4 md:p-6">
          <div>
            <h2 class="text-xl font-bold">{{ title }}</h2>
            <p v-if="description" class="text-sm text-muted-foreground mt-1">{{ description }}</p>
          </div>
          <Button variant="ghost" size="icon" @click="$emit('close')">
            <X class="h-5 w-5" />
          </Button>
        </div>
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
          <slot />
        </div>
        
        <!-- Footer -->
        <div class="border-t p-4 md:p-6 flex justify-end gap-3">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>
