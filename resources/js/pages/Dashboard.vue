<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';

// Importa los tipos Product y ProductStore
import { type Product, type ProductStore, type Usd_exchange_rate } from '@/types'; 
import { SelectSearch } from '@/components/ui/SelectSearch';
import { Input } from '@/components/ui/input';
import { ActionButton } from '@/components/ui/ActionButton';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    usd: Usd_exchange_rate;
    products: Product[];
    productStores: ProductStore[]; // 1. Recibe los productos de la tienda
}>();

const selectedProductId = ref<number | null>(null);

// 2. Propiedad computada para encontrar y combinar los datos del producto
const currentProduct = computed(() => {
    if (selectedProductId.value === null) {
        return null;
    }
    
    // Busca el producto en bodega (tabla 'products')
    const product = props.products.find(p => p.id === selectedProductId.value);
    
    // Busca el producto en la tienda (tabla 'product_stores')
    const productInStore = props.productStores.find(p => p.product_id === selectedProductId.value);
    
    // Si no hay producto en bodega, no hay nada que mostrar.
    if (!product) {
        return null;
    }

    // Combina los datos de ambas tablas en un solo objeto.
    return {
        ...product,
        quantity_in_store: productInStore ? productInStore.quantity : 0,
        unit_price_wholesale: productInStore ? productInStore.unit_price_wholesale : 0,
        unit_price_retail: productInStore ? productInStore.unit_price_retail : 0,
        saleprice: productInStore ? productInStore.saleprice : 0,
    };
});

// 3. Función para convertir precios a Bolivianos (Bs)
const toBs = (price: number | undefined | null) => {
    if (price === undefined || price === null || !props.usd || !props.usd.exchange_rate) {
        return 'N/A';
    }
    return (price * props.usd.exchange_rate).toFixed(2);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                        <h1 class="text-2xl font-bold mb-4">Buscar Producto</h1>
                       <SelectSearch
                            v-model="selectedProductId"
                            :options="props.products"
                            :searchKeys="['name', 'code']"
                            placeholder="Buscar un producto por nombre o código..."
                            labelKey="code"
                        />
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h1 class="text-xl font-bold">Detalle del Producto</h1>
                        <div v-if="currentProduct">
                            <p><strong>Nombre:</strong> {{ currentProduct.name }}</p>
                            <p><strong>Código:</strong> {{ currentProduct.code }}</p>
                            <p><strong>Cantidad en Bodega:</strong> {{ currentProduct.quantity_in_stock }}</p>
                            <p><strong>Cantidad en Tienda:</strong> {{ currentProduct.quantity_in_store }}</p>
                            <p><strong>P/U CAJA:</strong> ${{ currentProduct.unit_price_wholesale }} (Bs {{ toBs(currentProduct.unit_price_wholesale) }})</p>
                            <p><strong>P/U MAYOR:</strong> ${{ currentProduct.unit_price_retail }} (Bs {{ toBs(currentProduct.unit_price_retail) }})</p>
                            <p><strong>P/U MENOR:</strong> ${{ currentProduct.saleprice }} (Bs {{ toBs(currentProduct.saleprice) }})</p>
                        </div>
                        <div v-else>
                            <p class="text-gray-500 mt-2">No hay producto seleccionado.</p>
                        </div>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                       <p class="mt-2 text-xl">
                            Cambio Actual 
                       </p>
                        <h1 class="text-5xl font-bold">
                            Bs. {{props.usd.exchange_rate}}
                        </h1>
                        <div>
                            <ActionButton
                                color="green"
                                iconName="bx-pencil"
                                :href="route('rusdexchangerates.edit',1)"
                                name="Editar"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
