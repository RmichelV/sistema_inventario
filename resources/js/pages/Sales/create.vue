<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SelectSearch } from '@/components/ui/SelectSearch';
import InputError from '@/components/InputError.vue';

// App Layout & Types
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Product, ProductStore, Usd_exchange_rate } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Regitrar Venta',
        href: route('rproductstores.create'),
    },
];

const props = defineProps<{
    products: Product[];
    productStores?: ProductStore[];
    usd_exchange_rate?: Usd_exchange_rate;

}>();

// Estado del formulario con Inertia
const form = useForm({
    product_id: null as number | null,
    quantity_products: undefined as number | undefined,

    unit_price_wholesale: undefined as number | undefined,
    unit_price_retail: undefined as number | undefined,
    saleprice: undefined as number | undefined,
    

    sale_date: undefined as Date | undefined,
    pay_type: undefined as string | undefined,
    final_price: undefined as number | undefined,
    customer_name: undefined as string | undefined
});

// Propiedad computada para encontrar el producto de la tienda
const productInStore = computed(() => {
    if (form.product_id === null || !props.productStores) {
        return null;
    }
    return props.productStores.find(p => p.product_id === form.product_id) || null;
});

// Propiedad computada para encontrar el producto en bodega
const productInWarehouse = computed(() => {
    if (form.product_id === null) {
        return null;
    }
    return props.products.find(p => p.id === form.product_id) || null;
});

// Propiedad computada para obtener la tasa de cambio del dólar (se basa en el ID 1)
const exchangeRate = computed(() => {
    if (props.usd_exchange_rate && props.usd_exchange_rate.id === 1) {
        return props.usd_exchange_rate.exchange_rate;
    }
    return 1;
});

// Función para convertir a bolivianos (Bs)
const toBs = (price: number | undefined) => {
    if (price === undefined) {
        return 'N/A';
    }
    return (price * exchangeRate.value).toFixed(2);
};

// Observa los cambios en el producto seleccionado y rellena los campos
watch(productInStore, (newProduct) => {
    if (newProduct) {
        form.unit_price_wholesale = newProduct.unit_price_wholesale;
        form.unit_price_retail = newProduct.unit_price_retail;
        form.saleprice = newProduct.saleprice;
    } else {
        form.unit_price_wholesale = undefined;
        form.unit_price_retail = undefined;
        form.saleprice = undefined;
    }
}, { immediate: true });

// ✨ NUEVAS PROPIEDADES COMPUTADAS PARA LOS TOTALES
const totalWholesalePrice = computed(() => {
    if (form.saleprice === undefined || form.quantity_products === undefined) {
        return null;
    }
    return (form.unit_price_wholesale * form.quantity_products).toFixed(2);
});

const totalRetailPrice = computed(() => {
    if (form.saleprice === undefined || form.quantity_products === undefined) {
        return null;
    }
    return (form.unit_price_retail * form.quantity_products).toFixed(2);
});

const totalSalePrice = computed(() => {
    if (form.saleprice === undefined || form.quantity_products === undefined) {
        return null;
    }
    return (form.saleprice * form.quantity_products).toFixed(2);
});

</script>

<template>
    <Head title="Registrar Venta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="form.post(route('rsales.store'))" class="flex flex-col gap-6 p-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="product_id">Elija un producto </Label>
                            <SelectSearch
                                v-model="form.product_id"
                                :options="props.products"
                                :searchKeys="['name', 'code']"
                                placeholder="Buscar un producto por nombre o código..."
                                required
                                name="product_id"
                                id="product_id"
                                labelKey="code"
                            />
                            <InputError :message="form.errors.product_id" />
                            
                            <div class="mt-3 p-3 rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                                <p v-if="productInWarehouse" class="text-base text-blue-700 dark:text-blue-300">
                                    Stock en bodega: <span class="font-extrabold text-lg">{{ productInWarehouse.quantity_in_stock }}</span> unidades
                                </p>
                                <p v-else class="text-base text-gray-500 dark:text-gray-400">
                                    Stock en bodega: --
                                </p>

                                <p v-if="productInStore" class="text-base text-green-700 dark:text-green-300">
                                    Stock en tienda: <span class="font-extrabold text-lg">{{ productInStore.quantity }}</span> unidades
                                </p>
                                <p v-else-if="form.product_id" class="text-base text-red-700 dark:text-red-300">
                                    Stock en tienda: <span class="font-extrabold text-lg">0</span> unidades
                                </p>
                                <p v-else class="text-base text-gray-500 dark:text-gray-400">
                                    Stock en tienda: --
                                </p>
                            </div>

                            <div v-if="productInStore" class="mt-4 p-3 rounded-md bg-gray-50 dark:bg-gray-700/20 border border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-1 font-semibold">Precios en Tienda:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-800 dark:text-gray-200">
                                    <p>
                                        Mayorista: <span class="font-bold">${{ productInStore.unit_price_wholesale }}</span>
                                        <span class="text-xs text-gray-500 dark:text-red-300 font-bold"> (Bs {{ toBs(productInStore.unit_price_wholesale) }})</span>
                                    </p>
                                    <p>
                                        Minorista: <span class="font-bold">${{ productInStore.unit_price_retail }}</span>
                                        <span class="text-xs text-gray-500 dark:text-red-300 font-bold"> (Bs {{ toBs(productInStore.unit_price_retail) }})</span>
                                    </p>
                                    <p class="md:col-span-2">
                                        Venta: <span class="font-bold">${{ productInStore.saleprice }}</span>
                                        <span class="text-xs text-gray-500 dark:text-red-300 font-bold"> (Bs {{ toBs(productInStore.saleprice) }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="quantity_products">Cantidad de unidades que se venderán</Label>
                            <Input
                                id="quantity_products"
                                type="number"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="quantity_products"
                                name="quantity_products"
                                placeholder="Ej. 500"
                                min="0"
                                v-model.number="form.quantity_products"
                            />
                            <InputError :message="form.errors.quantity_products" />
                        </div>
                        <div class="grid gap-2">
                            <h3 v-if="totalWholesalePrice" class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Precio por Mayor: <span class="text-xl font-bold">${{ totalWholesalePrice }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"> (Bs {{ toBs(totalWholesalePrice) }})</span>
                            </h3>
                            <h3 v-if="totalRetailPrice" class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Precio por Menor: <span class="text-xl font-bold">${{ totalRetailPrice }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"> (Bs {{ toBs(totalRetailPrice) }})</span>
                            </h3>
                            <h3 v-if="totalSalePrice" class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Precio de venta: <span class="text-xl font-bold">${{ totalSalePrice }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"> (Bs {{ toBs(totalSalePrice) }})</span>
                            </h3>
                        </div>

                         <div class="grid gap-2">
                            <Label for="pay_type">Metodo de pago</Label>
                            <select
                                id="pay_type"
                                name="pay_type"
                                v-model="form.pay_type"
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                                <option value="" disabled selected>Seleccione un método de pago</option>
                                <option value="Dolares">Dolares</option>
                                <option value="Bolivianos">Bolivianos</option>
                                <option value="Qr">QR</option>
                            </select>
                            <InputError :message="form.errors.pay_type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="final_price">Precio Final</Label>
                            <Input
                                id="final_price"
                                type="number"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="final_price"
                                name="final_price"
                                placeholder="Ej. 500"
                                min="0"
                                v-model.number="form.final_price"
                            />
                            <InputError :message="form.errors.final_price" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="sale_date">Fecha de venta</Label>
                            <Input
                                id="sale_date"
                                type="date"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="sale_date"
                                name="sale_date"
                                v-model="form.sale_date"
                            />
                            <InputError :message="form.errors.sale_date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="customer_name">Nombre(s) y Apellido(s)</Label>
                            <Input
                                id="customer_name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="customer_name"
                                name="customer_name"
                                placeholder="Ingrese un nombre como referencia"
                                v-model="form.customer_name"
                            />
                            <InputError :message="form.errors.customer_name" />
                        </div>

                        <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Registrar producto
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>