<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FileText, Upload, Plus, Trash2, Sparkles } from 'lucide-vue-next';
import { ref, reactive, watch, onMounted } from 'vue';
import FullscreenDialog from '@/components/FullscreenDialog.vue';
import FileUploadDialog from '@/components/FileUploadDialog.vue';

// Define the structure of prefillData
interface Education {
  school: string;
  degree: string;
  field_of_study: string;
  start_date: string;
  end_date: string;
  currently_studying: boolean;
  grade: string;
  activities: string;
}

interface Experience {
  title: string;
  company: string;
  location: string;
  start_date: string;
  end_date: string;
  currently_working: boolean;
  employment_type_id: number | null;
  industry: string;
  description: string;
}

interface LicenseAndCertification {
  name: string;
  issuing_organization: string;
  issue_date: string;
  expiration_date: string;
  credential_id: string;
  credential_url: string;
}

interface Project {
  name: string;
  description: string;
  start_date: string;
  end_date: string;
  url: string;
  skills_used: string;
}

interface Skill {
  name: string;
  proficiency_level: number;
}

interface PrefillData {
  name: string;
  location: string;
  email: string;
  website: string;
  summary: string;
  educations: Education[];
  experiences: Experience[];
  licenses_and_certifications: LicenseAndCertification[];
  projects: Project[];
  skills: Skill[];
}

// Define the structure of the AI parsed data
interface AiParsedData {
  name: string;
  location: string;
  description: string;
  headline: string;
  languages: string[];
  educations: Array<{
    start_date: string;
    end_date: string;
    school: string;
    degree: string;
    description: string;
    grade: string;
    field_of_study: string;
  }>;
  experiences: Array<{
    start_date: string;
    end_date: string | null;
    company: string;
    title: string;
    description: string;
    location: string;
    currently_working: boolean;
    employment_type: string;
  }>;
  skills: string[];
  license_and_certifications: Array<{
    name: string;
    issuing_organization: string;
    issue_date: string;
    expiration_date: string;
    credential_id: string;
    credential_url: string;
  }>;
  projects: Array<{
    name: string;
    description: string;
    start_date: string;
    end_date: string | null;
    url: string | null;
    skills_used: string[];
  }>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'AI Resume Builder',
        href: '/ai-resume-builder',
    },
];

const page: any = usePage();
const user = page.props.auth.user as User;
const prefillData = (page.props.prefillData || {}) as Partial<PrefillData>;

// Form data
const formData = reactive({
  name: prefillData.name || user.name,
  location: prefillData.location || '',
  email: prefillData.email || user.email,
  website: prefillData.website || '',
  summary: prefillData.summary || '',
  educations: prefillData.educations && prefillData.educations.length > 0
    ? prefillData.educations
    : [{
        school: '',
        degree: '',
        field_of_study: '',
        start_date: '',
        end_date: '',
        currently_studying: false,
        grade: '',
        activities: ''
      }],
  experiences: prefillData.experiences && prefillData.experiences.length > 0
    ? prefillData.experiences
    : [{
        title: '',
        company: '',
        location: '',
        start_date: '',
        end_date: '',
        currently_working: false,
        employment_type_id: null,
        industry: '',
        description: ''
      }],
  licenses_and_certifications: prefillData.licenses_and_certifications && prefillData.licenses_and_certifications.length > 0
    ? prefillData.licenses_and_certifications
    : [{
        name: '',
        issuing_organization: '',
        issue_date: '',
        expiration_date: '',
        credential_id: '',
        credential_url: ''
      }],
  projects: prefillData.projects && prefillData.projects.length > 0
    ? prefillData.projects
    : [{
        name: '',
        description: '',
        start_date: '',
        end_date: '',
        url: '',
        skills_used: ''
      }],
  skills: prefillData.skills && prefillData.skills.length > 0
    ? prefillData.skills
    : [{
        name: '',
        proficiency_level: 3
      }]
});

// Dialog states
const isDialogOpen = ref(false);
const isUploadDialogOpen = ref(false);

// Check for AI parsed data in page props on component mount
onMounted(() => {
  console.log('Page props on mount:', page.props);
  const aiParsedData = page.props.aiParsedData;
  if (aiParsedData) {
    console.log('AI Parsed Data found on mount:', aiParsedData);
    populateFormWithAiData(aiParsedData);
    isDialogOpen.value = true;
  }
});

// Watch for changes in page props to auto-open dialog with AI parsed data
watch(() => page.props, (newProps) => {
  console.log('Page props changed:', newProps);
  if (newProps.aiParsedData) {
    console.log('AI Parsed Data found in watch:', newProps.aiParsedData);
    populateFormWithAiData(newProps.aiParsedData);
    isDialogOpen.value = true;
  }
}, { deep: true });

// Function to populate form with AI parsed data
const populateFormWithAiData = (aiData: any) => {
  console.log('Populating form with AI data:', aiData);

  // Populate basic info
  if (aiData.name) formData.name = aiData.name;
  if (aiData.location) formData.location = aiData.location;
  if (aiData.description) formData.summary = aiData.description;

  // Populate educations
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
    }));
  }

  // Populate experiences
  if (aiData.experiences && aiData.experiences.length > 0) {
    formData.experiences = aiData.experiences.map((exp: any) => ({
      title: exp.title || '',
      company: exp.company || '',
      location: exp.location || '',
      start_date: exp.start_date || '',
      end_date: exp.end_date || '',
      currently_working: exp.currently_working || false,
      employment_type_id: null, // This would need to be mapped from employment_type string
      industry: '',
      description: exp.description || ''
    }));
  }

  // Populate licenses and certifications
  if (aiData.license_and_certifications && aiData.license_and_certifications.length > 0) {
    formData.licenses_and_certifications = aiData.license_and_certifications.map((license: any) => ({
      name: license.name || '',
      issuing_organization: license.issuing_organization || '',
      issue_date: license.issue_date || '',
      expiration_date: license.expiration_date || '',
      credential_id: license.credential_id || '',
      credential_url: license.credential_url || ''
    }));
  }

  // Populate projects
  if (aiData.projects && aiData.projects.length > 0) {
    formData.projects = aiData.projects.map((project: any) => ({
      name: project.name || '',
      description: project.description || '',
      start_date: project.start_date || '',
      end_date: project.end_date || '',
      url: project.url || '',
      skills_used: Array.isArray(project.skills_used) ? project.skills_used.join(', ') : (project.skills_used || '')
    }));
  }

  // Populate skills
  if (aiData.skills && aiData.skills.length > 0) {
    formData.skills = aiData.skills.map((skill: string) => ({
      name: skill,
      proficiency_level: 3 // Default proficiency level
    }));
  }

  console.log('Form data after population:', formData);
};

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
    employment_type_id: null,
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
  console.log('Form submitted:', formData);
  // Send the data to the backend
  router.post(route('ai-resume-builder.store'), formData, {
    onSuccess: () => {
      isDialogOpen.value = false;
    },
    onError: (errors: any) => {
      console.log('Validation errors:', errors);
    }
  });
};

// Handle file selection from upload dialog
const handleFileSelected = (file: File) => {
  isUploadDialogOpen.value = false;

  // Create FormData object
  const formDataObj = new FormData();
  formDataObj.append('resume', file);

  router.post(route('ai-resume-builder.upload'), formDataObj, {
    onSuccess: (response: any) => {
      console.log('File uploaded successfully:', response);
    },
    onError: (errors: any) => {
      console.log('Upload errors:', errors);
    }
  });
};

</script>

<template>
    <Head title="AI Resume Builder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6 overflow-x-auto">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight">AI Resume Builder</h1>
                <p class="text-muted-foreground">
                    Upload your existing resume or build a new one by entering your information below.
                    Our AI will turn your details into polished, standout documents that help you shine in every hiring process.
                </p>
            </div>

            <div class="flex flex-col gap-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Upload Resume Button -->
                    <Button variant="outline" class="flex items-center gap-2 w-fit" @click="isUploadDialogOpen = true">
                        <Upload class="h-5 w-5" />
                        Upload Resume
                    </Button>

                    <!-- Resume Builder Button -->
                    <Button class="flex items-center gap-2 w-fit" @click="isDialogOpen = true">
                        <FileText class="h-5 w-5" />
                        Resume Builder
                    </Button>

                    
                </div>

                <!-- Beautiful View of User's Information -->
                <div class="border rounded-lg p-6 bg-card">
                    <div class="space-y-6">
                        <!-- Personal Information Preview -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Personal Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Full Name</p>
                                    <p class="font-medium">{{ formData.name || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Location</p>
                                    <p class="font-medium">{{ formData.location || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Email</p>
                                    <p class="font-medium">{{ formData.email || 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Website</p>
                                    <p class="font-medium">{{ formData.website || 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Summary Preview -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Professional Summary</h2>
                            <p class="font-medium">{{ formData.summary || 'Not provided' }}</p>
                        </div>

                        <!-- Education Preview -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Education</h2>
                            <div v-if="formData.educations && formData.educations.length > 0" class="space-y-4">
                                <div v-for="(education, index) in formData.educations" :key="index" class="border rounded-lg p-4">
                                    <h3 class="font-bold">{{ education.school || 'School not provided' }}</h3>
                                    <p>{{ education.degree }} in {{ education.field_of_study }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ education.start_date }} - {{ education.currently_studying ? 'Present' : education.end_date }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No education information provided</p>
                        </div>

                        <!-- Work Experience Preview -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Work Experience</h2>
                            <div v-if="formData.experiences && formData.experiences.length > 0" class="space-y-4">
                                <div v-for="(experience, index) in formData.experiences" :key="index" class="border rounded-lg p-4">
                                    <h3 class="font-bold">{{ experience.title || 'Position not provided' }}</h3>
                                    <p>{{ experience.company }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ experience.start_date }} - {{ experience.currently_working ? 'Present' : experience.end_date }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No work experience provided</p>
                        </div>

                        <!-- Skills Preview -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Skills</h2>
                            <div v-if="formData.skills && formData.skills.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="(skill, index) in formData.skills" :key="index" class="px-3 py-1 bg-primary/10 rounded-full text-sm">
                                    {{ skill.name || 'Skill not provided' }}
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No skills provided</p>
                        </div>
                    </div>
                </div>

                <!-- Full-screen Dialog for entering details -->
                <FullscreenDialog
                  :open="isDialogOpen"
                  title="Enter Your Professional Details"
                  description="Fill in your details below. All fields except name and email are optional."
                  @close="isDialogOpen = false"
                >
                  <form @submit.prevent="submitForm" class="space-y-8">
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
                                              <Input :id="'edu-start-' + index" v-model="education.start_date" placeholder="MM/YYYY" />
                                          </div>

                                          <div class="space-y-2">
                                              <Label :for="'edu-end-' + index">End Date</Label>
                                              <Input :id="'edu-end-' + index" v-model="education.end_date" placeholder="MM/YYYY" :disabled="education.currently_studying" />
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
                                              <select
                                                  :id="'employment-type-' + index"
                                                  v-model="experience.employment_type_id"
                                                  class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                  v-if="$page.props.employmentTypes && $page.props.employmentTypes.length > 0"
                                              >
                                                  <option value="">Select Employment Type</option>
                                                  <option
                                                      v-for="employmentType in $page.props.employmentTypes"
                                                      :key="employmentType.id"
                                                      :value="employmentType.id"
                                                  >
                                                      {{ employmentType.name }}
                                                  </option>
                                              </select>
                                              <Input
                                                v-else
                                                :id="'employment-type-' + index"
                                                v-model="experience.employment_type"
                                                placeholder="Full-time, Part-time, etc."
                                              />
                                          </div>

                                          <div class="space-y-2">
                                              <Label :for="'exp-start-' + index">Start Date</Label>
                                              <Input :id="'exp-start-' + index" v-model="experience.start_date" placeholder="MM/YYYY" />
                                          </div>

                                          <div class="space-y-2">
                                              <Label :for="'exp-end-' + index">End Date</Label>
                                              <Input :id="'exp-end-' + index" v-model="experience.end_date" placeholder="MM/YYYY" :disabled="experience.currently_working" />
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
                                              <Input :id="'issue-date-' + index" v-model="license.issue_date" placeholder="MM/YYYY" />
                                          </div>

                                          <div class="space-y-2">
                                              <Label :for="'exp-date-' + index">Expiration Date</Label>
                                              <Input :id="'exp-date-' + index" v-model="license.expiration_date" placeholder="MM/YYYY" />
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
                                              <Input :id="'project-start-' + index" v-model="project.start_date" placeholder="MM/YYYY" />
                                          </div>

                                          <div class="space-y-2">
                                              <Label :for="'project-end-' + index">End Date</Label>
                                              <Input :id="'project-end-' + index" v-model="project.end_date" placeholder="MM/YYYY" />
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

                          <template #footer>
                            <Button type="button" variant="outline" @click="isDialogOpen = false">
                                Cancel
                            </Button>
                            <Button type="button" @click="submitForm">
                                <Sparkles class="mr-2 h-4 w-4" />
                                Create AI-Powered Resume
                            </Button>
                          </template>
                </FullscreenDialog>

                <!-- File Upload Dialog -->
                <FileUploadDialog
                  :open="isUploadDialogOpen"
                  @update:open="isUploadDialogOpen = $event"
                  @file-selected="handleFileSelected"
                />
            </div>
        </div>
    </AppLayout>
</template>
