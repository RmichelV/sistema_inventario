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
import type { BreadcrumbItem, Product, ProductStore } from '@/types';

// Definición de las props que el componente recibe
const props = defineProps<{
    products: Product[];
    productStores?: ProductStore[];
}>();

// Breadcrumbs para la navegación
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingreso de productos a la tienda',
        href: route('rproductstores.create'),
    },
];

// Estado del formulario con Inertia
const form = useForm({
    product_id: null as number | null,
    quantity: undefined as number | undefined,
    unit_price_wholesale: undefined as number | undefined,
    unit_price_retail: undefined as number | undefined,
    saleprice: undefined as number | undefined,
});

// Propiedad computada para encontrar el producto en la tienda
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

// Observa los cambios en el producto seleccionado y rellena los campos de precio
watch(productInStore, (newProduct) => {
    if (newProduct) {
        form.unit_price_wholesale = newProduct.unit_price_wholesale;
        form.unit_price_retail = newProduct.unit_price_retail;
        form.saleprice = newProduct.saleprice;
    } else {
        // Al deseleccionar o si el producto no está en la tienda, limpia los precios
        form.unit_price_wholesale = undefined;
        form.unit_price_retail = undefined;
        form.saleprice = undefined;
    }
});

// Función para manejar el envío del formulario
const submitForm = () => {
    form.post(route('rproductstores.store'), {
        onSuccess: () => {
            // Lógica a ejecutar después de un envío exitoso, como limpiar el formulario.
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="submitForm" class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="product_id">Elija un producto</Label>
                            <SelectSearch
                                v-model="form.product_id"
                                :options="props.products"
                                :searchKeys="['name', 'code']"
                                placeholder="Buscar un producto por nombre o código..."
                                required
                                id="product_id"
                                labelKey="name"
                            />
                            <InputError :message="form.errors.product_id" />
                            
                            <p v-if="productInWarehouse" class="text-sm text-gray-500 mt-1">
                                Stock en bodega: <span class="font-bold">{{ productInWarehouse.quantity_in_stock }}</span> unidades
                            </p>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="quantity">Cantidad de unidades que ingresan a la tienda</Label>
                            <Input
                                id="quantity"
                                type="number"
                                required
                                :tabindex="1"
                                placeholder="Ej. 500"
                                min="0"
                                v-model.number="form.quantity"
                            />
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="unit_price_wholesale">P/U al Mayor en $us</Label>
                            <Input
                                id="unit_price_wholesale"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                :tabindex="1"
                                placeholder="Ej. 6.96"
                                v-model.number="form.unit_price_wholesale"
                            />
                            <InputError :message="form.errors.unit_price_wholesale" />
                        </div>
                
                        <div class="grid gap-2">
                            <Label for="unit_price_retail">P/U al Menor en $us</Label>
                            <Input
                                id="unit_price_retail"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                :tabindex="1"
                                placeholder="Ej. 6.96"
                                v-model.number="form.unit_price_retail"
                            />
                            <InputError :message="form.errors.unit_price_retail" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="saleprice">Precio de Venta</Label>
                            <Input
                                id="saleprice"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                :tabindex="1"
                                placeholder="Ej. 6.96"
                                v-model.number="form.saleprice"
                            />
                            <InputError :message="form.errors.saleprice" />
                        </div>

                        <Button type="submit" class="w-full mt-2" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Registrar producto
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>