export interface Role {
    id: number;
    name: string;
    description?: string | null;
}

export interface UserItem {
    id: number;
    name: string;
    email: string;
    role_id?: number | null;
    role?: Role | null;
    email_verified_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface UserFilters {
    search?: string;
    role?: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface UserListProps {
    users: PaginatedData<UserItem>;
    roles: Role[];
    filters: UserFilters;
}

export interface UserForm {
    name: string;
    email: string;
    role: string;
    password?: string;
}
