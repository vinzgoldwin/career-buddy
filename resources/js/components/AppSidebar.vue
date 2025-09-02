<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { CreditCard, FileText, LayoutGrid, MessageCircle, Notebook, Send, Sparkles, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const serviceNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/home',
        icon: LayoutGrid,
    },
    {
        title: 'Resume Builder',
        href: '/ai-resume-builder',
        icon: FileText,
    },
    {
        title: 'Resume Evaluation',
        href: '/ai-evaluations',
        icon: Sparkles,
    },
    // {
    //     title: 'Mock Interview',
    //     href: '/mock-interview',
    //     icon: MessageCircle,
    // },
    {
        title: 'Interview Question Bank',
        href: '/interview-question-bank',
        icon: Notebook,
    },
    {
        title: 'Easy Apply',
        href: '/ai-auto-apply',
        icon: Send,
    },
];

const accountNavItems: NavItem[] = [
    {
        title: 'Billing',
        href: '/billing',
        icon: CreditCard,
    },
    {
        title: 'Affiliate',
        href: '/affiliate',
        icon: Users,
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
                        <Link :href="route('home')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="serviceNavItems" title="Services" />
            <NavMain :items="accountNavItems" title="Account" />
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
