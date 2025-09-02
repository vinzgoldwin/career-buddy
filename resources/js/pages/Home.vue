<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import GradualSpacing from '@/components/GradualSpacing.vue';
import GridBeams from '@/components/magicui/GridBeams.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];

const features = [
    {
        title: 'Resume Builder',
        description: 'Build a standout resume with AI-guided templates and instant suggestions.',
        icon: '📄',
    },
    {
        title: 'Resume Evaluation',
        description: 'Analyse your resume against the job description and receive tailored suggestions to stand out.',
        icon: '💬',
    },
    {
        title: 'Question Bank',
        description: 'Real interview questions from top companies, including behavioral, technical, and case questions. Practice anytime with self-guided Q&A.',
        icon: '📚',
    },
    {
        title: 'Easy Apply',
        description: 'Want to apply to 1,000 jobs with one click? No worries — all positions are smart-matched to your resume. Trust us, we won\'t get it wrong.',
        icon: '🚀',
    },
];
</script>

<template>
    <Head title="Home" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <GridBeams class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-8" :grid-size="44" :ray-count="18" :ray-speed="1.1" :ray-opacity="0.4">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="mb-6">
                        <GradualSpacing
                            :duration="50"
                            :delay-multiple="40"
                            text="Welcome to Career Buddy"
                            class="text-4xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent"
                        />
                    </div>
                    <div class="text-lg text-muted-foreground mb-10 max-w-2xl mx-auto">
                        <GradualSpacing
                            :duration="50"
                            :delay-multiple="40"
                            text="Your all-in-one platform to supercharge your job search and career growth"
                            class="text-lg"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
                        <div
                            v-for="(feature, index) in features"
                            :key="index"
                            class="bg-card-gradient rounded-lg p-6 text-left border border-sidebar-border/50 dark:border-sidebar-border/70 transition-all hover:scale-[1.02]"
                        >
                            <div class="flex items-start gap-4">
                                <span class="text-2xl">{{ feature.icon }}</span>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2">{{ feature.title }}</h3>
                                    <p class="text-muted-foreground">{{ feature.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12">
                        <Link
                            :href="route('ai-resume-builder')"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </GridBeams>
        </div>
    </AppLayout>
</template>
