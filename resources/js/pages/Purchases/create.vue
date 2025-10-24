<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Product } from '@/types';
import { SelectSearch } from '@/components/ui/SelectSearch';
import { LoaderCircle, Trash2 } from 'lucide-vue-next';

// This is the purchase form, now using useForm to manage a list of items.
type PurchaseItem = {
    product_id: number | null;
    purchase_quantity: number;
    unit_price?: number;
    total_price?: number;
};

const purchaseForm = useForm<{ purchase_date: string; items: PurchaseItem[] }>({
    purchase_date: new Date().toISOString().slice(0, 10),
    items: [{
        product_id: null,
        purchase_quantity: 0,
        unit_price: 0,
        total_price: 0,
    }],
});

const { products, currentBranch } = defineProps<{
    products: Product[];
    // currentBranch viene desde el controlador (suficiente { id: number })
    currentBranch?: { id: number };
}>();

// Buscar producto en la lista (enriquecida desde el controlador con unit_price)
const findProductInWarehouse = (id: number | null) => {
    return products.find(p => p.id === id) || null;
};

const onProductChange = (item: PurchaseItem) => {
    const prod = findProductInWarehouse(item.product_id);
    if (prod) {
        // Intento obtener el unit_price desde product_branches que coincida con la sucursal actual
        const branchId = (currentBranch && (currentBranch as any).id) || null;
        // prod.product_branches puede venir del backend; usamos optional chaining y any para seguridad en TS
        const branchEntry = branchId && (prod as any).product_branches
            ? (prod as any).product_branches.find((pb: any) => pb.branch_id === branchId)
            : null;

        const priceFromBranch = branchEntry?.unit_price ?? (prod as any).unit_price ?? 0;
        item.unit_price = Number(priceFromBranch || 0);
        item.total_price = Number((Number(item.unit_price || 0) * Number(item.purchase_quantity || 0)).toFixed(2));
    } else {
        item.unit_price = 0;
        item.total_price = 0;
    }
};

// Vigilar cambios en product_id de los items para autocompletar el precio
watch(
    () => purchaseForm.items.map(i => i.product_id),
    (newIds, oldIds) => {
        newIds.forEach((id, idx) => {
            if (id !== oldIds[idx]) {
                const item = purchaseForm.items[idx];
                // Solo ejecutar si existe el item
                if (item) onProductChange(item);
            }
        });
    }
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Registro de compras',
        href: route('rpurchases.create'),
    },
];

const addPurchaseItem = () => {
    purchaseForm.items.push({
        product_id: null,
        purchase_quantity: 0,
        unit_price: 0,
        total_price: 0,
    });
};

const removePurchaseItem = (index: number) => {
    purchaseForm.items.splice(index, 1);
};

const submit = () => {
    purchaseForm.post(route('rpurchases.store'), {
        onSuccess: () => {
            purchaseForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Registro de Compras" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-2 p-4">
                        <Label for="purchase_date">Fecha de compra</Label>
                        <Input 
                            id="purchase_date" 
                            type="date" 
                            required 
                            v-model="purchaseForm.purchase_date"
                        />
                        <InputError :message="purchaseForm.errors.purchase_date" />
                    </div>

                    <div v-for="(item, index) in purchaseForm.items" :key="index" class="grid gap-6 p-4 border-b border-gray-200">
                        <h3>Producto de compra #{{ index + 1 }}</h3>

                        <div class="grid gap-2">
                            <Label :for="'product_id-' + index">Elija un producto</Label>
                            <SelectSearch
                                :id="'product_id-' + index"
                                v-model="item.product_id"
                                @change="onProductChange(item)"
                                :options="products"
                                :searchKeys="['name', 'code']"
                                placeholder="Buscar un producto por nombre o código..."
                                required
                                labelKey="code"
                            />
                            <InputError :message="purchaseForm.errors[`items.${index}.product_id`]"/>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label :for="'purchase_quantity-' + index">Cantidad de compra</Label>
                            <Input 
                                :id="'purchase_quantity-' + index" 
                                type="number" 
                                required
                                placeholder="Ej. 500" 
                                min="0"
                                v-model.number="item.purchase_quantity"
                                @input="item.total_price = Number((Number(item.unit_price || 0) * Number(item.purchase_quantity || 0)).toFixed(2))"
                            />
                            <InputError :message="purchaseForm.errors[`items.${index}.purchase_quantity`]"/>
                        </div>

                        <div class="grid gap-2">
                            <Label :for="'unit_price-' + index">Precio unitario ($us)</Label>
                            <Input
                                :id="'unit_price-' + index"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model.number="item.unit_price"
                                @input="item.total_price = Number((Number(item.unit_price || 0) * Number(item.purchase_quantity || 0)).toFixed(2))"
                                placeholder="Ej. 2.50"
                            />
                            <InputError :message="purchaseForm.errors[`items.${index}.unit_price`]" />
                        </div>


                        <!-- Ahora mostramos inputs editables: unit_price y total_price -->
                        <div class="grid gap-2">
                            <Label :for="'total_price-' + index">Precio total ($us)</Label>
                            <Input
                                :id="'total_price-' + index"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model.number="item.total_price"
                                placeholder="Ej. 1250.00"
                            />
                            <InputError :message="purchaseForm.errors[`items.${index}.total_price`]" />
                        </div>
                        <Button v-if="purchaseForm.items.length > 1" type="button" @click="removePurchaseItem(index)" variant="destructive">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Eliminar producto
                        </Button>
                    </div>

                    <Button type="button" @click="addPurchaseItem" variant="outline" class="w-full mt-2">
                        Agregar otro producto
                    </Button>

                    <Button type="submit" class="w-full mt-2" :disabled="purchaseForm.processing">
                        <LoaderCircle v-if="purchaseForm.processing" class="w-4 h-4 animate-spin" />
                        Registrar compra
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>