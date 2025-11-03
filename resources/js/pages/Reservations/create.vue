<script setup lang="ts">
import { computed, watch } from 'vue';
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
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de reservaciones',
        href: route('rreservations.index'),
    },
];

const { customers, productStores, usd_exchange_rate, productBranches } = defineProps<{
    customers: any[];
    productStores: any[];
    usd_exchange_rate: any;
    productBranches?: Array<any>;
}>();

const exchangeRate = computed(() => {
    if (usd_exchange_rate && usd_exchange_rate.id === 1) {
        return usd_exchange_rate.exchange_rate;
    }
    return 1;
});

// Estado del formulario
const form = useForm({
    customer_id: null as number | null,
    pay_type: '' as string,
    advance_amount: 0 as number,
    items: [{
        product_id: null as number | null,
        quantity_from_warehouse: undefined as number | undefined,
        quantity_from_store: undefined as number | undefined,
        unit_price_usd: 0 as number,
        selected_price: '' as string | number,
        selected_min_type: 'bs_sale' as 'usd' | 'bs' | 'bs_sale',
    }],
});

const addReservationItem = () => {
    form.items.push({
        product_id: null,
        quantity_from_warehouse: undefined,
        quantity_from_store: undefined,
        unit_price_usd: 0,
        selected_price: '',
        selected_min_type: 'bs_sale',
    });
};

const removeReservationItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Encontrar producto en tienda
const findProductInStore = (productId: number | null) => {
    if (!productId || !productStores) {
        return null;
    }
    return productStores.find((p: any) => p.product_id === productId) || null;
};

// Función para encontrar un producto en bodega y su stock (productBranches)
const findProductInWarehouse = (productId: number | null) => {
    if (!productId) return null;

    if (!productBranches || productBranches.length === 0) {
        return null;
    }

    // Buscar en productBranches por product_id (match exacto)
    const pb = productBranches.find((b: any) => {
        return parseInt(b.product_id) === parseInt(productId as any);
    });
    
    if (pb) {
        return {
            id: pb.id,
            product_id: pb.product_id,
            name: pb.product?.name ?? null,
            code: pb.product?.code ?? null,
            quantity_in_stock: pb.quantity_in_stock ?? 0,
            unit_price: pb.unit_price ?? 0,
        };
    }

    return null;
};

// Calcular precio total en USD por item
const itemPriceUsd = (item: any) => {
    const productInStore = findProductInStore(item.product_id);
    const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
    return productInStore && totalQuantity > 0 ? productInStore.unit_price * totalQuantity : 0;
};

// Calcular precio total en Bs por item
const itemPriceBs = (item: any) => {
    return itemPriceUsd(item) * exchangeRate.value;
};

// Devuelve el precio de Venta en Bolivianos (Bs + 1.1% - Muestra la referencia de venta)
const itemSalePriceBs = (item: any) => {
    const priceBs = itemPriceBs(item);
    // Precio de venta (Precio Bs + 1.1% -> Multiplicamos por 1.1)
    return priceBs * 1.1;
};

// Calcula el mínimo según el radio seleccionado
const getItemMinPrice = (item: any) => {
    if (!item.product_id) return 0;
    switch (item.selected_min_type) {
        case 'usd':
            return itemPriceUsd(item);
        case 'bs':
            return itemPriceBs(item);
        case 'bs_sale':
            return itemSalePriceBs(item);
        default:
            return itemPriceBs(item);
    }
};

// --- MANEJO DEL CAMBIO DE TIPO MÍNIMO ---
// Regla: habilitar la opción 'bs' solo si la cantidad es > 3.
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
        item.minimum_limit = itemPriceUsd(item) * exchangeRate.value;
    } else {
        item.minimum_limit = getItemMinPrice(item);
    }
};

// Watch para corregir automáticamente selecciones inválidas cuando la cantidad cambie
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

// Calcular total de la reservación sin conversiones
// Solo suma los precios tal como están ingresados
const totalAmountInSelectedCurrency = computed(() => {
    return form.items.reduce((total, item) => {
        const price = parseFloat(item.selected_price as any) || 0;
        return total + price;
    }, 0);
});

// Calcular saldo restante
const restAmount = computed(() => {
    return totalAmountInSelectedCurrency.value - (form.advance_amount || 0);
});

// Validar si un item tiene precio válido (mayor o igual al mínimo)
const isItemPriceValid = (item: any) => {
    const price = parseFloat(item.selected_price as any) || 0;
    const minPrice = getItemMinPrice(item);
    return price >= minPrice;
};

// Validar que todos los items están en la MISMA MONEDA
// Bolivianos y Venta son compatibles (ambos en Bs)
// Dólares solo debe estar solo
const allItemsHaveSameCurrency = computed(() => {
    if (form.items.length === 0) return true;
    
    // Agrupar items por moneda
    const hasUsd = form.items.some((item: any) => item.selected_min_type === 'usd');
    const hasBs = form.items.some((item: any) => item.selected_min_type === 'bs' || item.selected_min_type === 'bs_sale');
    
    // No se puede mezclar USD con Bs/Venta
    if (hasUsd && hasBs) {
        return false;
    }
    
    return true;
});

// Validar si todos los items tienen precios válidos y la misma moneda
const areAllItemsValid = computed(() => {
    // Primero verificar que todos los items estén en la misma moneda
    if (!allItemsHaveSameCurrency.value) {
        return false;
    }
    
    return form.items.every((item: any) => {
        const totalQuantity = (item.quantity_from_warehouse || 0) + (item.quantity_from_store || 0);
        if (!item.product_id || totalQuantity === 0) return false;
        return isItemPriceValid(item);
    });
});

const submit = () => {
    // Solo enviar tal como está
    // El backend sabrá si es USD o Bs según el selected_min_type
    // NO convertimos nada aquí
    
    form.post(route('rreservations.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<style scoped>
/* Forzar visibilidad del select en todos los temas */
select {
    background-color: #ffffff !important;
    color: #000000 !important;
}

select option {
    background-color: #ffffff !important;
    color: #000000 !important;
}

select option:disabled {
    background-color: #f3f4f6 !important;
    color: #6b7280 !important;
}
</style>

<template>
    <Head title="Nueva Reservación" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="submit" class="flex flex-col gap-6 p-6">
                    <div class="grid gap-6">
                        <!-- Cliente -->
                        <div class="grid gap-2">
                            <Label for="customer_id">Seleccionar Cliente</Label>
                            <SelectSearch
                                id="customer_id"
                                v-model="form.customer_id"
                                :options="customers"
                                :searchKeys="['name', 'email']"
                                placeholder="Buscar cliente por nombre o email..."
                                required
                                labelKey="name"
                            />
                            <InputError :message="form.errors.customer_id" />
                        </div>

                         <div class="grid gap-2">
                            <Label for="pay_type">Método de pago</Label>
                            <select
                                id="pay_type"
                                name="pay_type"
                                v-model="form.pay_type"
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm bg-white text-black"
                                required
                            >
                                <option value="">-- Seleccione un método de pago --</option>
                                <option value="Dolares">Dólares</option>
                                <option value="Bolivianos">Bolivianos</option>
                                <option value="Qr">QR</option>
                            </select>
                            <InputError :message="form.errors.pay_type" />
                        </div>

                        <!-- Items de Productos -->
                        <div v-for="(item, index) in form.items" :key="index" class="grid gap-6 p-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">Producto #{{ index + 1 }}</h3>
                                <Button 
                                    v-if="form.items.length > 1"
                                    type="button"
                                    @click="removeReservationItem(index)"
                                    variant="destructive"
                                    class="flex items-center gap-2 px-4 py-2 text-base"
                                >
                                    <Trash2 class="w-5 h-5" />
                                    Eliminar Producto
                                </Button>
                            </div>

                            <div class="grid gap-2">
                                <Label :for="'product_id-' + index">Elija un producto</Label>
                                <SelectSearch
                                    :id="'product_id-' + index"
                                    v-model="item.product_id"
                                    :options="productStores"
                                    :searchKeys="['product_name', 'product_code']"
                                    placeholder="Buscar un producto por nombre o código..."
                                    required
                                    labelKey="product_code"
                                    valueKey="product_id"
                                    :autofocus="index === 0"
                                />
                                <InputError :message="form.errors[`items.${index}.product_id`]" />
                            </div>

                            <!-- Display de Stock -->
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
                                    placeholder="Ej. 5"
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
                                    placeholder="Ej. 5"
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
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" :class="!allowBs(item) ? 'opacity-50 cursor-not-allowed' : ''">
                                            Precio Base en Bolivianos: 
                                            <span class="font-bold text-base text-green-700 dark:text-green-300">Bs. {{ itemPriceBs(item).toFixed(2) }}</span>
                                            <span v-if="!allowBs(item)" class="text-xs text-red-600 dark:text-red-400">(Mín. 4 unidades)</span>
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
                                <p v-else class="text-sm text-gray-500">Seleccione un producto y cantidad para ver las referencias de precio.</p>
                            </div>

                            <!-- Campo de Input para el Precio Final Cobrado por este Ítem -->
                            <div class="grid gap-2 mt-2">
                                <Label :for="'selected_price-' + index" class="font-bold text-lg">
                                    Precio de Reservación por este producto ({{ item.selected_min_type === 'usd' ? '$' : 'Bs.' }})
                                </Label>
                                <div class="flex gap-2 items-center">
                                    <Input 
                                        :id="'selected_price-' + index"
                                        type="text"
                                        required
                                        :placeholder="item.selected_min_type === 'usd' ? 'Ingrese el precio en dólares...' : 'Ingrese el precio en bolivianos...'"
                                        v-model="item.selected_price"
                                        :class="item.selected_price && !isItemPriceValid(item) ? 'border-red-500' : ''"
                                    />
                                    <span class="text-xs text-gray-500 whitespace-nowrap">Mín: {{ item.selected_min_type === 'usd' ? '$' : 'Bs. ' }}{{ getItemMinPrice(item).toFixed(2) }}</span>
                                </div>
                                <InputError v-if="item.selected_price && !isItemPriceValid(item)" :message="`El precio debe ser mayor o igual a ${item.selected_min_type === 'usd' ? '$' : 'Bs. '}${getItemMinPrice(item).toFixed(2)}`" />
                                <InputError :message="form.errors[`items.${index}.selected_price`]" />
                            </div>
                        </div>

                        <!-- Botón para agregar otro producto -->
                        <Button 
                            type="button"
                            @click="addReservationItem"
                            variant="outline"
                            class="w-full"
                        >
                            + Agregar Otro Producto
                        </Button>

                        <!-- Resumen de Totales -->
                        <div class="grid gap-4 p-4 bg-indigo-50 dark:bg-indigo-900 rounded-lg border border-indigo-200 dark:border-indigo-700">
                            <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100">Resumen de Reservación</h3>
                            
                            <!-- Validación: Todos los items deben estar en la MISMA MONEDA -->
                            <div v-if="form.items.length > 0 && !allItemsHaveSameCurrency" class="p-3 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 rounded-lg">
                                <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                                    🚫 NO se puede mezclar Dólares con Bolivianos/Venta
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                    Usa TODOS en Dólares, O todos en Bolivianos/Venta
                                </p>
                            </div>
                            
                            <div class="grid gap-2">
                                <div class="flex justify-between text-base">
                                    <span>Monto Total ({{ form.pay_type === 'Dolares' ? '$' : 'Bs.' }}):</span>
                                    <span class="font-bold text-lg text-green-700 dark:text-green-300">{{ form.pay_type === 'Dolares' ? '$' : 'Bs. ' }}{{ totalAmountInSelectedCurrency.toFixed(2) }}</span>
                                </div>
                                
                                <div class="grid gap-2">
                                    <Label for="advance_amount">Anticipo ({{ form.pay_type === 'Dolares' ? '$' : 'Bs.' }})</Label>
                                    <Input 
                                        id="advance_amount"
                                        type="number"
                                        :placeholder="form.pay_type === 'Dolares' ? '0.00 USD' : '0.00 Bs.'"
                                        min="0"
                                        :max="totalAmountInSelectedCurrency"
                                        step="0.01"
                                        v-model.number="form.advance_amount"
                                    />
                                    <InputError :message="form.errors.advance_amount" />
                                </div>

                                <div class="flex justify-between text-base">
                                    <span>Saldo Restante ({{ form.pay_type === 'Dolares' ? '$' : 'Bs.' }}):</span>
                                    <span class="font-bold text-lg text-red-700 dark:text-red-300">{{ form.pay_type === 'Dolares' ? '$' : 'Bs. ' }}{{ restAmount.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Submit -->
                        <Button 
                            type="submit" 
                            class="w-full mt-4" 
                            :disabled="form.processing || !form.customer_id || form.items.some(i => !i.product_id) || !areAllItemsValid"
                        >
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                            Guardar Reservación
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
