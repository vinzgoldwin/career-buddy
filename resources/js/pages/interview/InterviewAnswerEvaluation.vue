<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { computed, ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { CheckCircle2, XCircle, GaugeCircle, BookOpenText, Sparkles, ListChecks, Lightbulb, Workflow, Scale, ChevronLeft, FileText, Download } from 'lucide-vue-next'

const props = defineProps<{
  evaluation: {
    id: number
    answer: string | null
    question: { id: number; title: string; category: string; explanation: string | null } | null
    overall_performance: { justification: string | null; score: number | null }
    structural_integrity: { justification: string | null; score: number | null }
    content_accuracy: { justification: string | null; score: number | null }
    fluency_of_expression: { justification: string | null; score: number | null }
    strengths: string[] | null
    priority_areas_for_improvement: string[] | null
    comparative_analysis: string[] | null
    encouraging_advice: string[] | null
  }
}>()

const avgScore = computed<number | null>(() => {
  const vals = [
    props.evaluation.overall_performance?.score ?? null,
    props.evaluation.structural_integrity?.score ?? null,
    props.evaluation.content_accuracy?.score ?? null,
    props.evaluation.fluency_of_expression?.score ?? null,
  ].filter((v) => typeof v === 'number') as number[]
  if (!vals.length) return null
  const sum = vals.reduce((a, b) => a + b, 0)
  return Math.round((sum / vals.length) * 10) / 10
})

function avgBadgeClass(score: number | null): string {
  if (score == null) return 'bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border-zinc-500/20'
  if (score >= 8) return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20'
  if (score >= 6) return 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20'
  return 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20'
}

function scoreColor(score: number | null): string {
  if (score == null) return 'from-zinc-500/20 to-transparent border-zinc-500/30'
  if (score >= 8) return 'from-emerald-500/20 to-transparent border-emerald-500/30'
  if (score >= 6) return 'from-amber-500/20 to-transparent border-amber-500/30'
  return 'from-rose-500/20 to-transparent border-rose-500/30'
}

function parseComparativeItem(item: string): { type: 'good' | 'bad' | null; text: string } {
  const t = (item || '').trim()
  if (t.startsWith('✅')) {
    return { type: 'good', text: t.replace(/^✅\s*/, '') }
  }
  if (t.startsWith('❌')) {
    return { type: 'bad', text: t.replace(/^❌\s*/, '') }
  }
  return { type: null, text: t }
}

function saveAsPdf() {
  try {
    const url = route('interview-answer-evaluations.download', props.evaluation.id)
    if (typeof window !== 'undefined' && window?.open) {
      window.open(url, '_blank')
    } else {
      router.visit(url)
    }
  } catch (_) {
    router.visit(route('interview-answer-evaluations.download', props.evaluation.id))
  }
}

// Collapsible state for long answers
const isAnswerExpanded = ref(false)
const showAnswerCollapse = ref(false)
const answerEl = ref<HTMLElement | null>(null)
const COLLAPSE_MAX_HEIGHT = 200 // px (~12.5rem)

function measureAnswerOverflow() {
  nextTick(() => {
    const el = answerEl.value
    const text = props.evaluation.answer || ''
    if (!el || !text.trim().length) {
      showAnswerCollapse.value = false
      return
    }
    const scroll = el.scrollHeight || 0
    // Fallback on content length in case layout produces low scrollHeight
    const longByChars = text.length > 400
    showAnswerCollapse.value = scroll > COLLAPSE_MAX_HEIGHT || longByChars
  })
}

onMounted(() => {
  measureAnswerOverflow()
  window.addEventListener('resize', measureAnswerOverflow)
})

onUnmounted(() => {
  window.removeEventListener('resize', measureAnswerOverflow)
})

watch(() => props.evaluation.answer, () => {
  isAnswerExpanded.value = false
  measureAnswerOverflow()
})
</script>

<template>
  <Head :title="props.evaluation.question?.title ? 'Evaluation – ' + props.evaluation.question.title : 'Evaluation'" />
  <AppLayout :breadcrumbs="[
      { title: 'Interview Evaluations', href: route('interview-answer-evaluations.index') },
      { title: 'Evaluation', href: '' },
    ]">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6 print-safe">
      <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <h1 class="text-2xl font-bold tracking-tight">Interview Evaluation</h1>
          <p class="text-muted-foreground">Automated assessment tailored to your answer</p>
        </div>
        <div class="flex items-center gap-5">
          <Badge class="bg-violet-500/10 text-violet-700 dark:text-violet-300 border-violet-500/20">#{{ evaluation.id }}</Badge>
          <Badge v-if="avgScore !== null" :class="avgBadgeClass(avgScore)" class="inline-flex items-center gap-1">
            <GaugeCircle class="h-3.5 w-3.5" />
            <span>Avg {{ avgScore?.toFixed(1) }}/10</span>
          </Badge>
          <Button variant="ghost" class="flex items-center gap-2 w-fit bg-emerald-600 text-white hover:opacity-90" @click="saveAsPdf">
            <Download class="h-5 w-5" />
            Save as PDF
          </Button>
          <Button variant="outline" @click="router.visit(route('interview-answer-evaluations.index'))">
            <ChevronLeft class="mr-2 h-4 w-4" /> Back to list
          </Button>
        </div>
      </div>
      <Card class="bg-card-gradient avoid-break">
        <CardContent class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2 text-primary">
              <FileText class="h-5 w-5" />
              <div class="text-sm text-muted-foreground">Question</div>
            </div>
            <Badge v-if="evaluation.question?.category" class="bg-primary/10 text-primary border-primary/20">{{ evaluation.question?.category }}</Badge>
          </div>
          <div class="mt-2 text-xl md:text-2xl font-semibold leading-snug">
            {{ evaluation.question?.title }}
          </div>
        </CardContent>
      </Card>

        <!-- User Answer -->
        <Card class="bg-card-gradient avoid-break">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <BookOpenText class="h-5 w-5" /> Your Answer
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="evaluation.answer && evaluation.answer.trim().length"
                    ref="answerEl"
                    class="relative whitespace-pre-line text-sm leading-relaxed text-foreground/90"
                    :style="!isAnswerExpanded ? { maxHeight: COLLAPSE_MAX_HEIGHT + 'px', overflow: 'hidden' } : {}"
                >
                    {{ evaluation.answer }}
                    <div v-if="showAnswerCollapse && !isAnswerExpanded" class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-background to-transparent"></div>
                </div>
                <div v-else class="text-sm text-muted-foreground">No answer provided.</div>

                <div v-if="showAnswerCollapse" class="mt-3">
                    <Button size="sm" variant="ghost" class="px-2 h-7 text-primary hover:bg-primary/10" @click="isAnswerExpanded = !isAnswerExpanded">
                        {{ isAnswerExpanded ? 'Show less' : 'Show more' }}
                    </Button>
                </div>
            </CardContent>
        </Card>

      <!-- Scores row -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl border p-4 bg-gradient-to-r ring-1 ring-black/5 transition" :class="scoreColor(evaluation.overall_performance.score)">
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium">Overall Performance</div>
            <GaugeCircle class="h-5 w-5 opacity-70" />
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <div class="text-2xl font-semibold">{{ evaluation.overall_performance.score ?? '—' }}</div>
            <div class="text-xs text-muted-foreground">/ 10</div>
          </div>
        </div>
        <div class="rounded-xl border p-4 bg-gradient-to-r ring-1 ring-black/5 transition" :class="scoreColor(evaluation.structural_integrity.score)">
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium">Structural Integrity</div>
            <Workflow class="h-5 w-5 opacity-70" />
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <div class="text-2xl font-semibold">{{ evaluation.structural_integrity.score ?? '—' }}</div>
            <div class="text-xs text-muted-foreground">/ 10</div>
          </div>
        </div>
        <div class="rounded-xl border p-4 bg-gradient-to-r ring-1 ring-black/5 transition" :class="scoreColor(evaluation.content_accuracy.score)">
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium">Content Accuracy</div>
            <BookOpenText class="h-5 w-5 opacity-70" />
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <div class="text-2xl font-semibold">{{ evaluation.content_accuracy.score ?? '—' }}</div>
            <div class="text-xs text-muted-foreground">/ 10</div>
          </div>
        </div>
        <div class="rounded-xl border p-4 bg-gradient-to-r ring-1 ring-black/5 transition" :class="scoreColor(evaluation.fluency_of_expression.score)">
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium">Fluency</div>
            <Sparkles class="h-5 w-5 opacity-70" />
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <div class="text-2xl font-semibold">{{ evaluation.fluency_of_expression.score ?? '—' }}</div>
            <div class="text-xs text-muted-foreground">/ 10</div>
          </div>
        </div>
      </div>

      <!-- Justifications -->
      <Card class="bg-card-gradient border-sidebar-border/70 avoid-break">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <GaugeCircle class="h-5 w-5" /> Detailed Justifications
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid md:grid-cols-2 gap-6 text-sm leading-relaxed">
            <div>
              <div class="font-medium">Overall Performance</div>
              <p class="text-muted-foreground mt-1 whitespace-pre-line">{{ evaluation.overall_performance.justification }}</p>
            </div>
            <div>
              <div class="font-medium">Structural Integrity</div>
              <p class="text-muted-foreground mt-1 whitespace-pre-line">{{ evaluation.structural_integrity.justification }}</p>
            </div>
            <div>
              <div class="font-medium">Content Accuracy</div>
              <p class="text-muted-foreground mt-1 whitespace-pre-line">{{ evaluation.content_accuracy.justification }}</p>
            </div>
            <div>
              <div class="font-medium">Fluency of Expression</div>
              <p class="text-muted-foreground mt-1 whitespace-pre-line">{{ evaluation.fluency_of_expression.justification }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Lists -->
      <div class="grid md:grid-cols-3 gap-4">
        <Card class="bg-card-gradient avoid-break">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="h-5 w-5" /> Strengths
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="grid gap-2 text-sm">
              <li v-for="(s, i) in (evaluation.strengths || [])" :key="'s'+i" class="flex items-start gap-2">
                <CheckCircle2 class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ s }}</span>
              </li>
            </ul>
            <div v-if="!evaluation.strengths?.length" class="text-muted-foreground text-sm">No strengths provided.</div>
          </CardContent>
        </Card>

        <Card class="bg-card-gradient avoid-break">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
              <ListChecks class="h-5 w-5" /> Priority Improvements
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="grid gap-2 text-sm">
              <li v-for="(s, i) in (evaluation.priority_areas_for_improvement || [])" :key="'i'+i" class="flex items-start gap-2">
                <ListChecks class="mt-0.5 h-4 w-4 text-amber-600 dark:text-amber-400 shrink-0" />
                <span>{{ s }}</span>
              </li>
            </ul>
            <div v-if="!evaluation.priority_areas_for_improvement?.length" class="text-muted-foreground text-sm">No improvements provided.</div>
          </CardContent>
        </Card>

        <Card class="bg-card-gradient avoid-break">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Scale class="h-5 w-5" /> Comparative Analysis
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="grid gap-2 text-sm">
              <li v-for="(s, i) in (evaluation.comparative_analysis || [])" :key="'c'+i" class="flex items-start gap-2">
                <template v-if="parseComparativeItem(s).type === 'good'">
                  <CheckCircle2 class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                  <span>{{ parseComparativeItem(s).text }}</span>
                </template>
                <template v-else-if="parseComparativeItem(s).type === 'bad'">
                  <XCircle class="mt-0.5 h-4 w-4 text-rose-600 dark:text-rose-400 shrink-0" />
                  <span>{{ parseComparativeItem(s).text }}</span>
                </template>
                <template v-else>
                  <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-primary">•</span>
                  <span>{{ parseComparativeItem(s).text }}</span>
                </template>
              </li>
            </ul>
            <div v-if="!evaluation.comparative_analysis?.length" class="text-muted-foreground text-sm">No comparison provided.</div>
          </CardContent>
        </Card>
      </div>

      <Card class="bg-card-gradient avoid-break">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-sky-700 dark:text-sky-400">
            <Lightbulb class="h-5 w-5" /> Encouraging Advice
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ul class="grid gap-2 text-sm">
            <li v-for="(s, i) in (evaluation.encouraging_advice || [])" :key="'a'+i" class="flex items-start gap-2">
              <Sparkles class="mt-0.5 h-4 w-4 text-sky-700 dark:text-sky-400 shrink-0" />
              <span>{{ s }}</span>
            </li>
          </ul>
          <div v-if="!evaluation.encouraging_advice?.length" class="text-muted-foreground text-sm">No advice provided.</div>
        </CardContent>
      </Card>


    </div>
  </AppLayout>
</template>

<style scoped>
/* Ensure print doesn’t crop content if user uses browser print */
@media print {
  .print-safe,
  .print-safe * {
    height: auto !important;
    min-height: auto !important;
    overflow: visible !important;
  }
  .avoid-break {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}
</style>
