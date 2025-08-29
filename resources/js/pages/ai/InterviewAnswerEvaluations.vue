<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
              <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <GaugeCircle class="h-4 w-4" />
                Avg score: <span class="font-medium text-foreground">{{ e.average_score ?? '—' }}</span>
              </div>
              <Button variant="outline" size="sm">
                View <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

