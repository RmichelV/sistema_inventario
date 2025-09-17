<script setup lang="ts">
import { ref, computed } from 'vue';
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

// Estado del formulario con un array 'items' para múltiples productos
const form = useForm({
    sale_date: new Date().toISOString().slice(0, 10),
    customer_name: '' as string,
    pay_type: '' as string,
    final_price: undefined as number | undefined,
    items: [{
        product_id: null as number | null,
        quantity_from_warehouse: undefined as number | undefined,
        quantity_from_store: undefined as number | undefined,
        selected_price: null as number | null,
    }],
});

// Funciones para agregar y eliminar ítems de venta
const addSaleItem = () => {
    form.items.push({
        product_id: null,
        quantity_from_warehouse: undefined,
        quantity_from_store: undefined,
        selected_price: null,
    });
};

const removeSaleItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Función para encontrar un producto de la tienda y sus precios
const findProductInStore = (productId: number | null) => {
    if (!productId || !props.productStores) {
        return null;
    }
    return props.productStores.find(p => p.product_id === productId) || null;
};

// Función para encontrar un producto en bodega y su stock
const findProductInWarehouse = (productId: number | null) => {
    if (!productId) {
        return null;
    }
    return props.products.find(p => p.id === productId) || null;
};

// Propiedad computada para obtener la tasa de cambio del dólar
const exchangeRate = computed(() => {
    if (props.usd_exchange_rate && props.usd_exchange_rate.id === 1) {
        return props.usd_exchange_rate.exchange_rate;
    }
    return 1;
});

// Función para convertir a bolivianos (Bs)
const toBs = (price: number | undefined | null) => {
    if (price === undefined || price === null) {
        return 'N/A';
    }
    return (price * exchangeRate.value).toFixed(2);
};

// Propiedad computada para calcular el precio total de la venta, usando el precio seleccionado de cada ítem
const totalSalePrice = computed(() => {
    return form.items.reduce((total, item) => {
        if (item.selected_price) {
            return total + item.selected_price;
        }
        return total;
    }, 0).toFixed(2);
});

// Funciones para calcular precios individuales
const itemWholesalePrice = (item: any) => {
    const productInStore = findProductInStore(item.product_id);
    const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    return productInStore && totalQuantity > 0 ? productInStore.unit_price_wholesale * totalQuantity : 0;
};

const itemRetailPrice = (item: any) => {
    const productInStore = findProductInStore(item.product_id);
    const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    return productInStore && totalQuantity > 0 ? productInStore.unit_price_retail * totalQuantity : 0;
};

const itemSalePrice = (item: any) => {
    const productInStore = findProductInStore(item.product_id);
    const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    return productInStore && totalQuantity > 0 ? productInStore.saleprice * totalQuantity : 0;
};

const submit = () => {
    form.post(route('rsales.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Registrar Venta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="submit" class="flex flex-col gap-6 p-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2 p-4">
                            <Label for="sale_date">Fecha de venta</Label>
                            <Input 
                                id="sale_date" 
                                type="date" 
                                required 
                                v-model="form.sale_date"
                            />
                            <InputError :message="form.errors.sale_date" />
                        </div>
                        
                        <div v-for="(item, index) in form.items" :key="index" class="grid gap-6 p-4 border-b border-gray-200">
                            <h3>Producto de venta #{{ index + 1 }}</h3>

                            <div class="grid gap-2">
                                <Label :for="'product_id-' + index">Elija un producto</Label>
                                <SelectSearch
                                    :id="'product_id-' + index"
                                    v-model="item.product_id"
                                    :options="props.products"
                                    :searchKeys="['name', 'code']"
                                    placeholder="Buscar un producto por nombre o código..."
                                    required
                                    labelKey="code"
                                />
                                <InputError :message="form.errors[`items.${index}.product_id`]"/>
                            </div>
                            
                            <div class="grid gap-2">
                                <p v-if="findProductInWarehouse(item.product_id)" class="text-base text-blue-700 dark:text-blue-300">
                                    Stock en bodega: <span class="font-extrabold text-lg">{{ findProductInWarehouse(item.product_id)?.quantity_in_stock }}</span> unidades
                                </p>
                                <p v-if="findProductInStore(item.product_id)" class="text-base text-green-700 dark:text-green-300">
                                    Stock en tienda: <span class="font-extrabold text-lg">{{ findProductInStore(item.product_id)?.quantity }}</span> unidades
                                </p>
                            </div>
                            
                            <div class="grid gap-2">
                                <Label :for="'quantity_from_warehouse-' + index">Cantidad desde Bodega</Label>
                                <Input 
                                    :id="'quantity_from_warehouse-' + index"
                                    type="number"
                                    required
                                    placeholder="Ej. 100"
                                    min="0"
                                    v-model.number="item.quantity_from_warehouse"
                                />
                                <InputError :message="form.errors[`items.${index}.quantity_from_warehouse`]" />
                            </div>

                            <div class="grid gap-2">
                                <Label :for="'quantity_from_store-' + index">Cantidad desde Tienda</Label>
                                <Input 
                                    :id="'quantity_from_store-' + index"
                                    type="number"
                                    required
                                    placeholder="Ej. 20"
                                    min="0"
                                    v-model.number="item.quantity_from_store"
                                />
                                <InputError :message="form.errors[`items.${index}.quantity_from_store`]" />
                            </div>
                            
                            <div class="grid gap-2 mt-4">
                                <Label>Seleccione un precio:</Label>
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="radio" 
                                        :id="'wholesale-price-' + index" 
                                        :name="'item-price-' + index" 
                                        :value="itemWholesalePrice(item)"
                                        v-model="item.selected_price"
                                        :disabled="!item.product_id || (!item.quantity_from_warehouse && !item.quantity_from_store)"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        required
                                    />
                                    <label :for="'wholesale-price-' + index" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Mayor: ${{ itemWholesalePrice(item).toFixed(2) }} (Bs {{ toBs(itemWholesalePrice(item)) }})
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="radio" 
                                        :id="'retail-price-' + index" 
                                        :name="'item-price-' + index" 
                                        :value="itemRetailPrice(item)"
                                        v-model="item.selected_price"
                                        :disabled="!item.product_id || (!item.quantity_from_warehouse && !item.quantity_from_store)"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <label :for="'retail-price-' + index" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Menor: ${{ itemRetailPrice(item).toFixed(2) }} (Bs {{ toBs(itemRetailPrice(item)) }})
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="radio" 
                                        :id="'sale-price-' + index" 
                                        :name="'item-price-' + index" 
                                        :value="itemSalePrice(item)"
                                        v-model="item.selected_price"
                                        :disabled="!item.product_id || (!item.quantity_from_warehouse && !item.quantity_from_store)"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <label :for="'sale-price-' + index" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Venta: ${{ itemSalePrice(item).toFixed(2) }} (Bs {{ toBs(itemSalePrice(item)) }})
                                    </label>
                                </div>
                            </div>

                            <Button v-if="form.items.length > 1" type="button" @click="removeSaleItem(index)" variant="destructive">
                                <Trash2 class="w-4 h-4 mr-2" />
                                Eliminar producto
                            </Button>
                        </div>
                        
                        <Button type="button" @click="addSaleItem" variant="outline" class="w-full mt-2">
                            Agregar otro producto
                        </Button>

                        <div class="grid gap-2">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Precio de venta (Total): <span class="text-xl font-bold">${{ totalSalePrice }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"> (Bs {{ toBs(parseFloat(totalSalePrice)) }})</span>
                            </h3>
                        </div>

                         <div class="grid gap-2">
                            <Label for="pay_type">Método de pago</Label>
                            <select
                                id="pay_type"
                                name="pay_type"
                                v-model="form.pay_type"
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                                <option value="" disabled selected>Seleccione un método de pago</option>
                                <option value="Dolares">Dólares</option>
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
                            Registrar venta
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>