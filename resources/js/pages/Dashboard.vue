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
import { Table as productsTable} from '@/components/ui/Table';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    usd: Usd_exchange_rate;
    products: Product[];
    productStores: ProductStore[];
    productStores15Days: ProductStore[];
    productStores30Days: ProductStore[];
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
        unit_price: productInStore ? productInStore.unit_price : 0,
        
        // unit_price_retail: productInStore ? productInStore.unit_price_retail : 0,
        // saleprice: productInStore ? productInStore.saleprice : 0,
    };
});

// 3. Función para convertir precios a Bolivianos (Bs)
const toBs = (price: number | undefined | null) => {
    if (price === undefined || price === null || !props.usd || !props.usd.exchange_rate) {
        return 'N/A';
    }
    return (price * props.usd.exchange_rate).toFixed(2);
};

// FUNCIÓN MODIFICADA: Calcula el precio en Bs y le suma el 1.1%
const toBsP = (price: number | undefined | null) => {
    if (price === undefined || price === null || !props.usd || !props.usd.exchange_rate) {
        return 'N/A';
    }
    // 1. Convertir a bolivianos
    const priceInBs = price * props.usd.exchange_rate;
    // 2. Sumar el 1.1% (multiplicar por 1.011)
    const priceWithProfit = priceInBs * 1.011;
    
    return priceWithProfit.toFixed(2);
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
                            <p><strong>P/U en Dolares:</strong> ${{ currentProduct.unit_price }} </p>
                            <p><strong>P/U en Bolivianos:</strong> Bs. {{ toBs(currentProduct.unit_price) }} </p>
                            
                            <p><strong>P/U sugerido:</strong>Bs {{ toBsP(currentProduct.unit_price) }}</p>
                            
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
                <h1 class="text-center mt-10">PRODUCTOS CON 30 SIN VENDER</h1>
                <productsTable
                :cadena="productStores30Days??[]"
                :cabeceras="[
                    'Id',
                    'Producto',
                    'Cantidad',
                    'P/U $us.',
                    'P/U Bs.',
                    'P/U + 1.1%',
                    'Acciones'
                ]"
                :campos="[
                        'id',
                        'product_id',
                        'quantity',
                        'unit_price',
                        'unit_price_bs',
                        'porcentaje',
                        
                        ]"
                :agregar="false"
                 :acciones="[
                    {
                        href: (item) => route('rproductstores.edit' , item.id),
                        color: 'blue',
                        name: 'Editar',
                        iconName: 'bx-pencil',
                    }
                 
                ]"
                headerBgColor="bg-[#D1340D]"
                headerTextColor="text-[#000000]"
                />

                <h1 class="text-center mt-10">PRODUCTOS CON 15 SIN VENDER</h1>
                <productsTable
                :cadena="productStores15Days??[]"
                :cabeceras="[
                    'Id',
                    'Producto',
                    'Cantidad',
                    'P/U $us.',
                    'P/U Bs.',
                    'P/U + 1.1%',
                    'Acciones'
                ]"
                :campos="[
                        'id',
                        'product_id',
                        'quantity',
                        'unit_price',
                        'unit_price_bs',
                        'porcentaje',
                        
                        ]"
                :agregar="false"
                 :acciones="[
                    {
                        href: (item) => route('rproductstores.edit' , item.id),
                        color: 'blue',
                        name: 'Editar',
                        iconName: 'bx-pencil',
                    }
                 
                ]"
                headerBgColor="bg-[#D1960D]"
                headerTextColor="text-[#000000]"
                />
            </div>
        </div>
    </AppLayout>
</template>
