<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, computed, onMounted, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Collapsible } from '@/components/ui/collapsible'
import { CollapsibleContent } from '@/components/ui/collapsible'
import { CollapsibleTrigger } from '@/components/ui/collapsible'
import { notifySuccess, notifyError } from '@/lib/notify'
import { Link as LinkIcon, Calendar, GraduationCap, Briefcase, User as UserIcon, Globe, Mail, Folder, CheckCircle2, ChevronDown, ChevronRight, Award, ListChecks } from 'lucide-vue-next'

interface Education { school: string; degree: string; field_of_study: string; start_date: string; end_date: string; currently_studying: boolean; grade: string; activities: string }
interface Experience { title: string; company: string; location: string; start_date: string; end_date: string; currently_working: boolean; employment_type_id: number | null; industry: string; description: string }
interface LicenseAndCertification { name: string; issuing_organization: string; issue_date: string; expiration_date: string; credential_id: string; credential_url: string }
interface Project { name: string; description: string; start_date: string; end_date: string; url: string; skills_used: string }
interface Skill { name: string; proficiency_level: number }

interface PrefillData { name: string; location: string; email: string; website: string; summary: string; educations: Education[]; experiences: Experience[]; licenses_and_certifications: LicenseAndCertification[]; projects: Project[]; skills: Skill[] }

const page: any = usePage()
const user = page.props.auth.user
const prefillData = (page.props.prefillData || {}) as Partial<PrefillData>
const aiParsedData = page.props.aiParsedData as any

interface FormData {
  name: string
  location: string
  email: string
  website: string
  summary: string
  educations: Education[]
  experiences: Experience[]
  licenses_and_certifications: LicenseAndCertification[]
  projects: Project[]
  skills: Skill[]
}

const formData = reactive<FormData>({
  name: prefillData.name || user.name,
  location: prefillData.location || '',
  email: prefillData.email || user.email,
  website: prefillData.website || '',
  summary: prefillData.summary || '',
  // Initialize arrays from prefillData (fallback to one blank row like the main builder)
  educations: prefillData.educations && prefillData.educations.length > 0
    ? prefillData.educations
    : [{ school: '', degree: '', field_of_study: '', start_date: '', end_date: '', currently_studying: false, grade: '', activities: '' }],
  experiences: prefillData.experiences && prefillData.experiences.length > 0
    ? prefillData.experiences
    : [{ title: '', company: '', location: '', start_date: '', end_date: '', currently_working: false, employment_type_id: null, industry: '', description: '' }],
  licenses_and_certifications: prefillData.licenses_and_certifications && prefillData.licenses_and_certifications.length > 0
    ? prefillData.licenses_and_certifications
    : [{ name: '', issuing_organization: '', issue_date: '', expiration_date: '', credential_id: '', credential_url: '' }],
  projects: prefillData.projects && prefillData.projects.length > 0
    ? prefillData.projects
    : [{ name: '', description: '', start_date: '', end_date: '', url: '', skills_used: '' }],
  skills: prefillData.skills && prefillData.skills.length > 0
    ? prefillData.skills
    : [{ name: '', proficiency_level: 3 }],
})

// If AI parsed data exists, populate form similarly to the main builder
const populateFormWithAiData = (aiData: any) => {
  if (!aiData) return
  if (aiData.name) formData.name = aiData.name
  if (aiData.location) formData.location = aiData.location
  if (aiData.description) formData.summary = aiData.description

  if (aiData.educations && aiData.educations.length > 0) {
    formData.educations = aiData.educations.map((edu: any) => ({
      school: edu.school || '',
      degree: edu.degree || '',
      field_of_study: edu.field_of_study || '',
      start_date: edu.start_date || '',
      end_date: edu.end_date || '',
      currently_studying: !edu.end_date,
      grade: edu.grade || '',
      activities: edu.description || ''
    }))
  }

  if (aiData.experiences && aiData.experiences.length > 0) {
    formData.experiences = aiData.experiences.map((exp: any) => ({
      title: exp.title || '',
      company: exp.company || '',
      location: exp.location || '',
      start_date: exp.start_date || '',
      end_date: exp.end_date || '',
      currently_working: exp.currently_working || false,
      employment_type_id: null,
      industry: '',
      description: exp.description || ''
    }))
  }

  if (aiData.license_and_certifications && aiData.license_and_certifications.length > 0) {
    formData.licenses_and_certifications = aiData.license_and_certifications.map((license: any) => ({
      name: license.name || '',
      issuing_organization: license.issuing_organization || '',
      issue_date: license.issue_date || '',
      expiration_date: license.expiration_date || '',
      credential_id: license.credential_id || '',
      credential_url: license.credential_url || ''
    }))
  }

  if (aiData.projects && aiData.projects.length > 0) {
    formData.projects = aiData.projects.map((project: any) => ({
      name: project.name || '',
      description: project.description || '',
      start_date: project.start_date || '',
      end_date: project.end_date || '',
      url: project.url || '',
      skills_used: Array.isArray(project.skills_used) ? project.skills_used.join(', ') : (project.skills_used || '')
    }))
  }

  if (aiData.skills && aiData.skills.length > 0) {
    formData.skills = aiData.skills.map((skill: string) => ({
      name: skill,
      proficiency_level: 3
    }))
  }
}

// Apply AI data on mount and when it changes
onMounted(() => {
  if (aiParsedData) populateFormWithAiData(aiParsedData)
})

watch(() => page.props.aiParsedData, (val) => {
  if (val) populateFormWithAiData(val)
})

// Simple completion indicators
const counts = computed(() => ({
  edu: formData.educations.filter(e => e.school || e.degree || e.field_of_study).length,
  exp: formData.experiences.filter(e => e.title || e.company).length,
  proj: formData.projects.filter(p => p.name).length,
  lic: formData.licenses_and_certifications.filter(l => l.name).length,
  skills: formData.skills.filter(s => s.name).length,
}))

const sections = [
  { id: 'personal', label: 'Personal' },
  { id: 'summary', label: 'Summary' },
  { id: 'education', label: 'Education' },
  { id: 'experience', label: 'Experience' },
  { id: 'projects', label: 'Projects' },
  { id: 'licenses', label: 'Licenses' },
  { id: 'skills', label: 'Skills' },
]

const addEducation = () => formData.educations.push({ school: '', degree: '', field_of_study: '', start_date: '', end_date: '', currently_studying: false, grade: '', activities: '' })
const removeEducation = (i: number) => formData.educations.splice(i, 1)
const addExperience = () => formData.experiences.push({ title: '', company: '', location: '', start_date: '', end_date: '', currently_working: false, employment_type_id: null, industry: '', description: '' })
const removeExperience = (i: number) => formData.experiences.splice(i, 1)
const addProject = () => formData.projects.push({ name: '', description: '', start_date: '', end_date: '', url: '', skills_used: '' })
const removeProject = (i: number) => formData.projects.splice(i, 1)
const addLicense = () => formData.licenses_and_certifications.push({ name: '', issuing_organization: '', issue_date: '', expiration_date: '', credential_id: '', credential_url: '' })
const removeLicense = (i: number) => formData.licenses_and_certifications.splice(i, 1)
const addSkill = () => formData.skills.push({ name: '', proficiency_level: 3 })
const removeSkill = (i: number) => formData.skills.splice(i, 1)

const submitForm = () => {
  router.post(route('ai-resume-builder.store'), formData, {
    onSuccess: () => {
      notifySuccess('Your resume data has been saved.', 'Saved')
      router.visit(route('ai-resume-builder'))
    },
    onError: (errors: any) => {
      const bag = errors?.errors || errors
      if (bag && typeof bag === 'object') {
        Object.values(bag).forEach((msg: any) => notifyError(Array.isArray(msg) ? msg.join('\n') : String(msg), 'Validation error'))
      } else {
        notifyError('Please review your inputs.', 'Validation error')
      }
    },
  })
}


</script>

<template>
  <AppLayout>
    <Head title="Resume Editor" />

    <div class="px-4 md:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Resume Editor</h1>
        <Button variant="ghost" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white" @click="submitForm">Save</Button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Sidebar outline -->
        <aside class="hidden lg:block lg:col-span-3">
          <div class="sticky top-20 space-y-1 rounded-xl border bg-card-gradient p-3">
            <div v-for="s in sections" :key="s.id">
              <a :href="`#${s.id}`" class="flex items-center justify-between rounded-md px-3 py-2 hover:bg-muted">
                <span class="text-sm font-medium">{{ s.label }}</span>
                <span v-if="s.id==='education' && counts.edu" class="text-xs text-muted-foreground">{{ counts.edu }}</span>
                <span v-else-if="s.id==='experience' && counts.exp" class="text-xs text-muted-foreground">{{ counts.exp }}</span>
                <span v-else-if="s.id==='projects' && counts.proj" class="text-xs text-muted-foreground">{{ counts.proj }}</span>
                <span v-else-if="s.id==='licenses' && counts.lic" class="text-xs text-muted-foreground">{{ counts.lic }}</span>
                <span v-else-if="s.id==='skills' && counts.skills" class="text-xs text-muted-foreground">{{ counts.skills }}</span>
              </a>
            </div>
          </div>
        </aside>

        <!-- Main editor -->
        <section class="lg:col-span-9 space-y-4">
          <!-- Personal -->
          <div id="personal" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <span>Personal</span>
              <UserIcon class="h-5 w-5 text-primary" />
            </h2>
            <Separator class="my-3" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="name">Full Name</Label>
                <Input id="name" v-model="formData.name" placeholder="Your full name" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" />
              </div>
              <div class="space-y-2">
                <Label for="location">Location</Label>
                <Input id="location" v-model="formData.location" placeholder="City, Country" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" />
              </div>
              <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="formData.email" type="email" placeholder="you@example.com" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" />
              </div>
              <div class="space-y-2">
                <Label for="website">Website</Label>
                <Input id="website" v-model="formData.website" placeholder="https://yourwebsite.com" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" />
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div id="summary" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <span>Summary</span>
              <LinkIcon class="h-5 w-5 text-primary rotate-90" />
            </h2>
            <Separator class="my-3" />
            <textarea v-model="formData.summary" class="flex min-h-[120px] w-full rounded-md border-2 border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary" placeholder="A brief overview of your professional background, key skills, and career goals..." />
          </div>

          <!-- Education -->
          <div id="education" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Education</span>
                <GraduationCap class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="ghost" size="sm" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white" @click="addEducation">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-if="formData.educations.length === 0" class="text-center py-4 text-muted-foreground">
                No education entries added yet. Click "Add" to add your first education.
              </div>
              <Collapsible v-for="(education, i) in formData.educations" :key="i" class="rounded-lg border bg-card-gradient">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ education.school || 'Education #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'school-'+i">School</Label><Input :id="'school-'+i" v-model="education.school" placeholder="University Name" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'degree-'+i">Degree</Label><Input :id="'degree-'+i" v-model="education.degree" placeholder="Bachelor's, Master's, etc." class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'fos-'+i">Field of Study</Label><Input :id="'fos-'+i" v-model="education.field_of_study" placeholder="Computer Science, Business, etc." class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'start-'+i">Start (MM/YYYY)</Label><Input :id="'start-'+i" v-model="education.start_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'end-'+i">End (MM/YYYY)</Label><Input :id="'end-'+i" v-model="education.end_date" placeholder="MM/YYYY" :disabled="education.currently_studying" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'activities-'+i">Activities</Label><Input :id="'activities-'+i" v-model="education.activities" placeholder="Student organizations, clubs, etc." class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button variant="ghost" size="sm" class="bg-gradient-to-r from-red-500 to-red-700 hover:from-red-600 hover:to-red-800 text-white" @click="removeEducation(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Experience -->
          <div id="experience" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Experience</span>
                <Briefcase class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="ghost" size="sm" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white" @click="addExperience">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-if="formData.experiences.length === 0" class="text-center py-4 text-muted-foreground">
                No experience entries added yet. Click "Add" to add your first experience.
              </div>
              <Collapsible v-for="(experience, i) in formData.experiences" :key="i" class="rounded-lg border bg-card-gradient">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ experience.title || 'Experience #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'title-'+i">Title</Label><Input :id="'title-'+i" v-model="experience.title" placeholder="Software Engineer" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'company-'+i">Company</Label><Input :id="'company-'+i" v-model="experience.company" placeholder="Company Name" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'location-'+i">Location</Label><Input :id="'location-'+i" v-model="experience.location" placeholder="City, Country" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'start-exp-'+i">Start (MM/YYYY)</Label><Input :id="'start-exp-'+i" v-model="experience.start_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'end-exp-'+i">End (MM/YYYY)</Label><Input :id="'end-exp-'+i" v-model="experience.end_date" placeholder="MM/YYYY" :disabled="experience.currently_working" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'desc-'+i">Description</Label><textarea :id="'desc-'+i" v-model="experience.description" class="flex min-h-[100px] w-full rounded-md border-2 border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary" placeholder="Describe your responsibilities and achievements..." /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button variant="ghost" size="sm" class="bg-gradient-to-r from-red-500 to-red-700 hover:from-red-600 hover:to-red-800 text-white" @click="removeExperience(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Projects -->
          <div id="projects" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Projects</span>
                <Folder class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="ghost" size="sm" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white" @click="addProject">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-if="formData.projects.length === 0" class="text-center py-4 text-muted-foreground">
                No project entries added yet. Click "Add" to add your first project.
              </div>
              <Collapsible v-for="(project, i) in formData.projects" :key="i" class="rounded-lg border bg-card-gradient">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ project.name || 'Project #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'pname-'+i">Name</Label><Input :id="'pname-'+i" v-model="project.name" placeholder="Project Title" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'purl-'+i">URL</Label><Input :id="'purl-'+i" v-model="project.url" placeholder="https://example.com/project" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'pstart-'+i">Start (MM/YYYY)</Label><Input :id="'pstart-'+i" v-model="project.start_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'pend-'+i">End (MM/YYYY)</Label><Input :id="'pend-'+i" v-model="project.end_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'pskills-'+i">Skills Used</Label><Input :id="'pskills-'+i" v-model="project.skills_used" placeholder="JavaScript, React, Node.js, etc." class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'pdesc-'+i">Description</Label><textarea :id="'pdesc-'+i" v-model="project.description" class="flex min-h-[100px] w-full rounded-md border-2 border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary" placeholder="Describe the project, your role, and key achievements..." /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button variant="ghost" size="sm" class="bg-gradient-to-r from-red-500 to-red-700 hover:from-red-600 hover:to-red-800 text-white" @click="removeProject(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Licenses -->
          <div id="licenses" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Licenses & Certifications</span>
                <Award class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="ghost" size="sm" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white" @click="addLicense">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-if="formData.licenses_and_certifications.length === 0" class="text-center py-4 text-muted-foreground">
                No license or certification entries added yet. Click "Add" to add your first license or certification.
              </div>
              <Collapsible v-for="(license, i) in formData.licenses_and_certifications" :key="i" class="rounded-lg border bg-card-gradient">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ license.name || 'License/Certification #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'lname-'+i">Name</Label><Input :id="'lname-'+i" v-model="license.name" placeholder="Certification Name" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'lorg-'+i">Issuing Organization</Label><Input :id="'lorg-'+i" v-model="license.issuing_organization" placeholder="Organization Name" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'lissue-'+i">Issue (MM/YYYY)</Label><Input :id="'lissue-'+i" v-model="license.issue_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'lexp-'+i">Expiration (MM/YYYY)</Label><Input :id="'lexp-'+i" v-model="license.expiration_date" placeholder="MM/YYYY" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'lcredid-'+i">Credential ID</Label><Input :id="'lcredid-'+i" v-model="license.credential_id" placeholder="ID Number" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                    <div class="space-y-1"><Label :for="'lcredurl-'+i">Credential URL</Label><Input :id="'lcredurl-'+i" v-model="license.credential_url" placeholder="https://example.com/credential" class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button variant="ghost" size="sm" class="bg-gradient-to-r from-red-500 to-red-700 hover:from-red-600 hover:to-red-800 text-white" @click="removeLicense(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Skills -->
          <div id="skills" class="scroll-mt-24 rounded-xl border bg-card-gradient p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Skills</span>
                <ListChecks class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="ghost" size="sm" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white" @click="addSkill">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-if="formData.skills.length === 0" class="text-center py-4 text-muted-foreground">
                No skills added yet. Click "Add" to add your first skill.
              </div>
              <Collapsible v-for="(skill, i) in formData.skills" :key="i" class="rounded-lg border bg-card-gradient">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ skill.name || 'Skill #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1">
                      <Label :for="'sname-'+i">Name</Label>
                      <Input :id="'sname-'+i" v-model="skill.name" placeholder="JavaScript, Project Management, etc." class="border-2 border-input focus:border-primary focus:ring-2 focus:ring-primary/30" />
                    </div>
                    <div class="space-y-1">
                      <Label :for="'sprof-'+i">Proficiency Level</Label>
                      <div class="flex items-center space-x-2">
                        <input
                          :id="'sprof-'+i"
                          type="range"
                          v-model="skill.proficiency_level"
                          min="1"
                          max="5"
                          class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                        />
                        <span class="text-sm font-medium w-8 text-center">{{ skill.proficiency_level }}/5</span>
                      </div>
                      <div class="flex justify-between text-xs text-muted-foreground">
                        <span>Beginner</span>
                        <span>Expert</span>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <Button variant="ghost" size="sm" class="bg-gradient-to-r from-red-500 to-red-700 hover:from-red-600 hover:to-red-800 text-white" @click="removeSkill(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
