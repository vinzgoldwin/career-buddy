<script setup lang="ts">
import { useToastManager } from '@/composables/useToastManager'
import ToastContainer from '@/components/notifications/ToastContainer.vue'

const {
  notifications,
  position,
  addNotification,
  addLoadingWithSuccess,
  close,
  setPosition,
} = useToastManager()
</script>

<template>
  <div class="relative min-h-screen p-4">
    <ToastContainer :items="notifications" :position="position" @close="close" />

    <div class="mt-20 rounded-xl border border-sidebar-border/60 bg-background/80 p-6 shadow-sm backdrop-blur-md dark:border-sidebar-border/60">
      <h3 class="mb-4 text-center text-lg font-semibold">Test Notifications</h3>
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
        <button @click="addNotification('success','Success!','Operation completed successfully.', true, 3000)" class="rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">Success (3s)</button>
        <button @click="addNotification('info','Info Message','Here is some general information.', true, 4000)" class="rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700">Info (4s)</button>
        <button @click="addNotification('warning','Warning!','This is a warning without an icon.', false, 5000)" class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700">Warning (5s)</button>
        <button @click="addNotification('error','Error!','An error occurred.', true, 6000)" class="rounded-lg bg-rose-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:bg-rose-600 dark:bg-rose-600 dark:hover:bg-rose-700">Error (6s)</button>
        <button @click="addNotification('loading','Loading...','Please wait a moment.', true)" class="rounded-lg bg-zinc-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:bg-zinc-600 dark:bg-zinc-600 dark:hover:bg-zinc-700">Loading</button>
        <button @click="addLoadingWithSuccess('Processing Payment','Please wait while we process your payment...','Payment Successful','Your payment of $29.99 has been processed successfully!')" class="rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 px-3 py-2 text-sm font-medium text-white transition hover:scale-105 hover:from-blue-600 hover:to-purple-600 dark:from-blue-600 dark:to-purple-600 dark:hover:from-blue-700 dark:hover:to-purple-700">Payment Processing</button>
      </div>
    </div>

    <div class="absolute bottom-4 left-1/2 z-40 -translate-x-1/2 rounded-xl border border-sidebar-border/60 bg-background/80 p-3 shadow-sm backdrop-blur-md dark:border-sidebar-border/60">
      <div class="flex flex-wrap justify-center gap-1">
        <button @click="setPosition('top-left')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='top-left' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Top Left</button>
        <button @click="setPosition('top-center')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='top-center' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Top Center</button>
        <button @click="setPosition('top-right')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='top-right' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Top Right</button>
        <button @click="setPosition('bottom-left')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='bottom-left' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Bottom Left</button>
        <button @click="setPosition('bottom-center')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='bottom-center' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Bottom Center</button>
        <button @click="setPosition('bottom-right')" class="rounded-lg px-3 py-1.5 text-xs font-medium" :class="position==='bottom-right' ? 'bg-blue-500 text-white' : 'bg-muted text-foreground hover:bg-accent'">Bottom Right</button>
      </div>
    </div>
  </div>
</template>

