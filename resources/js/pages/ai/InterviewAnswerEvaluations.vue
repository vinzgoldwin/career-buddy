<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { FileText, ArrowRight, GaugeCircle } from 'lucide-vue-next'

const props = defineProps<{
  evaluations: {
    data: Array<{
      id: number
      question: { id: number; title: string; category: string } | null
      overall_performance_score: number | null
      average_score: number | null
      created_at: string
    }>
    links: any
  }
}>()

function openEvaluation(id: number) {
  router.visit(route('interview-answer-evaluations.show', { evaluation: id }))
}

function avgBadgeClass(score: number | null): string {
  if (score == null) return 'bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border-zinc-500/20'
  if (score >= 8) return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20'
  if (score >= 6) return 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20'
  return 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20'
}
</script>

<template>
  <Head title="Interview Evaluations" />
  <AppLayout :breadcrumbs="[{ title: 'Interview Evaluations', href: route('interview-answer-evaluations.index') }]">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <h1 class="text-2xl font-bold tracking-tight">Interview Evaluations</h1>
          <p class="text-muted-foreground">Review your past AI interview answer evaluations.</p>
        </div>
        <div class="flex gap-2">
          <Button class="bg-primary-gradient text-white hover:opacity-90" @click="router.visit(route('interview-question-bank'))">
            <FileText class="mr-2 h-4 w-4" /> Practice a Question
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <Card v-for="e in evaluations.data" :key="e.id" class="bg-card-gradient hover:bg-accent/30 transition cursor-pointer" @click="openEvaluation(e.id)">
          <CardHeader>
            <CardTitle class="flex items-center justify-between gap-3">
              <span class="line-clamp-1">{{ e.question?.title || 'Untitled question' }}</span>
              <span class="text-sm text-muted-foreground">#{{ e.id }}</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center justify-between">
              <Badge :class="avgBadgeClass(e.average_score)" class="inline-flex items-center gap-1">
                <GaugeCircle class="h-3.5 w-3.5" />
                <span>Avg {{ e.average_score !== null ? (e.average_score).toFixed(1) : '—' }}/10</span>
              </Badge>
              <Button variant="outline" size="sm">
                View <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Pagination -->
      <div v-if="evaluations.links?.length" class="mt-2 flex items-center justify-center gap-2">
        <template v-for="link in evaluations.links" :key="link.label">
          <span
            v-if="!link.url"
            class="px-3 py-1.5 text-sm text-muted-foreground/60"
            v-html="link.label"
          />
          <Link
            v-else
            :href="link.url"
            preserve-scroll
            preserve-state
            class="px-3 py-1.5 rounded-md border text-sm"
            :class="[
              link.active
                ? 'bg-primary text-primary-foreground border-primary'
                : 'bg-background hover:bg-accent border-sidebar-border/70 dark:border-sidebar-border',
            ]"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
