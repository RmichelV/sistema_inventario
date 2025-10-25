<script setup lang="ts">
import { computed, ref, watch } from 'vue';
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

const { products, productStores, usd_exchange_rate, productBranches } = defineProps<{
    products: Product[];
    productStores?: ProductStore[];
    usd_exchange_rate?: Usd_exchange_rate;
    // opcional: colección de product_branches (stock por sucursal)
    productBranches?: Array<any>;
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
        selected_price: undefined as number | undefined,
        selected_min_type: 'bs_sale' as 'usd' | 'bs' | 'bs_sale', // Por defecto: opción 3
    }],
});

// Funciones para agregar y eliminar ítems de venta
const addSaleItem = () => {
    form.items.push({
        product_id: null,
        quantity_from_warehouse: undefined,
        quantity_from_store: undefined,
        selected_price: undefined,
        selected_min_type: 'bs_sale',
    });
};
// Calcula el mínimo según el radio seleccionado
const getItemMinPrice = (item: any) => {
    if (!item.product_id) return 0;
    switch (item.selected_min_type) {
        case 'usd':
            return itemPriceUsd(item) * exchangeRate.value;
        case 'bs':
            return itemPriceBs(item);
        case 'bs_sale':
            return itemSalePriceBs(item);
        default:
            return itemPriceBs(item);
    }
};

// --- MANEJO DEL CAMBIO DE TIPO MÍNIMO ---
// Regla: habilitar la opción 'bs' solo si la suma de cantidades (bodega+tienda) es > 3.
const allowBs = (item: any) => {
    const total = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    return total > 3;
};

const handleMinTypeChange = (item: any, type: 'usd' | 'bs' | 'bs_sale') => {
    // Si intentan seleccionar 'bs' pero no cumple la condición, forzamos 'bs_sale'
    if (type === 'bs' && !allowBs(item)) {
        item.selected_min_type = 'bs_sale';
        item.minimum_limit = getItemMinPrice(item);
        return;
    }

    item.selected_min_type = type;
    if (type === 'usd') {
        item.minimum_limit = itemPriceUsd(item);
    } else {
        item.minimum_limit = getItemMinPrice(item);
    }
};

// Watch para corregir automáticamente selecciones inválidas cuando las cantidades cambien
watch(
    () => form.items.map((it: any) => ({ q: (it.quantity_from_warehouse || 0) + (it.quantity_from_store || 0), sel: it.selected_min_type })),
    () => {
        form.items.forEach((it: any) => {
            if (it.selected_min_type === 'bs' && !allowBs(it)) {
                it.selected_min_type = 'bs_sale';
                it.minimum_limit = getItemMinPrice(it);
            }
        });
    },
    { deep: true }
);

const removeSaleItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Función para encontrar un producto de la tienda y sus precios
const findProductInStore = (productId: number | null) => {
    if (!productId || !productStores) {
        return null;
    }
    return productStores.find(p => p.product_id === productId) || null;
};

// Función para encontrar un producto en bodega y su stock (preferir productBranches)
const findProductInWarehouse = (productId: number | null) => {
    if (!productId) return null;

    // Si el backend envía productBranches (product_branches por sucursal), lo usamos
    if (typeof productBranches !== 'undefined' && productBranches && productBranches.length > 0) {
        const pb = productBranches.find((b: any) => {
            return b.product_id === productId || (b.product && b.product.id === productId) || b.id === productId;
        });
        if (pb) {
            return {
                id: pb.product_id ?? pb.id ?? (pb.product ? pb.product.id : null),
                name: pb.product?.name ?? pb.name ?? null,
                code: pb.product?.code ?? pb.code ?? null,
                img_product: pb.product?.img_product ?? pb.img_product ?? null,
                quantity_in_stock: pb.quantity_in_stock ?? 0,
                unit_price: pb.unit_price ?? 0,
                units_per_box: pb.units_per_box ?? null,
                last_update: pb.last_update ?? null,
            };
        }
    }

    // Fallback a products si no existe productBranches
    return products.find(p => p.id === productId) || null;
};

// Propiedad computada para obtener la tasa de cambio del dólar
const exchangeRate = computed(() => {
    if (usd_exchange_rate && usd_exchange_rate.id === 1) {
        return usd_exchange_rate.exchange_rate;
    }
    return 1; 
});

// Función para convertir a bolivianos (Bs) - Devuelve String formateado
// toBsDisplay removed (not used anywhere)

// --- LÓGICA DE CÁLCULO DE PRECIOS POR ÍTEM ---

// Devuelve el precio total en USD
const itemPriceUsd = (item: any) => {
    const productInStore = findProductInStore(item.product_id);
    const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    // 1. Precio del producto (en Dólares)
    return productInStore && totalQuantity > 0 ? productInStore.unit_price * totalQuantity : 0;
};

// Devuelve el precio total en Bolivianos (Conversión directa)
const itemPriceBs = (item: any) => {
    const priceUsd = itemPriceUsd(item);
    // 2. Precio en bolivianos (al tipo de cambio)
    return priceUsd * exchangeRate.value;
};

// Devuelve el precio de Venta en Bolivianos (Bs + 1.1% - Muestra la referencia de venta)
const itemSalePriceBs = (item: any) => {
    const priceBs = itemPriceBs(item);
    // 3. Precio de venta (Precio Bs + 1.1% -> Multiplicamos por 1.011)
    return priceBs * 1.1;
};


// 1. PROPIEDAD COMPUTADA: SUMA TOTAL DEL PRECIO BASE EN BOLIVIANOS (El MÍNIMO ABSOLUTO)
const totalMinimumBs = computed(() => {
    return form.items.reduce((total, item) => {
        return total + itemPriceBs(item);
    }, 0);
});


// totalSalePrice removed (not used anywhere)

// Propiedad computada para calcular el precio total de la venta, usando el precio seleccionado de cada ítem (en Bs)
const totalSalePriceBs = computed(() => {
    return form.items.reduce((total, item) => {
        // Suma el selected_price (en Bs)
        if (item.selected_price) {
            return total + item.selected_price;
        }
        return total;
    }, 0).toFixed(2); // Retorna String para display
});

// --- LÓGICA DEL PRECIO MÍNIMO DINÁMICO PARA FINAL_PRICE ---
const minimumFinalPrice = computed<number>(() => { // <- Tipo explícito para asegurar que solo devuelve number
    // totalMinimumBs.value es un float number (el precio base total en Bs)
    const totalBs = totalMinimumBs.value; 

    // Si no hay productos seleccionados o el total es cero
    if (totalBs === 0) {
        return 0;
    }

    // Si el método de pago es Dólares, el mínimo es el total mínimo de Bs convertido a USD
    if (form.pay_type === 'Dolares') {
        const minUsd = totalBs / exchangeRate.value;
        // FIX: Se usa parseFloat para convertir el resultado de toFixed (que es un string) de nuevo a number.
        return parseFloat(minUsd.toFixed(2));
    } 
    // Si el método de pago es Bolivianos o Qr, el mínimo es el total mínimo en Bs
    else if (form.pay_type === 'Bolivianos' || form.pay_type === 'Qr') {
        // FIX: Se usa parseFloat para convertir el resultado de toFixed (que es un string) de nuevo a number.
        return parseFloat(totalBs.toFixed(2));
    }
    
    // Si no se ha seleccionado método de pago, el mínimo es 0 hasta que se escoja uno.
    return 0; 
});


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
                                    :options="products"
                                    :searchKeys="['name', 'code']"
                                    placeholder="Buscar un producto por nombre o código..."
                                    required
                                    labelKey="code"
                                    :autofocus="index === 0"
                                />
                                <InputError :message="form.errors[`items.${index}.product_id`]"/>
                            </div>
                            
                            <!-- Display de Stocks -->
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
                                    :max="findProductInWarehouse(item.product_id)?.quantity_in_stock ?? 0"
                                    defaultValue="0"
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
                                    :max="findProductInStore(item.product_id)?.quantity ?? 0"
                                    defaultValue="0"
                                    v-model.number="item.quantity_from_store"
                                />
                                <InputError :message="form.errors[`items.${index}.quantity_from_store`]" />
                            </div>
                            
                            <!-- BLOQUE DE PRECIOS DE REFERENCIA CON LOS 3 VALORES -->
                            <div class="grid gap-2 mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-indigo-300 dark:border-indigo-700">
                                <Label class="text-lg font-semibold text-indigo-700 dark:text-indigo-400">Referencias de Precio (Total por Cantidad)</Label>
                                
                                <div v-if="item.product_id && ((item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0) > 0)" class="flex flex-col gap-2">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" :name="'ref-radio-' + index" value="usd" v-model="item.selected_min_type" @change="handleMinTypeChange(item, 'usd')">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Precio Producto (Dólares): 
                                            <span class="font-bold text-base text-blue-600">${{ itemPriceUsd(item).toFixed(2) }}</span>
                                        </span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" :name="'ref-radio-' + index" value="bs" v-model="item.selected_min_type" @change="handleMinTypeChange(item, 'bs')" :disabled="!allowBs(item)">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Precio Base en Bolivianos: 
                                            <span class="font-bold text-base text-green-700 dark:text-green-300">Bs. {{ itemPriceBs(item).toFixed(2) }}</span>
                                        </span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" :name="'ref-radio-' + index" value="bs_sale" v-model="item.selected_min_type" @change="handleMinTypeChange(item, 'bs_sale')">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Precio de Venta (Bs + 1.1%): 
                                            <span class="font-bold text-base text-red-600">Bs. {{ itemSalePriceBs(item).toFixed(2) }}</span>
                                        </span>
                                    </label>
                                </div>
                                <p v-else class="text-sm text-gray-500">Seleccione un producto y cantidades para ver las referencias de precio.</p>
                            </div>
                            
                            <!-- Campo de Input para el Precio Final Cobrado por este Ítem -->
                            <div class="grid gap-2 mt-2">
                                <Label :for="'selected_price-' + index" class="font-bold text-lg">
                                    Precio Cobrado por ESTE producto (Bs.)
                                </Label>
                                <!-- Radios para elegir el mínimo -->
                                <Input 
                                    :id="'selected_price-' + index"
                                    type="number"
                                    required
                                    placeholder="Ingrese el precio final cobrado por este ítem..."
                                    :min="item.selected_min_type === 'usd' ? itemPriceUsd(item) : getItemMinPrice(item)"
                                    step="0.01"
                                    v-model.number="item.selected_price"
                                />
                                <InputError :message="form.errors[`items.${index}.selected_price`]" />
                            </div>

                            <Button v-if="form.items.length > 1" type="button" @click="removeSaleItem(index)" variant="destructive">
                                <Trash2 class="w-4 h-4 mr-2" />
                                Eliminar producto
                            </Button>
                        </div>
                        
                        <Button type="button" @click="addSaleItem" variant="outline" class="w-full mt-2">
                            Agregar otro producto
                        </Button>

                        <div class="grid gap-2 p-4 border-t border-gray-200 mt-4">
        
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Suma total: <span class="text-xl font-bold"> {{ totalSalePriceBs }}</span>
                                
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
                            <!-- Label Dinámico para Precio Final -->
                            <Label for="final_price">
                                Precio Final 
                                <span v-if="form.pay_type === 'Dolares'" class="font-normal text-blue-600">($us)</span>
                                <span v-else-if="form.pay_type === 'Bolivianos' || form.pay_type === 'Qr'" class="font-normal text-green-600">(Bs)</span>
                                <span v-else class="font-normal text-red-500">(Elija el Método de Pago)</span>
                            </Label>
                            <Input
                                id="final_price"
                                type="number"
                                required
                                name="final_price"
                                placeholder="Ej. 500"
                                :min="minimumFinalPrice"
                                step="0.01"
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
