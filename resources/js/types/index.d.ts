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
    roles?: number[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    flash: {
        success?: string;
        error?: string;
    };

};

export interface User {
    id: number;
    name: string;
    address: string;
    phone: string;
    role_id: number;
    branch_id?: number;
    hire_date: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;

    // --- Relaciones ---
    role: Role; 
    branch?: Branch;
    
    // --- Campos Base (Formateados por el Controlador) ---
    // base_salary llega formateado como string (ej: "5000.00")
    base_salary: string; 

    // --- Campos Calculados para la Nómina ---
    
    // Total de ajustes (Desc/Bon) formateado (ej: "150.00" o "-50.00")
    total_adjustment: string; 
    
    // Costo por minuto (con alta precisión, ej: "0.3472")
    cost_per_minute: string; 
    
    // Salario ganado solo por el tiempo trabajado (Salario Neto)
    salario_devengado: string; 
    
    // Salario final a pagar (Salario Devengado + Ajuste)
    final_salary: string; 
    
    // Tiempo trabajado total (el valor numérico entero bruto)
    total_minutes_worked: number; 
    // Tiempo trabajado total en formato legible (ej: "40:30")
    total_time_formatted: string; 
    
    asistencia_registrada?: boolean;
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

export interface Branch {
    id: number;
    name: string;
    address: string;
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
    // Precio por sucursal (nullable/optional) agregado para soportar product_branches.unit_price
    unit_price?: number;
    unit_price_wholesale: number;
    unit_price_retail: number;
    product_store?: ProductStore; 
    last_update?: Date;
}

export interface Purchase{
    id: number;
    product_id: number;
    purchase_quantity: number;
    purchase_date: string;
    total_paid: number;
}

export interface Usd_exchange_rate{
    id: number;
    exchange_rate: number;
}

export interface ProductStore{
    id: number;
    product_id:number;
    quantity:number
    unit_price:number;
    last_update:Date;
}
export interface Sale{
    id: number;
    sale_code: string;
    customer_name: string;
    sale_date: Date;
    initial_price: number;
    final_price: number;
}
export interface SaleItem{
    id: number;
    sale_id: number;
    product_id: number;
    quantity_products: number;
    total_price: number;
    exchange_rate: number;
}

export interface Devolution{
    product_id:number;
    quantity:number;
    reason:string;
    refund_amount:number;
    sale_item_id:number;
}

export interface Salary{
    user_id:number;
    base_salary:number;
    salary_adjustment:number;
    discounts:number;
    total_salary:number;
    paydate:Date;
    user_id_m:number;
}

export type BreadcrumbItemType = BreadcrumbItem;
