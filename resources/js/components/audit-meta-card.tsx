import { Clock, User as UserIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/utils';

interface UserRef {
    name?: string | null;
}

interface AuditMetaCardProps {
    createdBy?: UserRef | string | null;
    createdAt?: string | null;
    updatedBy?: UserRef | string | null;
    updatedAt?: string | null;
    showUsers?: boolean;
    className?: string;
}

export function AuditMetaCard({
    createdBy,
    createdAt,
    updatedBy,
    updatedAt,
    showUsers = true,
    className = '',
}: AuditMetaCardProps) {
    const creatorName =
        typeof createdBy === 'object' ? createdBy?.name : createdBy;
    const updaterName =
        typeof updatedBy === 'object' ? updatedBy?.name : updatedBy;

    return (
        <Card className={`border-sidebar-border/70 bg-muted/20 dark:bg-muted/10 ${className}`}>
            <CardHeader className="py-3 px-4">
                <CardTitle className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <Clock className="h-3.5 w-3.5 text-primary" />
                    Informasi Audit / Metadata
                </CardTitle>
            </CardHeader>
            <CardContent className="px-4 pb-4 pt-0 text-xs text-muted-foreground">
                <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    {showUsers && (
                        <div className="flex items-center gap-2">
                            <UserIcon className="h-3.5 w-3.5 text-muted-foreground/70" />
                            <span>Dibuat oleh:</span>
                            <span className="font-medium text-foreground">
                                {creatorName || '-'}
                            </span>
                        </div>
                    )}
                    <div className="flex items-center gap-2">
                        <Clock className="h-3.5 w-3.5 text-muted-foreground/70" />
                        <span>Dibuat pada:</span>
                        <span className="font-medium text-foreground">
                            {formatDate(createdAt, true)}
                        </span>
                    </div>
                    {showUsers && (
                        <div className="flex items-center gap-2">
                            <UserIcon className="h-3.5 w-3.5 text-muted-foreground/70" />
                            <span>Diperbarui oleh:</span>
                            <span className="font-medium text-foreground">
                                {updaterName || '-'}
                            </span>
                        </div>
                    )}
                    <div className="flex items-center gap-2">
                        <Clock className="h-3.5 w-3.5 text-muted-foreground/70" />
                        <span>Diperbarui pada:</span>
                        <span className="font-medium text-foreground">
                            {formatDate(updatedAt, true)}
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
