<script setup lang="ts">
import { computed } from 'vue'

type Props = {
  gridSize?: number
  gridColor?: string
  rayCount?: number
  rayOpacity?: number
  raySpeed?: number
  rayLength?: string
  gridFadeStart?: number // percentage 0-100
  gridFadeEnd?: number   // percentage 0-100
  backgroundColor?: string
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  gridSize: 40,
  gridColor: 'rgba(200, 220, 255, 0.2)',
  rayCount: 15,
  rayOpacity: 0.35,
  raySpeed: 1,
  rayLength: '45vh',
  gridFadeStart: 30,
  gridFadeEnd: 90,
  backgroundColor: '#020412',
})

function prng(i: number): number {
  let seed = (i + 1) * 9301 + 49297
  seed = seed % 233280
  return seed / 233280
}

type Ray = {
  top: number
  left: number
  angle: number
  duration: number
  delay: number
  opacity: number
}

const rays = computed<Ray[]>(() => {
  const list: Ray[] = []
  for (let i = 0; i < props.rayCount; i++) {
    const r1 = prng(i * 3 + 1)
    const r2 = prng(i * 3 + 2)
    const r3 = prng(i * 3 + 3)
    const angle = Math.floor(360 * r1)
    const top = Math.floor(100 * r2)
    const left = Math.floor(100 * r3)
    const base = 8 + (r1 * 10) // 8-18s base
    const duration = base / Math.max(0.1, props.raySpeed)
    const delay = - (r2 * duration) // negative delay to desync starts
    const opacity = Math.max(0.05, Math.min(1, props.rayOpacity * (0.7 + r3 * 0.6)))
    list.push({ angle, top, left, duration, delay, opacity })
  }
  return list
})

const gridStyle = computed(() => {
  const size = `${props.gridSize}px`
  const color = props.gridColor
  const fadeStart = Math.max(0, Math.min(100, props.gridFadeStart))
  const fadeEnd = Math.max(0, Math.min(100, props.gridFadeEnd))
  return {
    backgroundImage: `
      repeating-linear-gradient(0deg, ${color}, ${color} 1px, transparent 1px, transparent ${size}),
      repeating-linear-gradient(90deg, ${color}, ${color} 1px, transparent 1px, transparent ${size})
    `,
    WebkitMaskImage: `radial-gradient(ellipse at center, black ${fadeStart}%, transparent ${fadeEnd}%)`,
    maskImage: `radial-gradient(ellipse at center, black ${fadeStart}%, transparent ${fadeEnd}%)`,
  } as Record<string, string>
})

const containerStyle = computed(() => ({
  backgroundColor: props.backgroundColor,
}))
</script>

<template>
  <div class="relative isolate overflow-hidden rounded-xl" :class="props.class" :style="containerStyle">
    <!-- Grid overlay -->
    <div class="pointer-events-none absolute inset-0" :style="gridStyle" />

    <!-- Rays -->
    <div class="pointer-events-none absolute inset-0">
      <div
        v-for="(ray, i) in rays"
        :key="i"
        class="absolute will-change-transform"
        :style="{
          top: ray.top + '%',
          left: ray.left + '%',
          '--angle': `rotate(${ray.angle}deg)`,
          opacity: String(ray.opacity),
          animation: `grid-beam-move ${ray.duration}s linear ${ray.delay}s infinite`,
          width: props.rayLength,
        }"
      >
        <div class="h-px w-full"
             :style="{
               background: `linear-gradient(90deg, transparent 0%, rgba(255,255,255,0) 10%, rgba(255,255,255,${Math.min(0.85, ray.opacity)}) 50%, rgba(255,255,255,0) 90%, transparent 100%)`,
               filter: 'blur(1.2px) drop-shadow(0 0 10px rgba(140, 200, 255, 0.35))',
               mixBlendMode: 'screen',
             }"
        />
      </div>
    </div>

    <!-- Content slot -->
    <div class="relative z-10">
      <slot />
    </div>
  </div>
</template>

<style scoped>
@keyframes grid-beam-move {
  0% { transform: var(--angle, rotate(0deg)) translateX(-20%); }
  100% { transform: var(--angle, rotate(0deg)) translateX(120%); }
}
</style>
