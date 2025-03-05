import type { LucideIcon } from 'lucide-vue-next';
import { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon: LucideIcon;
    description?: string;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export type UserRole = 'landlord' | 'tenant-admin' | 'teacher';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    tenant_id?: number;
}

export interface PageProps extends InertiaPageProps {
    auth: {
        user: User;
    };
    tenant?: Tenant;
    [key: string]: any;
}

export interface Plan {
    id: number;
    name: string;
    description: string | null;
    price: number;
    billing_period: 'monthly' | 'yearly';
    trial_period_days: number | null;
    features: string[];
    is_active: boolean;
    sort_order: number;
    subscriptions_count: number;
}

export interface Feature {
    id: string;
    text: string;
    order: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
