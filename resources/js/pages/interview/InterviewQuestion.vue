<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, onBeforeUnmount } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Icon from '@/components/Icon.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Skeleton } from '@/components/ui/skeleton'
import { CheckCircle2, XCircle, GaugeCircle, BookOpenText, Sparkles, ListChecks, Lightbulb, Workflow, Scale } from 'lucide-vue-next'
import { notifyError, notifySuccess } from '@/lib/notify'

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
const isRecording = ref(false)
let recognition: any | null = null

// Evaluation state
const isSubmitting = ref(false)
const evaluationId = ref<number | null>(null)
const evaluation = ref<null | {
  overall_performance: { justification: string | null; score: number | null }
  structural_integrity: { justification: string | null; score: number | null }
  content_accuracy: { justification: string | null; score: number | null }
  fluency_of_expression: { justification: string | null; score: number | null }
  strengths: string[] | null
  priority_areas_for_improvement: string[] | null
  comparative_analysis: string[] | null
  encouraging_advice: string[] | null
}>(null)

const placeholder = computed(() =>
  mode.value === 'speaking'
    ? 'Start speaking to see the transcript here'
    : 'Start typing your answer here',
)

const canSubmit = computed(() => {
  return /\b\w+\b/.test(answer.value.trim())
})

async function startRecording(): Promise<void> {
  try {
    if (navigator?.mediaDevices?.getUserMedia) {
      await navigator.mediaDevices.getUserMedia({ audio: true })
    }

    const SpeechRecognition =
      (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition

    if (!SpeechRecognition) {
      alert('Speech recognition is not supported in this browser.')
      return
    }

    recognition = new SpeechRecognition()
    recognition.lang = 'en-US'
    recognition.interimResults = true
    recognition.continuous = true

    let finalTranscript = ''

    recognition.onresult = (event: any) => {
      let interimTranscript = ''
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const transcript = event.results[i][0].transcript
        if (event.results[i].isFinal) {
          finalTranscript += transcript + ' '
        } else {
          interimTranscript += transcript
        }
      }
      answer.value = (finalTranscript + interimTranscript).trim()
    }

    recognition.onerror = () => {
      isRecording.value = false
    }

    recognition.onend = () => {
      isRecording.value = false
    }

    recognition.start()
    isRecording.value = true
  } catch (e) {
    // Permission denied or other error
    console.error(e)
    alert('Unable to access microphone. Please check permissions.')
    isRecording.value = false
  }
}

function stopRecording(): void {
  if (recognition) {
    try {
      recognition.stop()
    } catch (_) {
      // no-op if already stopped
    }
  }
  isRecording.value = false
}

onBeforeUnmount(() => {
  if (recognition) {
    try {
      recognition.stop()
    } catch (_) {
      // ignore
    }
  }
})

function getCookie(name: string) {
  const match = document.cookie
    .split('; ')
    .find((row) => row.startsWith(name + '='))
  return match ? decodeURIComponent(match.split('=')[1]) : null
}

async function submit() {
  if (!canSubmit.value) return
  isSubmitting.value = true
  evaluation.value = null
  try {
    const token = getCookie('XSRF-TOKEN')
    const res = await fetch(route('interview-question-bank.answer.store', { question: props.question.id }), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ answer: answer.value }),
    })
    if (!res.ok) {
      let message = 'Failed to submit answer.'
      try {
        const data = await res.json()
        const bag = (data?.errors || data)
        if (bag && typeof bag === 'object') {
          const combined = Object.values(bag).flat().join('\n')
          if (combined) message = combined as string
        }
      } catch (_) {}
      throw new Error(message)
    }
    const data = await res.json()
    evaluationId.value = data?.id ?? null
    notifySuccess('Evaluation complete. Opening results…', 'Completed')
    if (evaluationId.value) {
      router.visit(route('interview-answer-evaluations.show', { evaluation: evaluationId.value }))
      return
    }
    // Fallback: keep inline render if id missing
    evaluation.value = data?.data ?? null
  } catch (e: any) {
    notifyError(e?.message || 'Submission failed.', 'Error')
  } finally {
    isSubmitting.value = false
  }
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
            :disabled="isRecording"
            class="w-full min-h-40 rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-transparent p-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
          />
        </div>
        <div class="mt-4">
          <div v-if="mode === 'speaking'" class="flex items-center justify-between">
            <div class="text-xs text-muted-foreground">
              {{ isRecording ? 'Recording… speak now. Click Finish when done.' : 'Click Start Speaking and allow mic access.' }}
            </div>
            <div class="flex items-center gap-2">
              <Button
                v-if="!isRecording"
                variant="default"
                @click="startRecording"
              >
                <Icon name="mic" /> Start Speaking
              </Button>
              <Button
                v-else
                variant="destructive"
                @click="stopRecording"
              >
                <Icon name="square" /> Finish
              </Button>
              <Button variant="default" :disabled="!canSubmit || isSubmitting" @click="submit">
                <span v-if="!isSubmitting">Submit Answer</span>
                <span v-else class="flex items-center gap-2"><Icon name="loader-2" class="animate-spin" /> Submitting…</span>
              </Button>
            </div>
          </div>
          <div v-else class="flex justify-end">
            <Button variant="default" :disabled="!canSubmit || isSubmitting" @click="submit">
              <span v-if="!isSubmitting">Submit Answer</span>
              <span v-else class="flex items-center gap-2"><Icon name="loader-2" class="animate-spin" /> Submitting…</span>
            </Button>
          </div>
        </div>
      </div>

      <!-- Evaluation Result -->
      <div v-if="isSubmitting" class="grid gap-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Skeleton class="h-24 rounded-xl" />
          <Skeleton class="h-24 rounded-xl" />
          <Skeleton class="h-24 rounded-xl" />
          <Skeleton class="h-24 rounded-xl" />
        </div>
        <Skeleton class="h-10 rounded-xl" />
        <div class="grid md:grid-cols-2 gap-4">
          <Skeleton class="h-40 rounded-xl" />
          <Skeleton class="h-40 rounded-xl" />
        </div>
      </div>

      <div v-if="evaluation" class="flex flex-col gap-4 animate-in fade-in slide-in-from-bottom-2">
        <div class="rounded-xl p-5 text-white bg-linear-to-r from-indigo-600 to-fuchsia-600 shadow-sm ring-1 ring-black/5">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-lg font-semibold">Evaluation Results</div>
              <div class="text-xs opacity-90">Automated assessment tailored to your answer</div>
            </div>
            <Badge variant="outline" class="bg-white/10 border-white/20 text-white/90">#{{ evaluationId || '—' }}</Badge>
          </div>
        </div>
        <!-- Scores row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div
            class="rounded-xl border p-4 bg-gradient-to-r ring-1 ring-black/5 transition"
            :class="scoreColor(evaluation.overall_performance.score)"
          >
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
        <Card class="bg-card-gradient border-sidebar-border/70">
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
          <Card class="bg-card-gradient">
            <CardHeader>
              <CardTitle class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                <CheckCircle2 class="h-5 w-5" /> Strengths
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ul class="grid gap-2 text-sm">
                <li v-for="(s, i) in (evaluation.strengths || [])" :key="'s'+i" class="flex items-start gap-2">
                  <CheckCircle2 class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                  <span>{{ s }}</span>
                </li>
              </ul>
              <div v-if="!evaluation.strengths?.length" class="text-muted-foreground text-sm">No strengths provided.</div>
            </CardContent>
          </Card>

          <Card class="bg-card-gradient">
            <CardHeader>
              <CardTitle class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                <ListChecks class="h-5 w-5" /> Priority Improvements
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ul class="grid gap-2 text-sm">
                <li v-for="(s, i) in (evaluation.priority_areas_for_improvement || [])" :key="'i'+i" class="flex items-start gap-2">
                  <ListChecks class="mt-0.5 h-4 w-4 text-amber-600 dark:text-amber-400" />
                  <span>{{ s }}</span>
                </li>
              </ul>
              <div v-if="!evaluation.priority_areas_for_improvement?.length" class="text-muted-foreground text-sm">No improvements provided.</div>
            </CardContent>
          </Card>

          <Card class="bg-card-gradient">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Scale class="h-5 w-5" /> Comparative Analysis
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ul class="grid gap-2 text-sm">
                <li v-for="(s, i) in (evaluation.comparative_analysis || [])" :key="'c'+i" class="flex items-start gap-2">
                  <template v-if="parseComparativeItem(s).type === 'good'">
                    <CheckCircle2 class="mt-0.5 h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                    <span>{{ parseComparativeItem(s).text }}</span>
                  </template>
                  <template v-else-if="parseComparativeItem(s).type === 'bad'">
                    <XCircle class="mt-0.5 h-4 w-4 text-rose-600 dark:text-rose-400" />
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

        <Card class="bg-card-gradient">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-sky-700 dark:text-sky-400">
              <Lightbulb class="h-5 w-5" /> Encouraging Advice
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="grid gap-2 text-sm">
              <li v-for="(s, i) in (evaluation.encouraging_advice || [])" :key="'a'+i" class="flex items-start gap-2">
                <Sparkles class="mt-0.5 h-4 w-4 text-sky-700 dark:text-sky-400" />
                <span>{{ s }}</span>
              </li>
            </ul>
            <div v-if="!evaluation.encouraging_advice?.length" class="text-muted-foreground text-sm">No advice provided.</div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>

</template>
