<script setup lang="ts">
import { ref, computed } from 'vue'
import { Moon, Sun } from 'lucide-vue-next'
import { useAppearance } from '@/composables/useAppearance'

const { appearance, updateAppearance } = useAppearance()

const isDarkMode = computed(() => appearance.value === 'dark')
const buttonRef = ref<HTMLButtonElement | null>(null)

async function changeTheme(): Promise<void> {
  const el = buttonRef.value
  if (!el) return

  const next = isDarkMode.value ? 'light' : 'dark'

  const supportsVT = typeof document !== 'undefined' && (document as any).startViewTransition

  if (supportsVT) {
    try {
      // Start view transition and wait until ready
      await (document as any).startViewTransition(() => {
        // Toggle theme immediately on the document element via our composable
        updateAppearance(next as any)
      }).ready

      // Compute reveal animation geometry from the button center
      const rect = el.getBoundingClientRect()
      const x = rect.left + rect.width / 2
      const y = rect.top + rect.height / 2

      const left = rect.left
      const top = rect.top
      const right = window.innerWidth - rect.left
      const bottom = window.innerHeight - rect.top
      const maxRad = Math.hypot(Math.max(left, right), Math.max(top, bottom))

      document.documentElement.animate(
        {
          clipPath: [
            `circle(0px at ${x}px ${y}px)`,
            `circle(${maxRad}px at ${x}px ${y}px)`,
          ],
        },
        {
          duration: 700,
          easing: 'ease-in-out',
          pseudoElement: '::view-transition-new(root)',
        },
      )
    } catch {
      // Fallback: just toggle without animation
      updateAppearance(next as any)
    }
  } else {
    // No view transition support
    updateAppearance(next as any)
  }
}
</script>

<template>
  <button
    ref="buttonRef"
    type="button"
    @click="changeTheme"
    :aria-label="`Switch to ${isDarkMode ? 'light' : 'dark'} mode`"
    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-sidebar-border/70 bg-background/60 shadow-xs backdrop-blur-[2px] transition hover:bg-accent/60 dark:border-sidebar-border"
  >
    <Sun v-if="isDarkMode" class="h-5 w-5" />
    <Moon v-else class="h-5 w-5" />
  </button>
</template>
