import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

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
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;

};

export interface User {
    id: number;
    name: string;
    address: string;
    phone: string;
    role_id: number;
    base_salary: number;
    hire_date: Date;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    asistencia_registrada?: boolean;
    role?: Role;
}

export interface Attendace_records {
    id: number;
    user_id: number;
    attendance_status: string;
    attendance_date: string;
    check_in_at: string | null;
    check_out_at: string | null;
    late_minutes: number | null;
    created_at: string;
    updated_at: string;
    user?: User;
}

export interface Role {
    id: number;
    name: string;
}


export interface SalaryAdjustment {
    id: number;
    user_id: number;
    salary_adjustment_type: number;
    amount: number;
    effective_date: string;
    reason: string;
    created_at: string;
    updated_at: string;
    user?: User;
    AdjustmentType?: AdjustmentType;
}

export interface Product{
    id: number;
    name: string;
    code: string;
    img_product: string;
    quantity_in_stock: number;
    units_per_box:number;
    minimum_wholesale_quantity: number;
    currency_type: string;
    unit_price_wholesale: number;
    unit_price_retail: number;
}

export interface Purchase{
    id: number;
    product_id: number;
    purchase_quantity: number;
    purchase_date: string;
    total_paid: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
