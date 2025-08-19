<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

const features = [
  {
    title: 'AI Resume Builder',
    description: 'Upload or build your resume and enhance it with AI.',
    icon: '📄',
  },
  {
    title: 'Mock Interview',
    description: 'Practice interviews with our AI-powered mock interview tool.',
    icon: '💬',
  },
  {
    title: 'Question Bank',
    description:
      'Real interview questions from top companies — behavioral, technical, and case questions included.',
    icon: '📚',
  },
  {
    title: 'Auto Apply',
    description:
      'Apply to many jobs at once. Positions are smart-matched to your resume.',
    icon: '🚀',
  },
]
</script>

<template>
  <Head title="Career Buddy">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="flex min-h-screen flex-col items-center bg-app-gradient p-6 text-foreground lg:justify-center lg:p-8">
    <header class="mb-6 w-full text-sm not-has-[nav]:hidden">
      <nav class="mx-auto flex w-full max-w-4xl items-center justify-end gap-4">
        <Link
          v-if="$page.props.auth.user"
          :href="route('home')"
          class="inline-block rounded-sm border border-sidebar-border/70 px-5 py-1.5 text-sm leading-normal hover:bg-muted/40 dark:border-sidebar-border"
        >
          Home
        </Link>
        <template v-else>
          <Link
            :href="route('login')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-sidebar-border/70 dark:hover:border-sidebar-border"
          >
            Log in
          </Link>
          <Link
            :href="route('register')"
            class="inline-block rounded-sm border border-sidebar-border/70 px-5 py-1.5 text-sm leading-normal hover:bg-muted/40 dark:border-sidebar-border"
          >
            Register
          </Link>
        </template>
      </nav>
    </header>

    <main class="flex w-full items-center justify-center lg:grow">
      <div class="w-full max-w-4xl rounded-xl border border-sidebar-border/70 bg-card-gradient p-8 text-center shadow-sm dark:border-sidebar-border">
        <h1 class="mb-6 bg-gradient-to-r from-primary to-secondary bg-clip-text text-4xl font-bold text-transparent">
          Welcome to Career Buddy
        </h1>
        <p class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground">
          Your all-in-one platform to supercharge your job search and career growth.
        </p>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div
            v-for="(feature, index) in features"
            :key="index"
            class="rounded-lg border border-sidebar-border/50 bg-background/60 p-6 text-left backdrop-blur-[2px] transition-all hover:scale-[1.02] dark:border-sidebar-border/70"
          >
            <div class="flex items-start gap-4">
              <span class="text-2xl">{{ feature.icon }}</span>
              <div>
                <h3 class="mb-2 text-xl font-semibold">{{ feature.title }}</h3>
                <p class="text-muted-foreground">{{ feature.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-12" v-if="!$page.props.auth.user">
          <div class="inline-flex items-center gap-3">
            <Link
              :href="route('login')"
              class="inline-flex h-11 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
            >
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="inline-flex h-11 items-center justify-center rounded-md border border-sidebar-border/70 px-8 text-sm font-medium ring-offset-background transition-colors hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:border-sidebar-border"
            >
              Register
            </Link>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

