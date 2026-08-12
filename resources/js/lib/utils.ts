import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function formatPaginationLabel(label: string): string {
    return label
        .replace(/pagination\.previous/gi, '&laquo; Sebelumnya')
        .replace(/pagination\.next/gi, 'Selanjutnya &raquo;')
        .replace(/&laquo;\s*Previous/gi, '&laquo; Sebelumnya')
        .replace(/Next\s*&raquo;/gi, 'Selanjutnya &raquo;');
}

export function formatDate(dateString?: string | null, includeTime: boolean = false): string {
    if (!dateString) {
        return '-';
    }

    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
        return dateString;
    }

    if (includeTime) {
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(date);
}
