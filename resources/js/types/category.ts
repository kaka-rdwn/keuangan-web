export type CashflowType = 'inflow' | 'outflow';

export interface Category {
    id: number;
    name: string;
    type: CashflowType;
    description: string | null;
    created_by?: number | null;
    updated_by?: number | null;
    created_at?: string;
    updated_at?: string;
}

export interface CategoryForm {
    name: string;
    type: CashflowType;
    description: string;
}

export interface PaginatedCategories {
    data: Category[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}
