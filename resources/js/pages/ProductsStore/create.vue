<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Trash2 } from 'lucide-vue-next';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SelectSearch } from '@/components/ui/SelectSearch';
import InputError from '@/components/InputError.vue';

// App Layout & Types
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Product, ProductStore } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingreso de productos a la tienda',
        href: route('rproductstores.create'),
    },
];

const props = defineProps<{
    products: Product[];
    productStores?: ProductStore[];
}>();

// Estado del formulario con Inertia, ahora con un ARRAY
const productStoresForm = useForm({
    items: [{
        product_id: null as number | null,
        quantity: undefined as number | undefined,
        unit_price: undefined as number | undefined,
    // price_multiplier es ahora un factor multiplicativo (ej. 1.1 => +10%)
    price_multiplier: 1.0 as number,
    }],
});

// Función para agregar un nuevo ítem al formulario
const addProductStoreItem = () => {
    const newItem = {
        product_id: null,
        quantity: undefined,
        unit_price: undefined,
        price_multiplier: 1.0,
    };
    // Calcular precio inicial
    updateCalculatedPrice(newItem as any);
    productStoresForm.items.push(newItem as any);
};

// Función para eliminar un ítem del formulario
const removeProductStoreItem = (index: number) => {
    productStoresForm.items.splice(index, 1);
};

// Función para encontrar el producto en bodega (útil para mostrar el stock)
const findProductInWarehouse = (id: number | null) => {
    return props.products.find(p => p.id === id) || null;
};

// Función para calcular el precio unitario final basado en la bodega y el multiplicador
const computeUnitPrice = (item: { product_id: number | null; price_multiplier: number }) => {
    const product = findProductInWarehouse(item.product_id);
    const base = product?.unit_price ? Number(product.unit_price) : 0;
    // Multiplicador multiplicativo: base * multiplier
    const multiplier = item.price_multiplier ?? 1.0;
    return Number((base * multiplier).toFixed(2));
};

// Cuando cambie el producto o el multiplicador, actualizamos el unit_price
const updateCalculatedPrice = (item: { product_id: number | null; price_multiplier: number; unit_price?: number }) => {
    item.unit_price = computeUnitPrice(item as any);
};

const submit = () => {
    productStoresForm.post(route('rproductstores.store'), {
        onSuccess: () => {
            productStoresForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div v-for="(item, index) in productStoresForm.items" :key="index" class="grid gap-6 p-4 border-b border-gray-200">
                        <h3>Producto #{{ index + 1 }}</h3>
                        
                        <div class="grid gap-2">
                            <Label :for="'product_id-' + index">Elija un producto</Label>
                            <SelectSearch
                                :id="'product_id-' + index"
                                                        v-model="item.product_id"
                                                        @change="updateCalculatedPrice(item)"
                                :options="props.products"
                                :searchKeys="['name', 'code']"
                                placeholder="Buscar un producto por nombre o código..."
                                required
                                labelKey="code"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.product_id`]" />
                            
                            <p v-if="findProductInWarehouse(item.product_id)" class="text-sm text-gray-500 mt-1">
                                Stock en bodega: <span class="font-bold">{{ findProductInWarehouse(item.product_id)?.quantity_in_stock }}</span> unidades
                            </p>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label :for="'quantity-' + index">Cantidad de unidades que ingresan a la tienda</Label>
                            <Input
                                :id="'quantity-' + index"
                                type="number"
                                required
                                min="1"
                                v-model.number="item.quantity"
                                placeholder="Ej. 10"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.quantity`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Precio unitario en Dólares ($us)</Label>
                            <p class="py-2">
                                <span class="font-medium">{{ findProductInWarehouse(item.product_id)?.unit_price ? Number(findProductInWarehouse(item.product_id)?.unit_price).toFixed(2) : '0.00' }}</span>
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label :for="'price_multiplier-' + index">Multiplicador (ej. 1.1 para +10%)</Label>
                            <Input
                                :id="'price_multiplier-' + index"
                                type="number"
                                min="1"
                                step="0.01"
                                v-model.number="item.price_multiplier"
                                placeholder="Ej. 1.1000"
                                @input="updateCalculatedPrice(item)"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.price_multiplier`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="'calculated_price-' + index">Precio unitario calculado ($us)</Label>
                            <Input
                                :id="'calculated_price-' + index"
                                type="text"
                                readonly
                                :value="item.unit_price !== undefined ? Number(item.unit_price).toFixed(2) : (findProductInWarehouse(item.product_id)?.unit_price ? Number(findProductInWarehouse(item.product_id)?.unit_price).toFixed(2) : '0.00')"
                                @focus="$event.preventDefault()"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.unit_price`]" />
                        </div>
                

                        <Button v-if="productStoresForm.items.length > 1" type="button" @click="removeProductStoreItem(index)" variant="destructive">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Eliminar producto
                        </Button>
                    </div>

                    <Button type="button" @click="addProductStoreItem" variant="outline" class="w-full mt-2">
                        Agregar otro producto
                    </Button>

                    <Button type="submit" class="w-full mt-2" :disabled="productStoresForm.processing">
                        <LoaderCircle v-if="productStoresForm.processing" class="w-4 h-4 animate-spin" />
                        Registrar productos
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

