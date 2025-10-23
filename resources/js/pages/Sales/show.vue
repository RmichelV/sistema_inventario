<script setup lang="ts">
import { defineProps, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Sale, type Product, type SaleItem } from '@/types';
import { Table as ItemsTable } from '@/components/ui/Table';
// UI Components
// ActionButton import removed (unused)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Detalle de la venta',
        href: "/rsale"
    },
];

const { sale, products, sale_items } = defineProps<{
    sale: Sale;
    products: Product[]; // Corregido: 'products' es un array
    sale_items: SaleItem[];
}>();

// Función para encontrar el producto asociado a un ítem de venta
const findProduct = (productId: number) => {
    return products.find(p => p.id === productId);
};

// Propiedad computada para procesar los ítems de venta para la tabla
const tableItems = computed(() => {
    return sale_items.map(item => {
        const product = findProduct(item.product_id);
        const unitPrice = product?.product_store?.unit_price || 0;
        const subtotal = item.quantity_products * unitPrice;

        return {
            ...item,
            product_name: product?.code || 'N/A', // Agrega el nombre del producto
            unit_price: unitPrice.toFixed(2), // Agrega el precio unitario
            subtotal: subtotal.toFixed(2), // Agrega el subtotal
        };
    });
});

// totalItemsPrice removed (not used)

</script>

<template>
    <Head title="Detalle de Venta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-lg mb-2">Cliente</h3>
                        <p class="text-xl text-gray-700 dark:text-gray-300">{{ sale.customer_name }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-lg mb-2">Fecha de Venta</h3>
                        <p class="text-xl text-gray-700 dark:text-gray-300">{{ sale.sale_date }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-lg mb-2">Código de Venta</h3>
                        <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ sale.sale_code }}</p>
                    </div>
                </div>

                <h3 class="font-bold text-2xl mt-8 mb-4">Productos de la Venta</h3>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <ItemsTable
                        :cadena="tableItems"
                        :cabeceras="['Producto','Cantidad','Precio $us','T/C en ese momento','Acciones']"
                        :campos="['product_name','quantity_products','total_price','exchange_rate']"
                        :agregar="false"
                        :acciones="[
                            {
                                href: (item) => route('rsaleitems.edit' , item.id),
                                color: 'red',
                                name: 'Devolver',
                                iconName: 'bx-pencil',
                            }
                        ]"
                    />
                </div>

                <div class="mt-8 p-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-right">
                    <!-- <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Total de Productos: <span class="text-xl font-bold">${{ totalItemsPrice }}</span>
                    </p> -->
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Precio Final Cancelado: <span class="text-xl font-bold text-green-600 dark:text-green-400">${{ sale.final_price }}</span>
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>