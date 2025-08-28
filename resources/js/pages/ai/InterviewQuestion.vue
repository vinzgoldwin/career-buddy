<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Icon from '@/components/Icon.vue'

const props = defineProps<{
  question: {
    id: number
    title: string
    category: string
    difficulty: string
    views_count: number
    users_practiced_count: number
    time_ago: string
    explanation: string | null
  }
}>()

const mode = ref<'speaking' | 'text'>('speaking')
const answer = ref('')

const placeholder = computed(() =>
  mode.value === 'speaking'
    ? 'Start speaking to see the transcript here'
    : 'Start typing your answer here',
)

function submit() {
  // Placeholder: submit behavior can be implemented later
  // For now, just a simple console log
  console.log('Submitted', { mode: mode.value, answer: answer.value })
}
</script>

<template>
  <Head :title="props.question.title" />
  <AppLayout :breadcrumbs="[{ title: 'Interview Question Bank', href: '/interview-question-bank' }, { title: 'Question', href: `/interview-question-bank/${props.question.id}` }]">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <!-- Header -->
      <div class="rounded-xl p-6 text-white bg-linear-to-r from-violet-600 to-fuchsia-600 shadow-sm">
        <div class="flex flex-col gap-3">
          <div class="flex items-start justify-between gap-3">
            <h1 class="text-xl font-semibold md:text-2xl leading-snug">{{ question.title }}</h1>
            <Badge variant="outline" class="text-xs bg-white/10 border-white/20">{{ question.time_ago }}</Badge>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <Badge class="bg-primary/10 text-white border-white/20">{{ question.category }}</Badge>
            <Badge class="bg-amber-500/10 text-amber-100 border-amber-400/30">{{ question.difficulty }}</Badge>
            <div class="ml-2 flex items-center gap-1 text-white/90 text-sm">
              <Icon name="eye" />
              <span>{{ question.views_count.toLocaleString() }} views</span>
            </div>
            <div class="ml-2 flex items-center gap-1 text-white/90 text-sm">
              <Icon name="users" />
              <span>{{ question.users_practiced_count.toLocaleString() }} practiced</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Explanation -->
      <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-background p-5 shadow-xs">
        <h2 class="text-sm font-semibold mb-3">About this question</h2>
        <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed whitespace-pre-line">
          {{ question.explanation ?? 'No explanation provided yet.' }}
        </div>
      </div>

      <!-- Simulation -->
      <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-background p-5 shadow-xs">
        <div class="flex items-center justify-between gap-3 mb-3">
          <h2 class="text-sm font-semibold">Start Trying This Question Simulation</h2>
          <div class="flex items-center gap-2">
            <Button :variant="mode === 'speaking' ? 'gradient' : 'outline'" size="sm" @click="mode = 'speaking'">
              <Icon name="mic" /> Start Speaking
            </Button>
            <Button :variant="mode === 'text' ? 'gradient' : 'outline'" size="sm" @click="mode = 'text'">
              <Icon name="type" /> Text Answer
            </Button>
          </div>
        </div>
        <div>
          <textarea
            v-model="answer"
            :placeholder="placeholder"
            class="w-full min-h-40 rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-transparent p-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
          />
        </div>
        <div class="mt-4 flex justify-end">
          <Button variant="default" @click="submit">Submit Answer</Button>
        </div>
      </div>
    </div>
  </AppLayout>
  
</template>

