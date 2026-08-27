import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

interface SortableHeaderProps extends ComponentProps<'th'> {
    column: string;
    label?: string;
    sortBy?: string;
    sortDir?: string;
    onSort: (column: string) => void;
}

export function SortableHeader({
    column,
    label,
    sortBy,
    sortDir,
    onSort,
    className,
    children,
    ...props
}: SortableHeaderProps) {
    const isActive = sortBy === column;
    const isAsc = isActive && sortDir === 'asc';

    return (
        <th
            {...props}
            onClick={() => onSort(column)}
            className={cn(
                'cursor-pointer transition-colors select-none hover:text-foreground',
                className,
            )}
        >
            <div className="inline-flex items-center gap-1.5 font-semibold">
                <span>{children || label}</span>
                {isActive ? (
                    isAsc ? (
                        <ArrowUp className="h-4 w-4 text-primary" />
                    ) : (
                        <ArrowDown className="h-4 w-4 text-primary" />
                    )
                ) : (
                    <ArrowUpDown className="h-3.5 w-3.5 text-muted-foreground/50" />
                )}
            </div>
        </th>
    );
}
