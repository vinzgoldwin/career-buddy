import { toast } from '@/components/ui/toast';

const DEFAULT_DURATION = 5000; // 5 seconds

export function notifySuccess(description: string, title = 'Success') {
    const { dismiss } = toast({ title, description, variant: 'success' });
    setTimeout(dismiss, DEFAULT_DURATION);
}

export function notifyError(description: string, title = 'Error') {
    const { dismiss } = toast({ title, description, variant: 'destructive' });
    setTimeout(dismiss, DEFAULT_DURATION);
}

export function notifyInfo(description: string, title = 'Notice') {
    const { dismiss } = toast({ title, description });
    setTimeout(dismiss, DEFAULT_DURATION);
}

