<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Check, Copy, DollarSign, Link2, Users, UserCheck, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    totalReferrals: number;
    registeredReferrals: number;
    transactedReferrals: number;
    totalEarnings: number;
    referralCode: string;
    referralLink: string;
}

const props = withDefaults(defineProps<Props>(), {
    totalReferrals: 0,
    registeredReferrals: 0,
    transactedReferrals: 0,
    totalEarnings: 0,
    referralCode: '',
    referralLink: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Affiliate',
        href: '/affiliate',
    },
];

const isCopied = ref(false);

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const copyToClipboard = async (): Promise<void> => {
    try {
        await navigator.clipboard.writeText(props.referralLink);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

const statsCards = [
    {
        title: 'Total Referrals',
        value: props.totalReferrals.toString(),
        description: 'People you\'ve invited',
        icon: Users,
        color: 'text-blue-600',
    },
    {
        title: 'Registered',
        value: props.registeredReferrals.toString(),
        description: 'Completed registration',
        icon: UserCheck,
        color: 'text-green-600',
    },
    {
        title: 'Transacted',
        value: props.transactedReferrals.toString(),
        description: 'Made their first topup',
        icon: TrendingUp,
        color: 'text-orange-600',
    },
    {
        title: 'Total Earnings',
        value: formatCurrency(props.totalEarnings),
        description: '10% commission earned',
        icon: DollarSign,
        color: 'text-emerald-600',
    },
];
</script>

<template>
    <Head title="Affiliate Program" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold">Affiliate Program</h1>
                <p class="text-muted-foreground">
                    Earn 10% commission on confirmed topup payments from your referrals
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card v-for="stat in statsCards" :key="stat.title">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ stat.title }}
                        </CardTitle>
                        <component :is="stat.icon" :class="`h-4 w-4 ${stat.color}`" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stat.description }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Referral Link Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Link2 class="h-5 w-5" />
                        Your Referral Link
                    </CardTitle>
                    <CardDescription>
                        Share this link with your friends and earn 10% commission on confirmed topup payments
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-2">
                        <Input
                            :value="referralLink"
                            readonly
                            class="flex-1 font-mono text-sm"
                            placeholder="Your referral link will appear here"
                        />
                        <Button
                            @click="copyToClipboard"
                            :disabled="!referralLink"
                            class="gap-2"
                        >
                            <component :is="isCopied ? Check : Copy" class="h-4 w-4" />
                            {{ isCopied ? 'Copied!' : 'Copy' }}
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground mt-2">
                        When someone registers using your link and makes their first topup, you'll earn 10% commission on that payment.
                    </p>
                </CardContent>
            </Card>

            <!-- How it Works Section -->
            <Card>
                <CardHeader>
                    <CardTitle>How It Works</CardTitle>
                    <CardDescription>
                        Follow these simple steps to start earning
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="flex flex-col items-center text-center gap-2">
                            <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <Users class="h-6 w-6 text-blue-600" />
                            </div>
                            <h3 class="font-semibold">1. Share Your Link</h3>
                            <p class="text-sm text-muted-foreground">
                                Share your unique referral link with friends and family
                            </p>
                        </div>
                        <div class="flex flex-col items-center text-center gap-2">
                            <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <UserCheck class="h-6 w-6 text-green-600" />
                            </div>
                            <h3 class="font-semibold">2. They Register</h3>
                            <p class="text-sm text-muted-foreground">
                                Your friends sign up using your referral link
                            </p>
                        </div>
                        <div class="flex flex-col items-center text-center gap-2">
                            <div class="h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                <DollarSign class="h-6 w-6 text-emerald-600" />
                            </div>
                            <h3 class="font-semibold">3. You Earn</h3>
                            <p class="text-sm text-muted-foreground">
                                Get 10% commission when they make their first topup
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>