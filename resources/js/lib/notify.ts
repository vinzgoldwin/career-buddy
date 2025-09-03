import { useToastManager } from '@/composables/useToastManager';

const DEFAULT_DURATION = 5000; // 5 seconds

export function notifySuccess(description: string, title = 'Success'): void {
    const { success } = useToastManager();
    success(title, description, DEFAULT_DURATION);
}

export function notifyError(description: string, title = 'Error'): void {
    const { error } = useToastManager();
    error(title, description, DEFAULT_DURATION);
}

export function notifyInfo(description: string, title = 'Notice'): void {
    const { info } = useToastManager();
    info(title, description, DEFAULT_DURATION);
}
