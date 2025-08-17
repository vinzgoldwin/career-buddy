<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { X, Upload as UploadIcon, FileText, AlertCircle } from 'lucide-vue-next'
import { ref, computed } from 'vue'

interface Props {
  open: boolean
}

interface Emits {
  (e: 'update:open', open: boolean): void
  (e: 'file-selected', file: File): void
}

const props = withDefaults(defineProps<Props>(), {
  open: false
})

const emit = defineEmits<Emits>()

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isDragging = ref(false)
const errorMessage = ref('')

// Handle file selection
const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    validateAndSetFile(target.files[0])
  }
}

// Handle drag and drop events
const handleDragEnter = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = true
}

const handleDragLeave = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = false
}

const handleDragOver = (e: DragEvent) => {
  e.preventDefault()
}

const handleDrop = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = false

  if (e.dataTransfer && e.dataTransfer.files.length > 0) {
    validateAndSetFile(e.dataTransfer.files[0])
  }
}

// Validate and set the selected file
const validateAndSetFile = (file: File) => {
  // Reset error message
  errorMessage.value = ''

  // Check if file is a PDF
  if (file.type !== 'application/pdf') {
    errorMessage.value = 'Please upload a PDF file.'
    return
  }

  // Check file size (max 10MB)
  if (file.size > 10 * 1024 * 1024) {
    errorMessage.value = 'File size must be less than 10MB.'
    return
  }

  selectedFile.value = file
}

// Handle upload
const handleUpload = () => {
  if (selectedFile.value) {
    emit('file-selected', selectedFile.value)
  }
}

// File name computed property
const fileName = computed(() => {
  return selectedFile.value ? selectedFile.value.name : ''
})

// File size computed property
const fileSize = computed(() => {
  if (!selectedFile.value) return ''

  const sizeInMB = selectedFile.value.size / (1024 * 1024)
  return sizeInMB.toFixed(2) + ' MB'
})

// Clear selected file
const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Close dialog
const closeDialog = () => {
  emit('update:open', false)
}

// Trigger file input click
const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="closeDialog">
    <DialogContent class="max-w-md md:max-w-lg">
      <DialogHeader>
        <DialogTitle>Upload Resume</DialogTitle>
        <DialogDescription>
          Upload your PDF resume or CV to get started
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-6">
        <div
          class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors"
          :class="{
            'border-primary bg-primary/5': isDragging,
            'border-muted-foreground hover:border-foreground': !isDragging
          }"
          @click="triggerFileInput"
          @dragenter="handleDragEnter"
          @dragleave="handleDragLeave"
          @dragover="handleDragOver"
          @drop="handleDrop"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".pdf,application/pdf"
            class="hidden"
            @change="handleFileChange"
          />

          <div class="flex flex-col items-center justify-center gap-4">
            <UploadIcon class="h-10 w-10 text-muted-foreground" />
            <div>
              <p class="font-medium">Drag and drop your PDF here</p>
              <p class="text-sm text-muted-foreground mt-1">or click to browse files</p>
            </div>
            <Button variant="outline" size="sm">
              <FileText class="h-4 w-4 mr-2" />
              Select PDF
            </Button>
            <p class="text-xs text-muted-foreground mt-2">PDF files only, up to 10MB</p>
          </div>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="p-3 bg-destructive/10 text-destructive rounded-md flex items-start gap-2">
          <AlertCircle class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <p class="text-sm">{{ errorMessage }}</p>
        </div>

        <!-- Selected file preview -->
        <div v-if="selectedFile" class="p-4 border rounded-lg bg-muted/50">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <FileText class="h-8 w-8 text-muted-foreground" />
              <div>
                <p class="font-medium truncate max-w-xs">{{ fileName }}</p>
                <p class="text-sm text-muted-foreground">{{ fileSize }}</p>
              </div>
            </div>
            <Button variant="ghost" size="icon" @click="clearFile">
              <X class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <Button variant="outline" @click="closeDialog">
          Cancel
        </Button>
        <Button
          :disabled="!selectedFile"
          @click="handleUpload"
          class="flex items-center gap-2"
        >
          <UploadIcon class="h-4 w-4" />
          Upload Resume
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
