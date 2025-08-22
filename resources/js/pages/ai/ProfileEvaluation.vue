<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CheckCircle2, ChevronLeft, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const page: any = usePage();
const evaluation = page.props.evaluation || {};
const job = page.props.job || null;
const profile = page.props.profile || null;

const totalScore = computed(() => evaluation.total_score ?? null);

const SECTION_MAX_SCORES = {
    impact: 40,
    skills_and_traits: 20,
    alignment_with_job: 40,
} as const;

const SUBSECTION_MAX_SCORES = {
    impact: {
        quantifying_impact: 10,
        focus_on_achievements: 10,
        writing_quality: 12,
        varied_industry_specific_verbs: 8,
    },
    skills_and_traits: {
        problem_solving: 5,
        communication_collaboration: 5,
        initiative_innovation: 5,
        leadership_teamwork: 5,
    },
    alignment_with_job: {
        skills_match: 10,
        job_title_match: 10,
        responsibilities_qualifications: 10,
        industry_keywords_synonyms: 10,
    },
} as const;

function backToBuilder() {
    router.visit(route('ai-resume-builder'));
}

function formatSubScores(section: Record<string, any>, maxScores: Record<string, number>) {
    if (!section) return [];
    return Object.entries(section).map(([key, val]: any) => ({
        key,
        score: val?.score ?? null,
        max: maxScores[key],
        feedback: val?.feedback ?? '',
    }));
}

const impact = computed(() => formatSubScores(evaluation.impact, SUBSECTION_MAX_SCORES.impact));
const skillsAndTraits = computed(() => formatSubScores(evaluation.skills_and_traits, SUBSECTION_MAX_SCORES.skills_and_traits));
const alignment = computed(() => formatSubScores(evaluation.alignment_with_job, SUBSECTION_MAX_SCORES.alignment_with_job));

const impactScore = computed(() => impact.value.reduce((sum, row) => sum + (row.score ?? 0), 0));
const skillsAndTraitsScore = computed(() => skillsAndTraits.value.reduce((sum, row) => sum + (row.score ?? 0), 0));
const alignmentScore = computed(() => alignment.value.reduce((sum, row) => sum + (row.score ?? 0), 0));

const specificChanges = computed(() => (Array.isArray(evaluation.specific_changes) ? evaluation.specific_changes : []));

function formatField(field: string) {
    if (field === 'licenses_and_certifications') {
        return 'Licenses and Certifications';
    }
    return field
        .replaceAll('_', ' ')
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function fieldBadgeClass(field: string) {
    const colors: Record<string, string> = {
        licenses_and_certifications: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-700/20 dark:text-indigo-300',
        skills: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/20 dark:text-emerald-300',
        experiences: 'bg-blue-100 text-blue-700 dark:bg-blue-700/20 dark:text-blue-300',
        education: 'bg-amber-100 text-amber-700 dark:bg-amber-700/20 dark:text-amber-300',
        summary: 'bg-purple-100 text-purple-700 dark:bg-purple-700/20 dark:text-purple-300',
        projects: 'bg-pink-100 text-pink-700 dark:bg-pink-700/20 dark:text-pink-300',
    };
    return cn('border-transparent', colors[field] || 'bg-muted text-muted-foreground');
}
</script>

<template>
    <Head title="Profile Evaluation" />
    <AppLayout
        :breadcrumbs="[
            { title: 'AI Resume Builder', href: route('ai-resume-builder') },
            { title: 'Evaluation', href: '' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold tracking-tight">Profile Evaluation</h1>
                    <p class="text-muted-foreground">Objective evaluation based on your profile and the selected job.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="backToBuilder"> <ChevronLeft class="mr-2 h-4 w-4" /> Back to Builder </Button>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-muted-foreground">Overall Score</div>
                            <div class="text-3xl font-semibold">
                                {{ totalScore !== null ? `${totalScore}/100` : '—' }}
                            </div>
                        </div>
                        <CheckCircle2 class="h-8 w-8 text-primary" />
                    </div>
                    <div class="h-2 w-full rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary"
                            :style="{ width: totalScore !== null ? `${totalScore}%` : '0%' }"
                        />
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
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Impact</h2>
                        <div class="text-sm text-muted-foreground">{{ impactScore }}/{{ SECTION_MAX_SCORES.impact }}</div>
                    </div>
                    <div v-if="impact.length" class="flex flex-col gap-4">
                        <div v-for="row in impact" :key="row.key" class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">{{ row.key.replaceAll('_', ' ') }}</div>
                                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}/{{ row.max }}</div>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    :style="{ width: row.score != null ? `${(row.score / row.max) * 100}%` : '0%' }"
                                />
                            </div>
                            <p class="text-sm whitespace-pre-line text-muted-foreground">{{ row.feedback }}</p>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">No details provided.</div>
                </div>

                <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Skills & Traits</h2>
                        <div class="text-sm text-muted-foreground">{{ skillsAndTraitsScore }}/{{ SECTION_MAX_SCORES.skills_and_traits }}</div>
                    </div>
                    <div v-if="skillsAndTraits.length" class="flex flex-col gap-4">
                        <div v-for="row in skillsAndTraits" :key="row.key" class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">{{ row.key.replaceAll('_', ' ') }}</div>
                                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}/{{ row.max }}</div>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    :style="{ width: row.score != null ? `${(row.score / row.max) * 100}%` : '0%' }"
                                />
                            </div>
                            <p class="text-sm whitespace-pre-line text-muted-foreground">{{ row.feedback }}</p>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">No details provided.</div>
                </div>

                <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Alignment with Job</h2>
                        <div class="text-sm text-muted-foreground">{{ alignmentScore }}/{{ SECTION_MAX_SCORES.alignment_with_job }}</div>
                    </div>
                    <div v-if="alignment.length" class="flex flex-col gap-4">
                        <div v-for="row in alignment" :key="row.key" class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">{{ row.key.replaceAll('_', ' ') }}</div>
                                <div class="text-sm text-muted-foreground">{{ row.score ?? '—' }}/{{ row.max }}</div>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    :style="{ width: row.score != null ? `${(row.score / row.max) * 100}%` : '0%' }"
                                />
                            </div>
                            <p class="text-sm whitespace-pre-line text-muted-foreground">{{ row.feedback }}</p>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">No details provided.</div>
                </div>
            </div>

      <!-- Overall Recommendation -->
      <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 flex flex-col gap-4">
        <div class="flex items-center gap-2">
          <Sparkles class="h-4 w-4 text-primary" />
          <h2 class="text-lg font-semibold">Overall Recommendation</h2>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="flex flex-col gap-1">
            <div class="text-sm text-muted-foreground">Strengths</div>
            <p class="text-sm whitespace-pre-line">{{ evaluation.overall?.strengths || '—' }}</p>
          </div>
          <div class="flex flex-col gap-1">
            <div class="text-sm text-muted-foreground">Areas for Improvement</div>
            <p class="text-sm whitespace-pre-line">{{ evaluation.overall?.area_for_improvement || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Specific Changes -->
      <div class="rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 flex flex-col gap-3">
        <h2 class="text-lg font-semibold">Specific Immediate Changes</h2>
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
                  <td class="py-2 pr-4">
                      <Badge variant="outline" :class="fieldBadgeClass(c.field)">
                          {{ formatField(c.field) }}
                      </Badge>
                  </td>
                  <td class="py-2 pr-4 whitespace-pre-line">{{ c.old_value }}</td>
                <td class="py-2 pr-4 whitespace-pre-line">{{ c.new_value }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-sm text-muted-foreground">No specific changes.</div>
      </div>
        </div>
    </AppLayout>
</template>
