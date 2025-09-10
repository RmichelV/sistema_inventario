<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Product, ProductStore, Purchase } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Historial de ventas',
        href: '/rsales',
    },
];

const props = defineProps<{
    products: Product[];
    productstores: ProductStore[]
    
}>();
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
                :cadena="productstores??[]"
                :cabeceras="[
                    'Id',
                    'Producto',
                    'Cantidad',
                    'P/U POR MAYOR',
                    'P/U POR MENOR',
                    'PRECIO DE VENTA',
                    'Acciones'
                ]"
                :campos="[
                        'id',
                        'product_id',
                        'quantity',
                        'unit_price_wholesale',
                        'unit_price_retail',
                        'saleprice',
                        ]"
                :agregar="{
                    href: route('rsales.create'), 
                    color: 'green', 
                    name: 'registrar venta',
                    iconName: 'bx-plus' }"
                 :acciones="[
                    {
                        href: (item) => route('rproductstores.edit' , item.id),
                        color: 'blue',
                        name: 'Editar',
                        iconName: 'bx-pencil',
                    }
                ]"
            />
            </div>
        </div>
    </AppLayout>
</template>
