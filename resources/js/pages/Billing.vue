<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CreditCard, Plus, Wallet } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    currentBalance: number;
    transactions: Array<{
        id: number;
        date: string;
        type: 'credit' | 'debit';
        amount: number;
        description: string;
        status: 'completed' | 'pending' | 'failed';
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    currentBalance: 0,
    transactions: () => [],
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Billing',
        href: '/billing',
    },
];

const amount = ref('');
const isDialogOpen = ref(false);

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const setAmount = (value: number): void => {
    amount.value = value.toString();
};

const handleTopUp = (): void => {
    console.log('Top up amount:', amount.value);
    isDialogOpen.value = false;
    amount.value = '';
};

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'completed':
            return 'text-green-600';
        case 'pending':
            return 'text-yellow-600';
        case 'failed':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
};

const getTypeColor = (type: string): string => {
    return type === 'credit' ? 'text-green-600' : 'text-red-600';
};

const getTypePrefix = (type: string): string => {
    return type === 'credit' ? '+' : '-';
};
</script>

<template>
    <Head title="Billing" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold">Billing</h1>
                <p class="text-muted-foreground">Manage your balance and view transaction history</p>
            </div>

            <!-- Add Credit Button -->
            <div class="flex justify-start">
                <Dialog v-model:open="isDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add Credit
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Add Credit</DialogTitle>
                            <DialogDescription>
                                Add credits to your account to continue using our services.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="amount">Amount (Rp)</Label>
                                <Input
                                    id="amount"
                                    v-model="amount"
                                    placeholder="Enter amount"
                                    type="number"
                                />
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <Button
                                    variant="outline"
                                    @click="setAmount(10000)"
                                >
                                    Rp 10.000
                                </Button>
                                <Button
                                    variant="outline"
                                    @click="setAmount(25000)"
                                >
                                    Rp 25.000
                                </Button>
                                <Button
                                    variant="outline"
                                    @click="setAmount(50000)"
                                >
                                    Rp 50.000
                                </Button>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="handleTopUp">
                                <CreditCard class="mr-2 h-4 w-4" />
                                Top Up
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Current Credits Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Wallet class="h-5 w-5" />
                        Current Credits
                    </CardTitle>
                    <CardDescription>
                        Your available balance
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold">
                        {{ formatCurrency(currentBalance) }}
                    </div>
                </CardContent>
            </Card>

            <!-- Transactions Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Transaction History</CardTitle>
                    <CardDescription>
                        Your recent transactions and balance changes
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table v-if="transactions.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="transaction in transactions" :key="transaction.id">
                                <TableCell class="font-mono">
                                    {{ new Date(transaction.date).toLocaleDateString('id-ID') }}
                                </TableCell>
                                <TableCell>{{ transaction.description }}</TableCell>
                                <TableCell>
                                    <span class="capitalize" :class="getTypeColor(transaction.type)">
                                        {{ transaction.type }}
                                    </span>
                                </TableCell>
                                <TableCell :class="getTypeColor(transaction.type)">
                                    {{ getTypePrefix(transaction.type) }}{{ formatCurrency(transaction.amount) }}
                                </TableCell>
                                <TableCell>
                                    <span class="capitalize" :class="getStatusColor(transaction.status)">
                                        {{ transaction.status }}
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <Wallet class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No transactions found</p>
                        <p class="text-sm">Your transaction history will appear here</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>