<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import FullscreenDialog from '@/components/FullscreenDialog.vue'
import { FileText, Plus, ChevronLeft, Loader2 } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { notifyError, notifyInfo, notifySuccess } from '@/lib/notify'

const page: any = usePage()
const evaluations = computed(() => (Array.isArray(page.props.evaluations) ? page.props.evaluations : []))

// Paste Job Description dialog state
const isPasteJobDialogOpen = ref(false)
const isParsingJob = ref(false)
const jobParseForm = useForm({ raw: '' })
const isEvaluationPending = ref(false)

function getCookie(name: string) {
  const match = document.cookie
    .split('; ')
    .find((row) => row.startsWith(name + '='))
  return match ? decodeURIComponent(match.split('=')[1]) : null
}

const submitJobDescription = async () => {
  if (!jobParseForm.raw || jobParseForm.raw.trim().length < 30) {
    notifyError('Please paste a longer job description.', 'Too short')
    return
  }

  isParsingJob.value = true

  // Inform the user and show a pending card while the server works
  notifyInfo('We\'re generating your evaluation. It can take 2–3 minutes. Please wait. We\'ll refresh automatically when it\'s ready.', 'Working…')
  isEvaluationPending.value = true
  isPasteJobDialogOpen.value = false

  try {
    const token = getCookie('XSRF-TOKEN')
    const res = await fetch(route('ai-resume-builder.parse-job'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // Use CSRF cookie header format Laravel expects for SPA requests
        ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        // Ensure non-Inertia JSON response so we stay on the page
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ raw: jobParseForm.raw }),
    })

    if (!res.ok) {
      // Try to parse potential validation errors
      let msg = 'Submission failed.'
      try {
        const data = await res.json()
        const bag = (data?.errors || data)
        if (bag && typeof bag === 'object') {
          const combined = Object.values(bag).flat().join('\n')
          if (combined) msg = combined
        }
      } catch (_) {}
      throw new Error(msg)
    }

    // When finished, reload just the evaluations list and clear pending state
    notifySuccess('Evaluation complete. Loading results…', 'Completed')
    router.reload({ only: ['evaluations'] })
    isEvaluationPending.value = false
    jobParseForm.reset()
  } catch (e: any) {
    notifyError(e?.message || 'Failed to submit description.', 'Submission error')
    isEvaluationPending.value = false
  } finally {
    isParsingJob.value = false
  }
}

function openEvaluation(id: number) {
  router.visit(route('ai-evaluation.show', { evaluation: id }))
}

function clamp(text: string, max: number) {
  if (!text) return ''
  const t = String(text)
  if (t.length <= max) return t
  return t.slice(0, Math.max(0, max - 1)).trimEnd() + '…'
}
</script>

<template>
  <Head title="Profile Evaluations" />
  <AppLayout
    :breadcrumbs="[
      { title: 'AI Resume Builder', href: route('ai-resume-builder') },
      { title: 'Evaluations', href: '' },
    ]"
  >
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <h1 class="text-2xl font-bold tracking-tight">Profile Evaluations</h1>
          <p class="text-muted-foreground">Review your previous evaluations and create new ones.</p>
        </div>
        <div class="flex gap-2">
          <Button class="bg-primary-gradient text-white hover:opacity-90" @click="isPasteJobDialogOpen = true">
            <FileText class="mr-2 h-4 w-4" /> Paste Job Description
          </Button>
        </div>
      </div>

      <div v-if="evaluations.length || isEvaluationPending" class="grid grid-cols-1 gap-4">
        <!-- Pending/Evaluating card on top -->
        <div v-if="isEvaluationPending" class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 opacity-70 pointer-events-none">
          <div class="flex items-start justify-between">
            <div class="pr-4">
              <div class="flex items-center gap-2 text-lg font-semibold leading-tight">
                <Loader2 class="h-4 w-4 animate-spin" />
                Evaluation in progress
              </div>
              <div class="mt-1 text-sm text-muted-foreground">
                This can take 2–3 minutes. We’ll refresh automatically when it’s done.
              </div>
            </div>
            <div class="text-right text-xs text-muted-foreground">Just now</div>
          </div>
        </div>

        <!-- Existing evaluations -->
        <div
          v-for="e in evaluations"
          :key="e.id"
          class="cursor-pointer rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 hover:bg-accent/30 transition"
          @click="openEvaluation(e.id)"
        >
          <div class="flex items-center justify-between">
            <div class="pr-4">
              <div class="text-lg font-semibold leading-tight">{{ e.job?.title || 'Untitled role' }}</div>
              <div class="mt-1 text-sm text-muted-foreground">
                {{ e.job?.summary ? clamp(e.job.summary, 160) : '—' }}
              </div>
            </div>
            <div class="text-right">
              <div class="text-xs text-muted-foreground">
                {{ new Date(e.created_at).toLocaleString() }}
              </div>
              <div class="text-sm">Score: {{ e.total_score ?? '—' }}/100</div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center gap-3 rounded-xl border bg-card-gradient p-10 text-center ring-1 ring-black/5">
        <div class="text-lg font-medium">No evaluations yet</div>
        <p class="max-w-md text-sm text-muted-foreground">
          Paste a job description to generate your first evaluation. We’ll compare your profile against the role and highlight strengths and gaps.
        </p>
        <Button class="bg-primary-gradient text-white hover:opacity-90" @click="isPasteJobDialogOpen = true">
          <Plus class="mr-2 h-4 w-4" /> New Evaluation
        </Button>
      </div>

      <!-- Paste Job Description Dialog -->
      <FullscreenDialog
        :open="isPasteJobDialogOpen"
        title="Paste Job Description"
        description="Paste a raw job post. We will parse it into structured data and evaluate your profile."
        @close="isPasteJobDialogOpen = false"
      >
        <div class="space-y-4">
          <label class="text-sm font-medium text-foreground">Raw job description</label>
          <textarea
            v-model="jobParseForm.raw"
            rows="14"
            placeholder="Paste the full job description here..."
            class="w-full rounded-md border bg-background p-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
          />

          <p class="text-xs text-muted-foreground">Tip: Include responsibilities and qualifications sections for best results.</p>
        </div>

        <template #footer>
          <Button variant="ghost" @click="isPasteJobDialogOpen = false">Cancel</Button>
          <Button :disabled="isParsingJob" class="bg-primary-gradient text-white hover:opacity-90" @click="submitJobDescription">
            <Loader2 v-if="isParsingJob" class="h-4 w-4 mr-2 animate-spin" />
            Submit
          </Button>
        </template>
      </FullscreenDialog>
    </div>
  </AppLayout>
</template>
