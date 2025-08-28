<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue'
import SelectValue from '@/components/ui/select/SelectValue.vue'
import SelectContent from '@/components/ui/select/SelectContent.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import Icon from '@/components/Icon.vue'

type Question = {
  id: number
  title: string
  category: string
  difficulty: string
  views_count: number
  users_practiced_count: number
  time_ago: string
}

const props = defineProps<{
  filters: { search: string; category: string; difficulty: string }
  categories: string[]
  difficulties: string[]
  questions: {
    data: Question[]
    links: { url: string | null; label: string; active: boolean }[]
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Interview Question Bank', href: '/interview-question-bank' },
]

const form = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  difficulty: props.filters.difficulty || '',
})

function applyFilters() {
  router.get(
    '/interview-question-bank',
    {
      search: form.search || undefined,
      category: form.category || undefined,
      difficulty: form.difficulty || undefined,
    },
    { preserveState: true, replace: true, preserveScroll: true },
  )
}

watch(
  () => [form.category, form.difficulty],
  () => applyFilters(),
)

const hasFilters = computed(
  () => !!form.search || !!form.category || !!form.difficulty,
)

function clearFilters() {
  form.search = ''
  form.category = ''
  form.difficulty = ''
  applyFilters()
}

function practiceHref(q: Question) {
  return `/interview-question-bank/${q.id}`
}
</script>

<template>
  <Head title="Interview Question Bank" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <!-- Gradient Header -->
      <div class="rounded-xl p-6 text-white bg-linear-to-r from-violet-600 to-fuchsia-600 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-xl font-semibold md:text-2xl">Interview Question Bank</h1>
            <p class="text-white/80 text-sm">Practice real interview questions with smart filters.</p>
          </div>
          <div class="flex items-center gap-3 mt-3 md:mt-0">
            <Badge variant="outline" class="bg-white/10 text-white border-white/20">{{ questions.data.length }} shown</Badge>
            <Badge variant="outline" class="bg-white/10 text-white border-white/20" v-if="hasFilters">Filters active</Badge>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-background p-4 shadow-xs">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12 items-center">
          <!-- Category -->
          <div class="md:col-span-3">
            <label class="block text-sm text-muted-foreground mb-1">Category</label>
            <Select v-model="form.category">
              <SelectTrigger class="w-full">
                <SelectValue :placeholder="'All categories'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="General">General</SelectItem>
                <SelectItem value="Behavioral">Behavioral</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Difficulty -->
          <div class="md:col-span-3">
            <label class="block text-sm text-muted-foreground mb-1">Difficulty</label>
            <Select v-model="form.difficulty">
              <SelectTrigger class="w-full">
                <SelectValue :placeholder="'All difficulties'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="d in difficulties" :key="d" :value="d">{{ d }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Search -->
          <div class="md:col-span-6">
            <label class="block text-sm text-muted-foreground mb-1">Search</label>
            <div class="flex items-center gap-2">
              <div class="relative flex-1">
                <Icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                <Input
                  v-model="form.search"
                  placeholder="Search questions by title..."
                  class="pl-9"
                  @keyup.enter="applyFilters"
                />
              </div>
              <Button variant="outline" class="shrink-0" @click="applyFilters">Search</Button>
              <Button variant="ghost" class="shrink-0" v-if="hasFilters" @click="clearFilters">Clear</Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions List -->
      <div class="flex flex-col gap-3">
        <div
          v-for="q in questions.data"
          :key="q.id"
          class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-background p-4 shadow-xs"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-2 text-muted-foreground">
              <Icon name="eye" />
              <span class="text-sm">{{ q.views_count.toLocaleString() }} views</span>
            </div>
            <Badge variant="outline" class="text-xs bg-accent/40 border-accent/40">{{ q.time_ago }}</Badge>
          </div>

          <h3 class="mt-3 text-base font-medium leading-snug">{{ q.title }}</h3>

          <div class="mt-3 flex flex-wrap items-center gap-2">
            <Badge class="bg-primary/10 text-primary border-primary/20">{{ q.category }}</Badge>
            <Badge class="bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20">{{ q.difficulty }}</Badge>
            <div class="ml-2 flex items-center gap-1 text-muted-foreground text-sm">
              <Icon name="users" />
              <span>{{ q.users_practiced_count.toLocaleString() }} practiced</span>
            </div>
          </div>

          <div class="mt-4 flex justify-end">
            <Link :href="practiceHref(q)">
              <Button variant="gradient">Practice with this question</Button>
            </Link>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-2 flex items-center justify-center gap-2">
        <template v-for="link in questions.links" :key="link.label">
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
