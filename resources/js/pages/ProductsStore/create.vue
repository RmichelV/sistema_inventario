<script setup lang="ts">
import { ref, computed, watch } from 'vue';
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
        unit_price_wholesale: undefined as number | undefined,
        unit_price_retail: undefined as number | undefined,
        saleprice: undefined as number | undefined,
    }],
});

// Función para agregar un nuevo ítem al formulario
const addProductStoreItem = () => {
    productStoresForm.items.push({
        product_id: null,
        quantity: undefined,
        unit_price_wholesale: undefined,
        unit_price_retail: undefined,
        saleprice: undefined,
    });
};

// Función para eliminar un ítem del formulario
const removeProductStoreItem = (index: number) => {
    productStoresForm.items.splice(index, 1);
};

// Función para encontrar el producto en bodega (útil para mostrar el stock)
const findProductInWarehouse = (id: number | null) => {
    return props.products.find(p => p.id === id) || null;
};

// La lógica de watch ya no es necesaria, ya que los campos de precio
// se gestionarán para cada ítem de forma independiente si es necesario,
// o se capturarán al enviar el formulario.

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
                                :options="props.products"
                                :searchKeys="['name', 'code']"
                                placeholder="Buscar un producto por nombre o código..."
                                required
                                labelKey="name"
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
                                min="0"
                                v-model.number="item.quantity"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.quantity`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="'unit_price_wholesale-' + index">P/U al Mayor en $us</Label>
                            <Input
                                :id="'unit_price_wholesale-' + index"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                v-model.number="item.unit_price_wholesale"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.unit_price_wholesale`]" />
                        </div>
                
                        <div class="grid gap-2">
                            <Label :for="'unit_price_retail-' + index">P/U al Menor en $us</Label>
                            <Input
                                :id="'unit_price_retail-' + index"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                v-model.number="item.unit_price_retail"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.unit_price_retail`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="'saleprice-' + index">Precio de Venta</Label>
                            <Input
                                :id="'saleprice-' + index"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                v-model.number="item.saleprice"
                            />
                            <InputError :message="productStoresForm.errors[`items.${index}.saleprice`]" />
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

