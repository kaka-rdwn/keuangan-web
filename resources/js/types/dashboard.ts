import type { Cashflow } from './cashflow';

export interface DashboardMetrics {
    total_inflow: number;
    total_outflow: number;
    net_balance: number;
    inflow_growth: number;
    outflow_growth: number;
    top_expense_category: {
        name: string;
        amount: number;
    } | null;
}

export interface MonthlyTrendData {
    month_year: string;
    label: string;
    inflow: number;
    outflow: number;
}

export interface CategoryDistributionData {
    name: string;
    amount: number;
    percentage: number;
    color: string;
}

export interface DashboardProps {
    metrics: DashboardMetrics;
    monthly_trend: MonthlyTrendData[];
    chart_data: MonthlyTrendData[];
    category_distribution: CategoryDistributionData[];
    recent_transactions: Cashflow[];
    available_years: number[];
    selected_year: number;
    selected_period: 'monthly' | 'quarterly';
    filters: {
        month: number;
        year: number;
    };
}
