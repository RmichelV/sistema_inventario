<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Product } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de productos',
        href: '/rproducts',
    },
];

const props = defineProps<{
    products: Product[];
    
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
                :cadena="products??[]"
                :cabeceras="[
                    'Nombre',
                    'Codigo',
                    'imagen del producto',
                    'Cantidad en Stock',
                    'Cantidad de cajas',
                    'unidades por caja',
                    'Cantidad Minima PM+',
                    'precio unitario al por mayor', 
                    'precion unitario al por menor']"
                :campos="[
                        'name',
                        'code',
                        {key:'img_product' , type:'image' },
                        'quantity_in_stock',
                        'boxes',
                        'units_per_box',
                        'minimum_wholesale_quantity',
                        'unit_price_wholesale',
                        'unit_price_retail',
                        ]"
                :agregar="false"
                :acciones="[
                ]"
                :searchSelectConfig="{
                options: products, // Puedes pasar la misma cadena u otra lista de objetos
                valueKey: 'id',
                labelKey: 'code',
                placeholder: 'Buscar productos...'
                }"
            />
            </div>
        </div>
    </AppLayout>
</template>
