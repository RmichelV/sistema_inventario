
<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Sale } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
// PlaceholderPattern removed (unused)
import { Table as ProductTable } from '@/components/ui/Table';
import { generarNotaVentaPDF } from '@/utils/notaVentaPDF';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Historial de ventas',
        href: '/rsales',
    },
];

type SaleWithPayType = Sale & { pay_type?: string };
const { sales, branches, currentBranch, currentUser } = defineProps<{
    sales: SaleWithPayType[],
    branches: { id: number; name: string }[],
    currentBranch: { id: number; name: string } | null,
    currentUser: { id: number; role_id: number }
}>();

// Formulario para cambiar la sucursal del usuario (solo visible para admin)
const form = useForm({ branch_id: '' });
const switchBranch = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const branchId = select.value;

    if (!branchId) return;

    form.branch_id = branchId;
    form.post(route('rusers.switchBranch'), {
        onSuccess: () => {
            window.location.reload();
        },
        onError: (errors: any) => {
            console.error('Failed to switch branch', errors);
        }
    });
};

// Estado para el filtro de fechas
const fechaInicio = ref('');
const fechaFin = ref('');

// Computed para filtrar ventas por fecha
const ventasFiltradas = computed(() => {
    if (!fechaInicio.value && !fechaFin.value) return sales;
    return sales.filter(sale => {
        const fecha = new Date(sale.sale_date as any);
        const desde = fechaInicio.value ? new Date(fechaInicio.value) : null;
        const hasta = fechaFin.value ? new Date(fechaFin.value) : null;
        if (desde && fecha < desde) return false;
        if (hasta && fecha > hasta) return false;
        return true;
    });
});

// Suma de dólares (final_price de ventas filtradas con pay_type == 'Dólares' o similar)
const sumaDolares = computed(() => {
    return ventasFiltradas.value
        .filter(sale => sale.pay_type && sale.pay_type.toLowerCase().includes('dolar'))
        .reduce((acc, sale) => acc + (Number(sale.final_price) || 0), 0)
        .toFixed(2);
});


// Suma de bolivianos (solo pay_type == 'Bolivianos')
const sumaBs = computed(() => {
    return ventasFiltradas.value
        .filter(sale => sale.pay_type && sale.pay_type.toLowerCase().includes('boliv'))
        .reduce((acc, sale) => acc + (Number(sale.final_price) || 0), 0)
        .toFixed(2);
});

// Suma de QR (solo pay_type == 'QR')
const sumaQr = computed(() => {
    return ventasFiltradas.value
        .filter(sale => sale.pay_type && sale.pay_type.toLowerCase().includes('qr'))
        .reduce((acc, sale) => acc + (Number(sale.final_price) || 0), 0)
        .toFixed(2);
});


// Función para obtener los productos de la venta desde el backend
async function fetchProductosVenta(saleId: number) {
    // Llama a la ruta show de ventas y obtiene los productos y cantidades
    // Se asume que la ruta 'rsales.show' retorna { sale, products, sale_items }
    return new Promise<{cliente: string, codigo:string, fecha: string, productos: {nombre: string, cantidad: number}[], precioFinal: number}>(resolve => {
        router.visit(route('rsales.show', saleId), {
            only: ['sale', 'products', 'sale_items'],
            preserveState: true,
            onSuccess: (page: any) => {
                const sale = page.props.sale as { customer_name: string, sale_code: string, sale_date: string, final_price: number };
                const products = page.props.products as Array<{ id: number, name: string }>;
                const sale_items = page.props.sale_items as Array<{ product_id: number, quantity_products: number }>;
                const productos = sale_items.map((item) => {
                    const prod = products.find((p) => p.id === item.product_id);
                    return {
                        nombre: prod ? prod.name : 'N/A',
                        cantidad: item.quantity_products
                    };
                });
                resolve({
                    cliente: sale.customer_name,
                    codigo: sale.sale_code,
                    fecha: sale.sale_date,
                    productos,
                    precioFinal: sale.final_price
                });
            }
        });
    });
}

async function handleDescargarNotaVenta(sale: Sale) {
    // Obtener productos y cantidades de la venta
    const datos = await fetchProductosVenta(sale.id);
    generarNotaVentaPDF({
        empresa: 'EWTTO ELECTRONICS',
        cliente: datos.cliente,
        codigo: datos.codigo,
        fecha: datos.fecha,
        productos: datos.productos,
        precioFinal: datos.precioFinal
    });
}
</script>

<template>
    <Head title="Empleados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

            <div class="flex items-center justify-between mb-2">
                <div class="text-sm">
                    Sucursal: <span class="font-bold">{{ currentBranch ? currentBranch.name : 'Sin sucursal asignada' }}</span>
                </div>
                <div>
                    <label v-if="currentUser && currentUser.role_id === 1" class="mr-2 font-medium">Cambiar sucursal:</label>
                    <select v-if="currentUser && currentUser.role_id === 1" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                        <option value="">-- Seleccionar --</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>

            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <!-- Filtro de fechas -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center gap-2 p-4">
                    <label class="font-semibold">Filtrar por fecha</label>
                    <div class="flex flex-col gap-2 w-full max-w-xs">
                        <input type="date" v-model="fechaInicio" class="border rounded px-2 py-1" placeholder="Fecha desde" />
                        <input type="date" v-model="fechaFin" class="border rounded px-2 py-1" placeholder="Fecha hasta" />
                    </div>
                </div>
                <!-- Suma de dólares -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center">
                    <span class="text-lg font-semibold">Total Dólares</span>
                    <span class="text-2xl text-green-700 dark:text-green-400 font-bold mt-2">$us {{ sumaDolares }}</span>
                </div>
                <!-- Suma de bolivianos -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center">
                    <span class="text-lg font-semibold">Total Bolivianos</span>
                    <span class="text-2xl text-blue-700 dark:text-blue-400 font-bold mt-2">Bs {{ sumaBs }}</span>
                </div>
                <!-- Suma de QR -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center">
                    <span class="text-lg font-semibold">Total QR</span>
                    <span class="text-2xl text-purple-700 dark:text-purple-400 font-bold mt-2">Bs {{ sumaQr }}</span>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
            
            <ProductTable
                :cadena="ventasFiltradas"
                :cabeceras="[
                    'CODIGO',
                    'CLIENTE',
                    'FECHA',
                    'TIPO DE PAGO',
                    'PRECIO CANCELADO',
                    'Acciones'
                ]"
                :campos="[
                        'sale_code',
                        'customer_name',
                        'sale_date',
                        'pay_type',
                        'final_price',
                        ]"
                :agregar="{
                    href: route('rsales.create'), 
                    color: 'green', 
                    name: 'registrar venta',
                    iconName: 'bx-plus' }"
                 :acciones="[
                    {
                        href: (item) => route('rsales.show' , item.id),
                        color: 'blue',
                        name: 'Detalle',
                        iconName: 'bx-list-ul',
                    },
                    {
                        onClick: handleDescargarNotaVenta,
                        color: 'purple',
                        name: 'Recibo',
                        iconName: 'bx-download'
                    }
                ]"
            />
            </div>
        </div>
    </AppLayout>
</template>
