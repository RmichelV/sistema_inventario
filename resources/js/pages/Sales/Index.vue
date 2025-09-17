<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Sale } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Historial de ventas',
        href: '/rsales',
    },
];

const props = defineProps<{
    sales: Sale[]
}>();

console.log(props.sales)
</script>

<template>
    <Head title="Empleados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
            
            <ProductTable
                :cadena="sales??[]"
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
                    }
                ]"
            />
            </div>
        </div>
    </AppLayout>
</template>
