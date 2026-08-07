import type { CashflowType, Category } from './category';

export interface Cashflow {
    id: number;
    name: string;
    amount: number;
    type: CashflowType;
    category_id: number | null;
    category?: Category | null;
    transaction_date: string;
    description: string | null;
    created_by?: number | null;
    updated_by?: number | null;
    created_at?: string;
    updated_at?: string;
}

export interface CashflowForm {
    name: string;
    type: CashflowType;
    category_id: string | number;
    amount: string | number;
    transaction_date: string;
    description: string;
}

export interface CashflowSummary {
    total_inflow: number;
    total_outflow: number;
    net_balance: number;
}

export interface PaginatedCashflows {
    data: Cashflow[];
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
