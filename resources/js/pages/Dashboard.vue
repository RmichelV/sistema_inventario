<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Importa los tipos Product y ProductStore
import { type Product, type ProductStore, type Usd_exchange_rate } from '@/types'; 
import { SelectSearch } from '@/components/ui/SelectSearch';
import { ActionButton } from '@/components/ui/ActionButton';
import { Table as productsTable} from '@/components/ui/Table';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const { usd, products, productStores, productStores15Days, productStores30Days, branches, currentBranch, currentUser } = defineProps<{
    usd: Usd_exchange_rate;
    products: Product[];
    productStores: ProductStore[];
    productStores15Days: ProductStore[];
    productStores30Days: ProductStore[];
    branches: { id: number; name: string }[];
    currentBranch: { id: number; name: string } | null;
    currentUser: { id: number; role_id: number };

}>();

const selectedProductId = ref<number | null>(null);

// Formulario para cambiar la sucursal del usuario (solo visible para admin)
const form = useForm({ branch_id: '' });
const switchBranch = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const branchId = select.value;

    if (!branchId) return;

    form.branch_id = branchId;
    form.post(route('rusers.switchBranch'), {
        onSuccess: () => {
            // recargar para que el backend sirva los productos del nuevo branch
            window.location.reload();
        },
        onError: (errors: any) => {
            console.error('Failed to switch branch', errors);
        }
    });
};

// 2. Propiedad computada para encontrar y combinar los datos del producto
const currentProduct = computed(() => {
    if (selectedProductId.value === null) {
        return null;
    }
    
    // Busca el producto en bodega (tabla 'products')
    const product = products.find(p => p.id === selectedProductId.value);
    
    // Busca el producto en la tienda (tabla 'product_stores')
    const productInStore = productStores.find(p => p.product_id === selectedProductId.value);
    
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
    if (price === undefined || price === null || !usd || !usd.exchange_rate) {
        return 'N/A';
    }
    return (price * usd.exchange_rate).toFixed(2);
};

// FUNCIÓN MODIFICADA: Calcula el precio en Bs y le suma el 1.1%
const toBsP = (price: number | undefined | null) => {
    if (price === undefined || price === null || !usd || !usd.exchange_rate) {
        return 'N/A';
    }
    // 1. Convertir a bolivianos
    const priceInBs = price * usd.exchange_rate;
    // 2. Sumar el 1.1% (multiplicar por 1.011)
    const priceWithProfit = priceInBs * 1.1;
    
    return priceWithProfit.toFixed(2);
};

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex items-center justify-between mb-2">
                <div class="text-sm">Sucursal: <span class="font-bold">{{ currentBranch ? currentBranch.name : 'Sin sucursal asignada' }}</span></div>
                <div>
                    <label v-if="currentUser && currentUser.role_id === 1" class="mr-2 font-medium">Seleccionar sucursal:</label>
                    <select v-if="currentUser && currentUser.role_id === 1" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                        <option value="">-- Seleccionar --</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-3 grid-cols-1">
                <!-- Placeholder 1: Buscar Producto -->
                <div class="relative rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center p-4 min-h-[220px] h-auto w-full">
                    <h1 class="text-2xl font-bold mb-4">Buscar Producto</h1>
                    <SelectSearch
                        v-model="selectedProductId"
                        :options="products"
                        :searchKeys="['name', 'code']"
                        placeholder="Buscar un producto por nombre o código..."
                        labelKey="code"
                        class="w-full"
                    />
                </div>

                <!-- Placeholder 2: Detalle del Producto -->
                <div class="relative rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col justify-center p-4 min-h-[220px] h-auto w-full">
                    <h1 class="text-xl font-bold mb-2">Detalle del Producto</h1>
                    <div v-if="currentProduct">
                        <p><strong>Nombre:</strong> {{ currentProduct.name }}</p>
                        <p><strong>Código:</strong> {{ currentProduct.code }}</p>
                        <p><strong>Cantidad en Bodega:</strong> {{ currentProduct.quantity_in_stock }}</p>
                        <p><strong>Cantidad en Tienda:</strong> {{ currentProduct.quantity_in_store }}</p>
                        <p><strong>P/U en Dólares:</strong> ${{ currentProduct.unit_price }} </p>
                        <p><strong>P/U en Bolivianos:</strong> Bs. {{ toBs(currentProduct.unit_price) }} </p>
                        <p><strong>P/U sugerido:</strong> Bs {{ toBsP(currentProduct.unit_price) }}</p>
                    </div>
                    <div v-else>
                        <p class="text-gray-500 mt-2">No hay producto seleccionado.</p>
                    </div>
                </div>

                <!-- Placeholder 3: Cambio Actual -->
                <div class="relative rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center p-4 min-h-[220px] h-auto w-full">
                    <p class="mt-2 text-xl">Cambio Actual</p>
                    <h1 class="text-5xl font-bold">Bs. {{usd.exchange_rate}}</h1>
                    <div class="mt-4">
                        <ActionButton
                            color="green"
                            iconName="bx-pencil"
                            :href="route('rusdexchangerates.edit',1)"
                            name="Editar"
                        />
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <h1 class="text-center mt-10">PRODUCTOS CON 30 SIN VENDER</h1>
                <productsTable
                :cadena="productStores30Days??[]"
                :cabeceras="[
                    
                    'Producto',
                    'Cantidad',
                    'P/U $us.',
                    'P/U Bs.',
                    'P/U + 1.1%',
                    
                ]"
                :campos="[
                        
                        'product_id',
                        'quantity',
                        'unit_price',
                        'unit_price_bs',
                        'porcentaje',
                        
                        ]"
                :agregar="false"
                 :acciones="[
                    
                 
                ]"
                headerBgColor="bg-[#D1340D]"
                headerTextColor="text-[#000000]"
                />

                <h1 class="text-center mt-10">PRODUCTOS CON 15 SIN VENDER</h1>
                <productsTable
                :cadena="productStores15Days??[]"
                :cabeceras="[
                    
                    'Producto',
                    'Cantidad',
                    'P/U $us.',
                    'P/U Bs.',
                    'P/U + 1.1%',
                  
                ]"
                :campos="[
                        
                        'product_id',
                        'quantity',
                        'unit_price',
                        'unit_price_bs',
                        'porcentaje',
                        
                        ]"
                :agregar="false"
                 :acciones="[
                ]"
                headerBgColor="bg-[#D1960D]"
                headerTextColor="text-[#000000]"
                />
            </div>
        </div>
    </AppLayout>
</template>
