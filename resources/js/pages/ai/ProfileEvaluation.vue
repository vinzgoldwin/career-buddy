<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { CheckCircle2, ChevronLeft, Sparkles } from 'lucide-vue-next'
import { computed } from 'vue'

const page: any = usePage()
const evaluation = page.props.evaluation || {}
const job = page.props.job || null
const profile = page.props.profile || null

const totalScore = computed(() => evaluation.total_score ?? null)

function backToBuilder() {
  router.visit(route('ai-resume-builder'))
}

function formatSubScores(section: Record<string, any>) {
  if (!section) return []
  return Object.entries(section).map(([key, val]: any) => ({
    key,
    score: val?.score ?? null,
    feedback: val?.feedback ?? '',
  }))
}

const impact = computed(() => formatSubScores(evaluation.impact))
const skillsTraits = computed(() => formatSubScores(evaluation.skills_and_traits))
const alignment = computed(() => formatSubScores(evaluation.alignment_with_job))

const specificChanges = computed(() => Array.isArray(evaluation.specific_changes) ? evaluation.specific_changes : [])
</script>

<template>
  <Head title="Profile Evaluation" />
  <AppLayout :breadcrumbs="[{title: 'AI Resume Builder', href: route('ai-resume-builder')}, {title: 'Evaluation', href: ''}]">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight">Profile Evaluation</h1>
          <p class="text-muted-foreground">Objective evaluation based on your profile and the selected job.</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="backToBuilder">
            <ChevronLeft class="h-4 w-4 mr-2" /> Back to Builder
          </Button>
        </div>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-muted-foreground">Overall Score</div>
              <div class="text-3xl font-semibold">{{ totalScore ?? '—' }}</div>
            </div>
            <CheckCircle2 class="h-8 w-8 text-primary" />
          </div>
        </div>

        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5" v-if="job">
          <div class="text-sm text-muted-foreground">Target Role</div>
          <div class="font-medium">{{ job.title || '—' }}</div>
          <div class="text-xs text-muted-foreground">{{ job.company_name || '' }}</div>
        </div>

        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5" v-if="profile">
          <div class="text-sm text-muted-foreground">Profile</div>
          <div class="font-medium">{{ profile.name }}</div>
          <div class="text-xs text-muted-foreground">{{ profile.email }}</div>
        </div>
      </div>

      <!-- Sections -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
          <h2 class="text-lg font-semibold mb-3">Impact</h2>
          <div v-if="impact.length" class="space-y-4">
            <div v-for="row in impact" :key="row.key" class="space-y-1">
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium">{{ row.key.replaceAll('_',' ') }}</div>
                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}</div>
              </div>
              <p class="text-sm text-muted-foreground whitespace-pre-line">{{ row.feedback }}</p>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground">No details provided.</div>
        </div>

        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
          <h2 class="text-lg font-semibold mb-3">Skills & Traits</h2>
          <div v-if="skillsTraits.length" class="space-y-4">
            <div v-for="row in skillsTraits" :key="row.key" class="space-y-1">
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium">{{ row.key.replaceAll('_',' ') }}</div>
                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}</div>
              </div>
              <p class="text-sm text-muted-foreground whitespace-pre-line">{{ row.feedback }}</p>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground">No details provided.</div>
        </div>

        <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
          <h2 class="text-lg font-semibold mb-3">Alignment with Job</h2>
          <div v-if="alignment.length" class="space-y-4">
            <div v-for="row in alignment" :key="row.key" class="space-y-1">
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium">{{ row.key.replaceAll('_',' ') }}</div>
                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}</div>
              </div>
              <p class="text-sm text-muted-foreground whitespace-pre-line">{{ row.feedback }}</p>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground">No details provided.</div>
        </div>
      </div>

      <!-- Overall Recommendation -->
      <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
        <div class="flex items-center gap-2 mb-2">
          <Sparkles class="h-4 w-4 text-primary" />
          <h2 class="text-lg font-semibold">Overall Recommendation</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <div class="text-sm text-muted-foreground mb-1">Strengths</div>
            <p class="text-sm whitespace-pre-line">{{ evaluation.overall?.strengths || '—' }}</p>
          </div>
          <div>
            <div class="text-sm text-muted-foreground mb-1">Areas for Improvement</div>
            <p class="text-sm whitespace-pre-line">{{ evaluation.overall?.area_for_improvement || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Specific Changes -->
      <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
        <h2 class="text-lg font-semibold mb-3">Specific Immediate Changes</h2>
        <div v-if="specificChanges.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-muted-foreground">
                <th class="py-2 pr-4">Field</th>
                <th class="py-2 pr-4">Old Value</th>
                <th class="py-2 pr-4">New Value</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in specificChanges" :key="i" class="border-t">
                <td class="py-2 pr-4">{{ c.field }}</td>
                <td class="py-2 pr-4 whitespace-pre-line">{{ c.old_value }}</td>
                <td class="py-2 pr-4 whitespace-pre-line">{{ c.new_value }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-sm text-muted-foreground">No specific changes suggested.</div>
      </div>
    </div>
  </AppLayout>
</template>

