<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ref } from 'vue';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Easy Apply',
        href: '/easy-apply',
    },
];

defineProps<{
    events: {
        data: Array<{
            id: number
            resume_variant: string | null
            job_title: string | null
            company: string | null
            source_host: string | null
            page_url: string | null
            fields: string[] | null
            field_details: Array<Record<string, string>> | null
            filled_count: number
            created_at: string | null
        }>
        links: Array<{ url: string | null; label: string; active: boolean }>
        meta?: any
    }
}>()

async function generateSignedUrl() {
    try {
        const res = await fetch('/api/profile/signed/generate', { headers: { Accept: 'application/json' } })
        const json = await res.json()
        const url = json.url
        await navigator.clipboard.writeText(url)
        alert('Signed URL copied to clipboard!')
    } catch (e) {
        alert('Failed to generate signed URL')
    }
}

// Autofill profile form (used by Chrome extension)
const page = usePage();
const user = page.props.auth.user as any;
const autofillForm = useForm({
    name: user?.name || '',
    email: user?.email || '',
    phone: (user as any)?.phone || '',
    location: (user as any)?.location || '',
    website: (user as any)?.website || '',
    summary: (user as any)?.summary || '',
});

function saveAutofillProfile() {
    autofillForm.patch(route('profile.update'), { preserveScroll: true });
}

const isProfileDialogOpen = ref(false);
</script>

<template>
    <Head title="Easy Apply" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-card-gradient">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold">Easy Apply</h2>
                    <div class="flex items-center gap-2">
                        <Dialog v-model:open="isProfileDialogOpen">
                            <DialogTrigger as-child>
                                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                                    Edit Autofill Profile
                                </button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle>Autofill Profile</DialogTitle>
                                    <DialogDescription>
                                        These details are used by the Chrome extension to autofill job applications on Greenhouse, Lever, Workday, SuccessFactors/SAP, Indeed, LinkedIn, Glassdoor, and ZipRecruiter.
                                    </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="saveAutofillProfile" class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2 max-h-[70vh] overflow-y-auto pr-1">
                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="block text-sm font-medium mb-1" for="af-name">Full name</label>
                                        <input id="af-name" v-model="autofillForm.name" type="text" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="John Appleseed" autocomplete="name" />
                                        <p class="mt-1 text-xs text-muted-foreground">Your full name. We’ll split it into first and last name for ATS forms.</p>
                                        <div v-if="autofillForm.errors.name" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.name }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="af-email">Email</label>
                                        <input id="af-email" v-model="autofillForm.email" type="email" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="you@example.com" autocomplete="email" />
                                        <div v-if="autofillForm.errors.email" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.email }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="af-phone">Phone</label>
                                        <input id="af-phone" v-model="autofillForm.phone" type="tel" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="(+1) 555-123-4567" autocomplete="tel" />
                                        <div v-if="autofillForm.errors.phone" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.phone }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="af-location">Location</label>
                                        <input id="af-location" v-model="autofillForm.location" type="text" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="San Francisco, CA" autocomplete="address-level2" />
                                        <div v-if="autofillForm.errors.location" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.location }}</div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium mb-1" for="af-website">Website or LinkedIn URL</label>
                                        <input id="af-website" v-model="autofillForm.website" type="url" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="https://www.linkedin.com/in/you or https://you.dev" autocomplete="url" />
                                        <p class="mt-1 text-xs text-muted-foreground">Tip: Put your LinkedIn profile here for ATS fields that specifically ask for it. GitHub/Portfolio also supported.</p>
                                        <div v-if="autofillForm.errors.website" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.website }}</div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium mb-1" for="af-summary">Professional summary</label>
                                        <textarea id="af-summary" v-model="autofillForm.summary" rows="4" class="w-full rounded-md border border-sidebar-border/60 bg-background px-3 py-2" placeholder="Brief summary or default cover letter"></textarea>
                                        <div v-if="autofillForm.errors.summary" class="mt-1 text-xs text-rose-600">{{ autofillForm.errors.summary }}</div>
                                    </div>

                                    <div class="sm:col-span-2 flex items-center gap-2 pt-2">
                                        <button type="submit" :disabled="autofillForm.processing" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                                            Save Autofill Profile
                                        </button>
                                        <span v-if="autofillForm.recentlySuccessful" class="text-sm text-muted-foreground">Saved.</span>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                        <button @click="generateSignedUrl" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                            Copy Extension Signed URL
                        </button>
                    </div>
                </div>
                <p class="text-muted-foreground mt-1">Use this with the browser extension to autofill applications. The table below shows your recent autofills.</p>
            </div>

            <!-- Removed inline card editor; dialog trigger moved to header above -->

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card-gradient">
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-muted-foreground">
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Resume</th>
                                <th class="py-2 pr-4">Job Title</th>
                                <th class="py-2 pr-4">Company</th>
                                <th class="py-2 pr-4">Platform</th>
                                <th class="py-2 pr-4">Fields</th>
                                <th class="py-2 pr-4">Details</th>
                                <th class="py-2 pr-4">Count</th>
                                <th class="py-2 pr-4">Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="e in events.data" :key="e.id" class="border-t border-sidebar-border/60 dark:border-sidebar-border">
                                <td class="py-2 pr-4 whitespace-nowrap">{{ e.created_at }}</td>
                                <td class="py-2 pr-4">{{ e.resume_variant ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ e.job_title ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ e.company ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ e.source_host ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ e.fields?.join(', ') ?? '—' }}</td>
                                <td class="py-2 pr-4">
                                    <details v-if="e.field_details?.length">
                                        <summary class="cursor-pointer text-primary">View</summary>
                                        <div class="mt-1 max-h-40 overflow-auto">
                                            <div v-for="(d, i) in e.field_details" :key="i" class="text-xs text-muted-foreground">
                                                • {{ d.type || 'field' }} — name: {{ d.name || '—' }}, id: {{ d.id || '—' }}, label: {{ d.label || '—' }}
                                            </div>
                                        </div>
                                    </details>
                                    <span v-else class="text-muted-foreground">—</span>
                                </td>
                                <td class="py-2 pr-4">{{ e.filled_count }}</td>
                                <td class="py-2 pr-4">
                                    <a :href="e.page_url ?? '#'" target="_blank" class="text-primary hover:underline" v-if="e.page_url">Open</a>
                                    <span v-else class="text-muted-foreground">—</span>
                                </td>
                            </tr>
                            <tr v-if="events.data.length === 0">
                                <td colspan="7" class="text-center py-8 text-muted-foreground">No autofill activity yet.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-center gap-2 mt-4" v-if="events.links?.length">
                        <template v-for="(link, idx) in events.links" :key="idx">
                            <a v-if="link.url" :href="link.url" class="px-3 py-1 rounded border border-sidebar-border/60 dark:border-sidebar-border" :class="{ 'bg-primary text-primary-foreground': link.active }" v-html="link.label" />
                            <span v-else class="px-3 py-1 text-muted-foreground" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
