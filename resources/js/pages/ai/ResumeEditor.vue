<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Collapsible } from '@/components/ui/collapsible'
import { CollapsibleContent } from '@/components/ui/collapsible'
import { CollapsibleTrigger } from '@/components/ui/collapsible'
import { notifySuccess, notifyError } from '@/lib/notify'
import { Link as LinkIcon, Calendar, GraduationCap, Briefcase, User as UserIcon, Globe, Mail, Folder, CheckCircle2, ChevronDown, ChevronRight } from 'lucide-vue-next'

interface Education { school: string; degree: string; field_of_study: string; start_date: string; end_date: string; currently_studying: boolean; grade: string; activities: string }
interface Experience { title: string; company: string; location: string; start_date: string; end_date: string; currently_working: boolean; employment_type_id: number | null; industry: string; description: string }
interface LicenseAndCertification { name: string; issuing_organization: string; issue_date: string; expiration_date: string; credential_id: string; credential_url: string }
interface Project { name: string; description: string; start_date: string; end_date: string; url: string; skills_used: string }
interface Skill { name: string; proficiency_level: number }

interface PrefillData { name: string; location: string; email: string; website: string; summary: string; educations: Education[]; experiences: Experience[]; licenses_and_certifications: LicenseAndCertification[]; projects: Project[]; skills: Skill[] }

const page: any = usePage()
const user = page.props.auth.user
const prefillData = (page.props.prefillData || {}) as Partial<PrefillData>

const formData = reactive({
  name: prefillData.name || user.name,
  location: prefillData.location || '',
  email: prefillData.email || user.email,
  website: prefillData.website || '',
  summary: prefillData.summary || '',
  educations: prefillData.educations && prefillData.educations.length > 0 ? prefillData.educations : [
    { school: '', degree: '', field_of_study: '', start_date: '', end_date: '', currently_studying: false, grade: '', activities: '' }
  ],
  experiences: prefillData.experiences && prefillData.experiences.length > 0 ? prefillData.experiences : [
    { title: '', company: '', location: '', start_date: '', end_date: '', currently_working: false, employment_type_id: null, industry: '', description: '' }
  ],
  licenses_and_certifications: prefillData.licenses_and_certifications && prefillData.licenses_and_certifications.length > 0 ? prefillData.licenses_and_certifications : [
    { name: '', issuing_organization: '', issue_date: '', expiration_date: '', credential_id: '', credential_url: '' }
  ],
  projects: prefillData.projects && prefillData.projects.length > 0 ? prefillData.projects : [
    { name: '', description: '', start_date: '', end_date: '', url: '', skills_used: '' }
  ],
  skills: prefillData.skills && prefillData.skills.length > 0 ? prefillData.skills : [
    { name: '', proficiency_level: 3 }
  ]
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
const removeEducation = (i: number) => formData.educations.length > 1 && formData.educations.splice(i, 1)
const addExperience = () => formData.experiences.push({ title: '', company: '', location: '', start_date: '', end_date: '', currently_working: false, employment_type_id: null, industry: '', description: '' })
const removeExperience = (i: number) => formData.experiences.length > 1 && formData.experiences.splice(i, 1)
const addProject = () => formData.projects.push({ name: '', description: '', start_date: '', end_date: '', url: '', skills_used: '' })
const removeProject = (i: number) => formData.projects.length > 1 && formData.projects.splice(i, 1)
const addLicense = () => formData.licenses_and_certifications.push({ name: '', issuing_organization: '', issue_date: '', expiration_date: '', credential_id: '', credential_url: '' })
const removeLicense = (i: number) => formData.licenses_and_certifications.length > 1 && formData.licenses_and_certifications.splice(i, 1)
const addSkill = () => formData.skills.push({ name: '', proficiency_level: 3 })
const removeSkill = (i: number) => formData.skills.length > 1 && formData.skills.splice(i, 1)

const submitForm = () => {
  router.post(route('ai-resume-builder.store'), formData, {
    onSuccess: () => notifySuccess('Your resume data has been saved.', 'Saved'),
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

// Open dialog fallback from editor
const openDialog = () => router.visit(route('ai-resume-builder'))
</script>

<template>
  <AppLayout>
    <Head title="Resume Editor" />

    <div class="px-4 md:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Resume Editor</h1>
        <div class="flex gap-2">
          <Button variant="outline" @click="openDialog">Use Dialog</Button>
          <Button @click="submitForm">Save</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Sidebar outline -->
        <aside class="hidden lg:block lg:col-span-3">
          <div class="sticky top-20 space-y-1 rounded-xl border bg-card p-3">
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
        <section class="lg:col-span-6 space-y-4">
          <!-- Personal -->
          <div id="personal" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <span>Personal</span>
              <UserIcon class="h-5 w-5 text-primary" />
            </h2>
            <Separator class="my-3" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="name">Full Name</Label>
                <Input id="name" v-model="(formData as any).name" placeholder="Your full name" />
              </div>
              <div class="space-y-2">
                <Label for="location">Location</Label>
                <Input id="location" v-model="(formData as any).location" placeholder="City, Country" />
              </div>
              <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="(formData as any).email" type="email" placeholder="you@example.com" />
              </div>
              <div class="space-y-2">
                <Label for="website">Website</Label>
                <Input id="website" v-model="(formData as any).website" placeholder="https://yourwebsite.com" />
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div id="summary" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <span>Summary</span>
              <LinkIcon class="h-5 w-5 text-primary rotate-90" />
            </h2>
            <Separator class="my-3" />
            <textarea v-model="(formData as any).summary" class="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="A brief overview of your professional background, key skills, and career goals..." />
          </div>

          <!-- Education -->
          <div id="education" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Education</span>
                <GraduationCap class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="outline" size="sm" @click="addEducation">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <Collapsible v-for="(education, i) in (formData as any).educations" :key="i" class="rounded-lg border">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ education.school || 'Education #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'school-'+i">School</Label><Input :id="'school-'+i" v-model="education.school" placeholder="University Name" /></div>
                    <div class="space-y-1"><Label :for="'degree-'+i">Degree</Label><Input :id="'degree-'+i" v-model="education.degree" placeholder="Bachelor's, Master's, etc." /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'fos-'+i">Field of Study</Label><Input :id="'fos-'+i" v-model="education.field_of_study" placeholder="Computer Science, Business, etc." /></div>
                    <div class="space-y-1"><Label :for="'start-'+i">Start (MM/YYYY)</Label><Input :id="'start-'+i" v-model="education.start_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1"><Label :for="'end-'+i">End (MM/YYYY)</Label><Input :id="'end-'+i" v-model="education.end_date" placeholder="MM/YYYY" :disabled="education.currently_studying" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'activities-'+i">Activities</Label><Input :id="'activities-'+i" v-model="education.activities" placeholder="Student organizations, clubs, etc." /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button v-if="(formData as any).educations.length>1" variant="ghost" size="sm" @click="removeEducation(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Experience -->
          <div id="experience" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Experience</span>
                <Briefcase class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="outline" size="sm" @click="addExperience">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <Collapsible v-for="(experience, i) in (formData as any).experiences" :key="i" class="rounded-lg border">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ experience.title || 'Experience #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'title-'+i">Title</Label><Input :id="'title-'+i" v-model="experience.title" placeholder="Software Engineer" /></div>
                    <div class="space-y-1"><Label :for="'company-'+i">Company</Label><Input :id="'company-'+i" v-model="experience.company" placeholder="Company Name" /></div>
                    <div class="space-y-1"><Label :for="'location-'+i">Location</Label><Input :id="'location-'+i" v-model="experience.location" placeholder="City, Country" /></div>
                    <div class="space-y-1"><Label :for="'start-exp-'+i">Start (MM/YYYY)</Label><Input :id="'start-exp-'+i" v-model="experience.start_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1"><Label :for="'end-exp-'+i">End (MM/YYYY)</Label><Input :id="'end-exp-'+i" v-model="experience.end_date" placeholder="MM/YYYY" :disabled="experience.currently_working" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'desc-'+i">Description</Label><textarea :id="'desc-'+i" v-model="experience.description" class="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Describe your responsibilities and achievements..." /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button v-if="(formData as any).experiences.length>1" variant="ghost" size="sm" @click="removeExperience(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Projects -->
          <div id="projects" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>Projects</span>
                <Folder class="h-5 w-5 text-primary" />
              </h2>
              <Button variant="outline" size="sm" @click="addProject">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <Collapsible v-for="(project, i) in (formData as any).projects" :key="i" class="rounded-lg border">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ project.name || 'Project #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'pname-'+i">Name</Label><Input :id="'pname-'+i" v-model="project.name" placeholder="Project Title" /></div>
                    <div class="space-y-1"><Label :for="'purl-'+i">URL</Label><Input :id="'purl-'+i" v-model="project.url" placeholder="https://example.com/project" /></div>
                    <div class="space-y-1"><Label :for="'pstart-'+i">Start (MM/YYYY)</Label><Input :id="'pstart-'+i" v-model="project.start_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1"><Label :for="'pend-'+i">End (MM/YYYY)</Label><Input :id="'pend-'+i" v-model="project.end_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'pskills-'+i">Skills Used</Label><Input :id="'pskills-'+i" v-model="project.skills_used" placeholder="JavaScript, React, Node.js, etc." /></div>
                    <div class="space-y-1 md:col-span-2"><Label :for="'pdesc-'+i">Description</Label><textarea :id="'pdesc-'+i" v-model="project.description" class="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Describe the project, your role, and key achievements..." /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button v-if="(formData as any).projects.length>1" variant="ghost" size="sm" @click="removeProject(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Licenses -->
          <div id="licenses" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold">Licenses & Certifications</h2>
              <Button variant="outline" size="sm" @click="addLicense">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <Collapsible v-for="(license, i) in (formData as any).licenses_and_certifications" :key="i" class="rounded-lg border">
                <CollapsibleTrigger class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-muted">
                  <div class="text-sm font-medium truncate">{{ license.name || 'License/Certification #' + (i+1) }}</div>
                  <ChevronDown class="h-4 w-4 ml-2" />
                </CollapsibleTrigger>
                <CollapsibleContent class="px-3 py-3 border-t space-y-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-1"><Label :for="'lname-'+i">Name</Label><Input :id="'lname-'+i" v-model="license.name" placeholder="Certification Name" /></div>
                    <div class="space-y-1"><Label :for="'lorg-'+i">Issuing Organization</Label><Input :id="'lorg-'+i" v-model="license.issuing_organization" placeholder="Organization Name" /></div>
                    <div class="space-y-1"><Label :for="'lissue-'+i">Issue (MM/YYYY)</Label><Input :id="'lissue-'+i" v-model="license.issue_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1"><Label :for="'lexp-'+i">Expiration (MM/YYYY)</Label><Input :id="'lexp-'+i" v-model="license.expiration_date" placeholder="MM/YYYY" /></div>
                    <div class="space-y-1"><Label :for="'lcredid-'+i">Credential ID</Label><Input :id="'lcredid-'+i" v-model="license.credential_id" placeholder="ID Number" /></div>
                    <div class="space-y-1"><Label :for="'lcredurl-'+i">Credential URL</Label><Input :id="'lcredurl-'+i" v-model="license.credential_url" placeholder="https://example.com/credential" /></div>
                  </div>
                  <div class="flex justify-end">
                    <Button v-if="(formData as any).licenses_and_certifications.length>1" variant="ghost" size="sm" @click="removeLicense(i)">Remove</Button>
                  </div>
                </CollapsibleContent>
              </Collapsible>
            </div>
          </div>

          <!-- Skills -->
          <div id="skills" class="scroll-mt-24 rounded-xl border bg-card p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold">Skills</h2>
              <Button variant="outline" size="sm" @click="addSkill">Add</Button>
            </div>
            <Separator class="my-3" />
            <div class="space-y-3">
              <div v-for="(skill, i) in (formData as any).skills" :key="i" class="rounded-lg border p-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="space-y-1"><Label :for="'sname-'+i">Name</Label><Input :id="'sname-'+i" v-model="skill.name" placeholder="JavaScript, Project Management, etc." /></div>
                <div class="space-y-1"><Label :for="'sprof-'+i">Proficiency (1-5)</Label><Input :id="'sprof-'+i" v-model.number="skill.proficiency_level" type="number" min="1" max="5" placeholder="3" /></div>
                <div class="md:col-span-2 flex justify-end"><Button v-if="(formData as any).skills.length>1" variant="ghost" size="sm" @click="removeSkill(i)">Remove</Button></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Live preview -->
        <aside class="lg:col-span-3">
          <div class="sticky top-20 space-y-4">
            <div class="rounded-xl border bg-card p-4">
              <h3 class="text-sm font-semibold mb-3">Preview</h3>
              <!-- Personal -->
              <div class="space-y-3">
                <div class="rounded-lg border bg-background/50 p-3">
                  <p class="text-xs uppercase tracking-wide text-muted-foreground flex items-center gap-2"><UserIcon class="h-4 w-4" /> Full Name</p>
                  <p class="font-medium mt-1">{{ (formData as any).name || 'Not provided' }}</p>
                </div>
                <div class="rounded-lg border bg-background/50 p-3">
                  <p class="text-xs uppercase tracking-wide text-muted-foreground flex items-center gap-2"><Mail class="h-4 w-4" /> Email</p>
                  <p class="font-medium mt-1">{{ (formData as any).email || 'Not provided' }}</p>
                </div>
                <div class="rounded-lg border bg-background/50 p-3">
                  <p class="text-xs uppercase tracking-wide text-muted-foreground flex items-center gap-2"><Globe class="h-4 w-4" /> Website</p>
                  <p class="font-medium mt-1">{{ (formData as any).website || 'Not provided' }}</p>
                </div>
              </div>

              <Separator class="my-4" />

              <!-- Education preview -->
              <div>
                <h4 class="text-sm font-semibold mb-2 flex items-center gap-2"><GraduationCap class="h-4 w-4 text-primary" /> Education</h4>
                <div v-if="(formData as any).educations.length" class="space-y-2">
                  <div v-for="(edu, i) in (formData as any).educations" :key="i" class="rounded border p-2">
                    <div class="font-medium">{{ edu.school || 'School' }}</div>
                    <div class="text-xs text-muted-foreground">{{ edu.degree }}<span v-if="edu.field_of_study"> in {{ edu.field_of_study }}</span></div>
                    <div class="text-xs text-muted-foreground flex items-center gap-1 mt-1"><Calendar class="h-3 w-3" /> {{ edu.start_date }} - {{ edu.end_date }}</div>
                  </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">No education</p>
              </div>

              <Separator class="my-4" />

              <!-- Experience preview -->
              <div>
                <h4 class="text-sm font-semibold mb-2 flex items-center gap-2"><Briefcase class="h-4 w-4 text-primary" /> Experience</h4>
                <div v-if="(formData as any).experiences.length" class="space-y-2">
                  <div v-for="(exp, i) in (formData as any).experiences" :key="i" class="rounded border p-2">
                    <div class="font-medium">{{ exp.title || 'Title' }}</div>
                    <div class="text-xs text-muted-foreground">{{ exp.company }}</div>
                    <div class="text-xs text-muted-foreground flex items-center gap-1 mt-1"><Calendar class="h-3 w-3" /> {{ exp.start_date }} - {{ exp.end_date }}</div>
                  </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">No experience</p>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </AppLayout>
</template>

