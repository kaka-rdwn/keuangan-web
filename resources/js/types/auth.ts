export interface User {
    id: number;
    name: string;
    email: string;
    role?: string | null;
    permissions?: string[];
    avatar?: string;
    email_verified_at?: string | null;
    two_factor_enabled?: boolean;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
}

export interface Auth {
    user: User | null;
}

export interface PageProps {
    auth: Auth;
    name?: string;
    sidebarOpen?: boolean;
    [key: string]: unknown;
}

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
