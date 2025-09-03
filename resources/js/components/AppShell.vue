<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
// Removed old Toaster (shadcn) in favor of the new notifications system
import ToastContainer from '@/components/notifications/ToastContainer.vue';
import { useToastManager } from '@/composables/useToastManager';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const { notifications, position, close } = useToastManager();
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <ToastContainer :items="notifications" :position="position" @close="close" />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <ToastContainer :items="notifications" :position="position" @close="close" />
    </SidebarProvider>
</template>
