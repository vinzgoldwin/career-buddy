<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { notifySuccess } from '@/lib/notify';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CheckCircle2, ChevronLeft, Info, Loader2, Sparkles } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
const applyingIds = ref<number[]>([]);
const applyingAll = ref(false);

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

function parseSkills(value: string | null | undefined) {
    if (!value) return [] as string[];
    return value
        .split(',')
        .map((s) => s.trim())
        .filter((s) => s.length > 0);
}

function diffSkills(oldValue: string | null | undefined, newValue: string | null | undefined) {
    const oldSkills = new Set(parseSkills(oldValue).map((s) => s.toLowerCase()));
    return parseSkills(newValue).map((skill) => ({
        skill,
        isNew: !oldSkills.has(skill.toLowerCase()),
    }));
}

function applyChange(change: any) {
    if (!change?.id) return;
    if (!applyingIds.value.includes(change.id)) applyingIds.value.push(change.id);
    router.post(
        route('ai-evaluation.apply-change', { evaluation: evaluation.id, change: change.id }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                applyingIds.value = applyingIds.value.filter((id) => id !== change.id);
            },
            onSuccess: () => {
                // Show success notification
                notifySuccess('Change applied successfully!', 'Success');

                // Manually update the applied status in the current data
                if (Array.isArray(evaluation.specific_changes)) {
                    evaluation.specific_changes = evaluation.specific_changes.map((c) => (c.id === change.id ? { ...c, applied: true } : c));
                }
                // Then reload to get the server state
                router.reload({ only: ['evaluation'] });
            },
        },
    );
}

function applyAllChanges() {
    applyingAll.value = true;
    router.post(
        route('ai-evaluation.apply-all', { evaluation: evaluation.id }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                applyingAll.value = false;
            },
            onSuccess: () => {
                // Show success notification
                notifySuccess('All changes applied successfully!', 'Success');

                // Manually update the applied status in the current data
                if (Array.isArray(evaluation.specific_changes)) {
                    const updatedChanges = evaluation.specific_changes.map((c) => ({
                        ...c,
                        applied: true,
                    }));
                    evaluation.specific_changes = updatedChanges;
                }
                // Then reload to get the server state
                router.reload({ only: ['evaluation'] });
            },
        },
    );
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
                <div class="flex flex-col gap-4 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
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
                        <div class="h-full rounded-full bg-primary" :style="{ width: totalScore !== null ? `${totalScore}%` : '0%' }" />
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
                <div class="flex flex-col gap-4 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
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

                <div class="flex flex-col gap-4 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
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

                <div class="flex flex-col gap-4 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5 xl:col-span-1">
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
            <div class="flex flex-col gap-4 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
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
            <div class="flex flex-col gap-3 rounded-xl border bg-card-gradient p-5 ring-1 ring-black/5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Specific Changes</h2>
                    <Button size="sm" variant="outline" :disabled="applyingAll" @click="applyAllChanges">
                        <Loader2 v-if="applyingAll" class="mr-2 h-3.5 w-3.5 animate-spin" />
                        Apply All
                    </Button>
                </div>
                <div v-if="specificChanges.length" class="flex flex-col gap-4">
                    <div
                        v-for="c in specificChanges"
                        :key="c.id"
                        class="rounded-lg border p-4"
                        :class="[c.applied ? 'bg-emerald-50/60 dark:bg-emerald-900/20' : '']"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <template v-if="c.reference">
                                    <TooltipProvider :delay-duration="0">
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Badge
                                                    variant="outline"
                                                    :class="
                                                        cn(fieldBadgeClass(c.field), 'inline-flex items-center !pr-2 pl-2.5 whitespace-nowrap')
                                                    "
                                                    class="inline-flex items-center"
                                                >
                                                    {{ formatField(c.field) }}
                                                    <Info class="ml-1 h-3 w-3 shrink-0" />
                                                </Badge>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{{ c.reference }}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </template>
                                <template v-else>
                                    <Badge variant="outline" :class="fieldBadgeClass(c.field)">
                                        {{ formatField(c.field) }}
                                    </Badge>
                                </template>
                            </div>
                            <div class="text-right">
                                <template v-if="!c.applied">
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="bg-secondary-gradient px-3 py-1.5 text-white hover:opacity-90"
                                        :disabled="applyingIds.includes(c.id)"
                                        @click="applyChange(c)"
                                    >
                                        <Loader2 v-if="applyingIds.includes(c.id)" class="mr-2 h-3.5 w-3.5 animate-spin" />
                                        Apply
                                    </Button>
                                </template>
                                <template v-else>
                                    <Badge variant="secondary" class="gap-1">
                                        <CheckCircle2 class="h-3 w-3" />
                                        Applied
                                    </Badge>
                                </template>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-4 md:grid-cols-2">
                            <template v-if="c.field === 'skills'">
                                <div>
                                    <div class="mb-1 text-xs font-medium text-muted-foreground">Old Value</div>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="skill in parseSkills(c.old_value)"
                                            :key="`old-${skill}`"
                                            variant="outline"
                                        >
                                            {{ skill }}
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 text-xs font-medium text-muted-foreground">New Value</div>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="item in diffSkills(c.old_value, c.new_value)"
                                            :key="`new-${item.skill}`"
                                            :variant="item.isNew ? 'default' : 'outline'"
                                            :class="item.isNew ? fieldBadgeClass('skills') : ''"
                                        >
                                            {{ item.skill }}
                                        </Badge>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div>
                                    <div class="mb-1 text-xs font-medium text-muted-foreground">Old Value</div>
                                    <p class="text-sm whitespace-pre-line">{{ c.old_value }}</p>
                                </div>
                                <div>
                                    <div class="mb-1 text-xs font-medium text-muted-foreground">New Value</div>
                                    <p class="text-sm whitespace-pre-line">{{ c.new_value }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-muted-foreground">No specific changes.</div>
            </div>
        </div>
    </AppLayout>
</template>
