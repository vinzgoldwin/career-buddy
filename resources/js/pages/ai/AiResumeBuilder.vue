<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FileText, Upload, Sparkles, Plus, Trash2 } from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { ref, reactive } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'AI Resume Builder',
        href: '/ai-resume-builder',
    },
];

const page = usePage();
const user = page.props.auth.user;

// Form data
const formData = reactive({
  name: user.name,
  location: user.location || '',
  email: user.email,
  website: user.website || '',
  summary: user.summary || '',
  educations: [{
    school: '',
    degree: '',
    field_of_study: '',
    start_date: '',
    end_date: '',
    currently_studying: false,
    grade: '',
    activities: ''
  }],
  experiences: [{
    title: '',
    company: '',
    location: '',
    start_date: '',
    end_date: '',
    currently_working: false,
    employment_type: '',
    industry: '',
    description: ''
  }],
  licenses_and_certifications: [{
    name: '',
    issuing_organization: '',
    issue_date: '',
    expiration_date: '',
    credential_id: '',
    credential_url: ''
  }],
  projects: [{
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    url: '',
    skills_used: ''
  }],
  skills: [{
    name: '',
    proficiency_level: 3
  }]
});

// Add new item to array
const addEducation = () => {
  formData.educations.push({
    school: '',
    degree: '',
    field_of_study: '',
    start_date: '',
    end_date: '',
    currently_studying: false,
    grade: '',
    activities: ''
  });
};

const addExperience = () => {
  formData.experiences.push({
    title: '',
    company: '',
    location: '',
    start_date: '',
    end_date: '',
    currently_working: false,
    employment_type: '',
    industry: '',
    description: ''
  });
};

const addLicense = () => {
  formData.licenses_and_certifications.push({
    name: '',
    issuing_organization: '',
    issue_date: '',
    expiration_date: '',
    credential_id: '',
    credential_url: ''
  });
};

const addProject = () => {
  formData.projects.push({
    name: '',
    description: '',
    start_date: '',
    end_date: '',
    url: '',
    skills_used: ''
  });
};

const addSkill = () => {
  formData.skills.push({
    name: '',
    proficiency_level: 3
  });
};

// Remove item from array
const removeEducation = (index: number) => {
  if (formData.educations.length > 1) {
    formData.educations.splice(index, 1);
  }
};

const removeExperience = (index: number) => {
  if (formData.experiences.length > 1) {
    formData.experiences.splice(index, 1);
  }
};

const removeLicense = (index: number) => {
  if (formData.licenses_and_certifications.length > 1) {
    formData.licenses_and_certifications.splice(index, 1);
  }
};

const removeProject = (index: number) => {
  if (formData.projects.length > 1) {
    formData.projects.splice(index, 1);
  }
};

const removeSkill = (index: number) => {
  if (formData.skills.length > 1) {
    formData.skills.splice(index, 1);
  }
};

// Submit form
const submitForm = () => {
  router.post(route('ai-resume-builder.store'), formData, {
    onSuccess: () => {
      isDialogOpen.value = false;
    },
    onError: (errors) => {
      console.log('Validation errors:', errors);
    }
  });
};

const isDialogOpen = ref(false);
</script>

<template>
    <Head title="AI Resume Builder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6 overflow-x-auto">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight">AI Resume Builder</h1>
                <p class="text-muted-foreground">
                    Upload your resume or paste your resume content below, and let our AI turn them into polished, standout documents.
                    From refining structure to highlighting your unique strengths, we ensure you shine in every hiring process.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Upload Resume Card -->
                <Card class="flex flex-col">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Upload class="h-5 w-5" />
                            Upload Resume
                        </CardTitle>
                        <CardDescription>
                            Upload your existing resume in PDF format only
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col">
                        <div class="flex-1 space-y-4">
                            <div class="flex items-center justify-center rounded-lg border-2 border-dashed p-6">
                                <div class="flex flex-col items-center gap-2 text-center">
                                    <FileText class="h-8 w-8 text-muted-foreground" />
                                    <div class="text-sm text-muted-foreground">
                                        <p>Drag and drop your PDF resume here, or click to browse</p>
                                        <p class="mt-1 text-xs">Only PDF files are accepted</p>
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" class="w-full">
                                Select PDF File
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Resume Input Card -->
                <Card class="flex flex-col">
                    <CardHeader>
                        <CardTitle>Build Resume with AI</CardTitle>
                        <CardDescription>
                            Fill in your details to create a professional resume
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col">
                        <div class="flex-1 flex flex-col justify-between">
                            <p class="text-sm text-muted-foreground mb-4">
                                Click the button below to open a form where you can enter all your professional details.
                                Our AI will use this information to create a polished resume for you.
                            </p>
                            <Dialog v-model:open="isDialogOpen">
                                <DialogTrigger as-child>
                                    <Button class="w-full">
                                        <Sparkles class="mr-2 h-4 w-4" />
                                        Build Resume with AI
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="max-w-screen max-h-screen h-screen w-screen m-0 rounded-none p-0 overflow-hidden">
                                    <DialogHeader class="px-6 pt-6">
                                        <DialogTitle>Build Your Professional Resume</DialogTitle>
                                        <DialogDescription>
                                            Fill in your details below. All fields except name and email are optional.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div class="overflow-y-auto max-h-[calc(100vh-140px)] px-6 pb-6">
                                      <form @submit.prevent="submitForm" class="space-y-6">
                                          <!-- Personal Information -->
                                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                              <div class="space-y-2">
                                                  <Label for="name">Full Name</Label>
                                                  <Input id="name" v-model="formData.name" disabled />
                                              </div>

                                              <div class="space-y-2">
                                                  <Label for="location">Location</Label>
                                                  <Input id="location" v-model="formData.location" placeholder="City, Country" />
                                              </div>

                                              <div class="space-y-2">
                                                  <Label for="email">Email</Label>
                                                  <Input id="email" v-model="formData.email" type="email" disabled />
                                              </div>

                                              <div class="space-y-2">
                                                  <Label for="website">Website</Label>
                                                  <Input id="website" v-model="formData.website" placeholder="https://yourwebsite.com" />
                                              </div>
                                          </div>

                                          <div class="space-y-2">
                                              <Label for="summary">Professional Summary</Label>
                                              <textarea
                                                  id="summary"
                                                  v-model="formData.summary"
                                                  placeholder="A brief overview of your professional background, key skills, and career goals..."
                                                  class="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                              />
                                          </div>

                                          <!-- Education Section -->
                                          <div class="space-y-4">
                                              <div class="flex items-center justify-between">
                                                  <h3 class="text-lg font-medium">Education</h3>
                                                  <Button type="button" variant="outline" size="sm" @click="addEducation">
                                                      <Plus class="h-4 w-4 mr-2" />
                                                      Add Education
                                                  </Button>
                                              </div>

                                              <div v-for="(education, index) in formData.educations" :key="index" class="border rounded-lg p-4 space-y-4">
                                                  <div class="flex justify-between items-start">
                                                      <h4 class="font-medium">Education #{{ index + 1 }}</h4>
                                                      <Button
                                                          v-if="formData.educations.length > 1"
                                                          type="button"
                                                          variant="ghost"
                                                          size="sm"
                                                          @click="removeEducation(index)"
                                                      >
                                                          <Trash2 class="h-4 w-4" />
                                                      </Button>
                                                  </div>

                                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                      <div class="space-y-2">
                                                          <Label :for="'school-' + index">School</Label>
                                                          <Input :id="'school-' + index" v-model="education.school" placeholder="University Name" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'degree-' + index">Degree</Label>
                                                          <Input :id="'degree-' + index" v-model="education.degree" placeholder="Bachelor's, Master's, etc." />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'field-' + index">Field of Study</Label>
                                                          <Input :id="'field-' + index" v-model="education.field_of_study" placeholder="Computer Science, Business, etc." />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'grade-' + index">Grade</Label>
                                                          <Input :id="'grade-' + index" v-model="education.grade" placeholder="GPA or Score" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'edu-start-' + index">Start Date</Label>
                                                          <Input :id="'edu-start-' + index" v-model="education.start_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'edu-end-' + index">End Date</Label>
                                                          <Input :id="'edu-end-' + index" v-model="education.end_date" type="date" :disabled="education.currently_studying" />
                                                      </div>

                                                      <div class="space-y-2 flex items-end">
                                                          <div class="flex items-center space-x-2">
                                                              <input
                                                                  :id="'currently-studying-' + index"
                                                                  type="checkbox"
                                                                  v-model="education.currently_studying"
                                                                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                              />
                                                              <Label :for="'currently-studying-' + index">Currently Studying</Label>
                                                          </div>
                                                      </div>

                                                      <div class="space-y-2 md:col-span-2">
                                                          <Label :for="'activities-' + index">Activities and Societies</Label>
                                                          <Input :id="'activities-' + index" v-model="education.activities" placeholder="Student organizations, clubs, etc." />
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Experience Section -->
                                          <div class="space-y-4">
                                              <div class="flex items-center justify-between">
                                                  <h3 class="text-lg font-medium">Work Experience</h3>
                                                  <Button type="button" variant="outline" size="sm" @click="addExperience">
                                                      <Plus class="h-4 w-4 mr-2" />
                                                      Add Experience
                                                  </Button>
                                              </div>

                                              <div v-for="(experience, index) in formData.experiences" :key="index" class="border rounded-lg p-4 space-y-4">
                                                  <div class="flex justify-between items-start">
                                                      <h4 class="font-medium">Experience #{{ index + 1 }}</h4>
                                                      <Button
                                                          v-if="formData.experiences.length > 1"
                                                          type="button"
                                                          variant="ghost"
                                                          size="sm"
                                                          @click="removeExperience(index)"
                                                      >
                                                          <Trash2 class="h-4 w-4" />
                                                      </Button>
                                                  </div>

                                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                      <div class="space-y-2">
                                                          <Label :for="'title-' + index">Job Title</Label>
                                                          <Input :id="'title-' + index" v-model="experience.title" placeholder="Software Engineer" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'company-' + index">Company</Label>
                                                          <Input :id="'company-' + index" v-model="experience.company" placeholder="Company Name" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'exp-location-' + index">Location</Label>
                                                          <Input :id="'exp-location-' + index" v-model="experience.location" placeholder="City, Country" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'employment-type-' + index">Employment Type</Label>
                                                          <Input :id="'employment-type-' + index" v-model="experience.employment_type" placeholder="Full-time, Part-time, etc." />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'exp-start-' + index">Start Date</Label>
                                                          <Input :id="'exp-start-' + index" v-model="experience.start_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'exp-end-' + index">End Date</Label>
                                                          <Input :id="'exp-end-' + index" v-model="experience.end_date" type="date" :disabled="experience.currently_working" />
                                                      </div>

                                                      <div class="space-y-2 flex items-end">
                                                          <div class="flex items-center space-x-2">
                                                              <input
                                                                  :id="'currently-working-' + index"
                                                                  type="checkbox"
                                                                  v-model="experience.currently_working"
                                                                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                              />
                                                              <Label :for="'currently-working-' + index">Currently Working</Label>
                                                          </div>
                                                      </div>

                                                      <div class="space-y-2 md:col-span-2">
                                                          <Label :for="'industry-' + index">Industry</Label>
                                                          <Input :id="'industry-' + index" v-model="experience.industry" placeholder="Technology, Finance, etc." />
                                                      </div>

                                                      <div class="space-y-2 md:col-span-2">
                                                          <Label :for="'description-' + index">Description</Label>
                                                          <textarea
                                                              :id="'description-' + index"
                                                              v-model="experience.description"
                                                              placeholder="Describe your responsibilities and achievements..."
                                                              class="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                                          />
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Licenses & Certifications Section -->
                                          <div class="space-y-4">
                                              <div class="flex items-center justify-between">
                                                  <h3 class="text-lg font-medium">Licenses & Certifications</h3>
                                                  <Button type="button" variant="outline" size="sm" @click="addLicense">
                                                      <Plus class="h-4 w-4 mr-2" />
                                                      Add License/Certification
                                                  </Button>
                                              </div>

                                              <div v-for="(license, index) in formData.licenses_and_certifications" :key="index" class="border rounded-lg p-4 space-y-4">
                                                  <div class="flex justify-between items-start">
                                                      <h4 class="font-medium">License/Certification #{{ index + 1 }}</h4>
                                                      <Button
                                                          v-if="formData.licenses_and_certifications.length > 1"
                                                          type="button"
                                                          variant="ghost"
                                                          size="sm"
                                                          @click="removeLicense(index)"
                                                      >
                                                          <Trash2 class="h-4 w-4" />
                                                      </Button>
                                                  </div>

                                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                      <div class="space-y-2">
                                                          <Label :for="'cert-name-' + index">Name</Label>
                                                          <Input :id="'cert-name-' + index" v-model="license.name" placeholder="Certification Name" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'issuing-org-' + index">Issuing Organization</Label>
                                                          <Input :id="'issuing-org-' + index" v-model="license.issuing_organization" placeholder="Organization Name" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'issue-date-' + index">Issue Date</Label>
                                                          <Input :id="'issue-date-' + index" v-model="license.issue_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'exp-date-' + index">Expiration Date</Label>
                                                          <Input :id="'exp-date-' + index" v-model="license.expiration_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'cred-id-' + index">Credential ID</Label>
                                                          <Input :id="'cred-id-' + index" v-model="license.credential_id" placeholder="ID Number" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'cred-url-' + index">Credential URL</Label>
                                                          <Input :id="'cred-url-' + index" v-model="license.credential_url" placeholder="https://example.com/credential" />
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Projects Section -->
                                          <div class="space-y-4">
                                              <div class="flex items-center justify-between">
                                                  <h3 class="text-lg font-medium">Projects</h3>
                                                  <Button type="button" variant="outline" size="sm" @click="addProject">
                                                      <Plus class="h-4 w-4 mr-2" />
                                                      Add Project
                                                  </Button>
                                              </div>

                                              <div v-for="(project, index) in formData.projects" :key="index" class="border rounded-lg p-4 space-y-4">
                                                  <div class="flex justify-between items-start">
                                                      <h4 class="font-medium">Project #{{ index + 1 }}</h4>
                                                      <Button
                                                          v-if="formData.projects.length > 1"
                                                          type="button"
                                                          variant="ghost"
                                                          size="sm"
                                                          @click="removeProject(index)"
                                                      >
                                                          <Trash2 class="h-4 w-4" />
                                                      </Button>
                                                  </div>

                                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                      <div class="space-y-2">
                                                          <Label :for="'project-name-' + index">Project Name</Label>
                                                          <Input :id="'project-name-' + index" v-model="project.name" placeholder="Project Title" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'project-url-' + index">Project URL</Label>
                                                          <Input :id="'project-url-' + index" v-model="project.url" placeholder="https://example.com/project" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'project-start-' + index">Start Date</Label>
                                                          <Input :id="'project-start-' + index" v-model="project.start_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'project-end-' + index">End Date</Label>
                                                          <Input :id="'project-end-' + index" v-model="project.end_date" type="date" />
                                                      </div>

                                                      <div class="space-y-2 md:col-span-2">
                                                          <Label :for="'project-skills-' + index">Skills Used</Label>
                                                          <Input :id="'project-skills-' + index" v-model="project.skills_used" placeholder="JavaScript, React, Node.js, etc." />
                                                      </div>

                                                      <div class="space-y-2 md:col-span-2">
                                                          <Label :for="'project-desc-' + index">Description</Label>
                                                          <textarea
                                                              :id="'project-desc-' + index"
                                                              v-model="project.description"
                                                              placeholder="Describe the project, your role, and key achievements..."
                                                              class="flex min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                                          />
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Skills Section -->
                                          <div class="space-y-4">
                                              <div class="flex items-center justify-between">
                                                  <h3 class="text-lg font-medium">Skills</h3>
                                                  <Button type="button" variant="outline" size="sm" @click="addSkill">
                                                      <Plus class="h-4 w-4 mr-2" />
                                                      Add Skill
                                                  </Button>
                                              </div>

                                              <div v-for="(skill, index) in formData.skills" :key="index" class="border rounded-lg p-4 space-y-4">
                                                  <div class="flex justify-between items-start">
                                                      <h4 class="font-medium">Skill #{{ index + 1 }}</h4>
                                                      <Button
                                                          v-if="formData.skills.length > 1"
                                                          type="button"
                                                          variant="ghost"
                                                          size="sm"
                                                          @click="removeSkill(index)"
                                                      >
                                                          <Trash2 class="h-4 w-4" />
                                                      </Button>
                                                  </div>

                                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                      <div class="space-y-2">
                                                          <Label :for="'skill-name-' + index">Skill Name</Label>
                                                          <Input :id="'skill-name-' + index" v-model="skill.name" placeholder="JavaScript, Project Management, etc." />
                                                      </div>

                                                      <div class="space-y-2">
                                                          <Label :for="'proficiency-' + index">Proficiency Level</Label>
                                                          <div class="flex items-center space-x-2">
                                                              <input
                                                                  :id="'proficiency-' + index"
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
                                              </div>
                                          </div>
                                      </form>
                                    </div>

                                    <DialogFooter class="px-6 py-4 border-t">
                                        <Button type="button" variant="outline" @click="isDialogOpen = false">
                                            Cancel
                                        </Button>
                                        <Button type="button" @click="submitForm">
                                            <Sparkles class="mr-2 h-4 w-4" />
                                            Generate AI Resume
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
