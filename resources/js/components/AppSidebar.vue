<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { FileText, LayoutGrid, MessageCircle, Notebook, Send } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'AI Resume Builder',
        href: '/ai-resume-builder',
        icon: FileText,
    },
    {
        title: 'Mock Interview',
        href: '/mock-interview',
        icon: MessageCircle,
    },
    {
        title: 'Interview Question Bank',
        href: '/interview-question-bank',
        icon: Notebook,
    },
    {
        title: 'AI Auto Apply',
        href: '/ai-auto-apply',
        icon: Send,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter v-if="footerNavItems.length > 0">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
        <SidebarFooter v-else>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
