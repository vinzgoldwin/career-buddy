import { toast } from '@/components/ui/toast';

export function notifySuccess(description: string, title = 'Success') {
    toast({ title, description, variant: 'success' });
}

export function notifyError(description: string, title = 'Error') {
    toast({ title, description, variant: 'destructive' });
}

export function notifyInfo(description: string, title = 'Notice') {
    toast({ title, description });
}

