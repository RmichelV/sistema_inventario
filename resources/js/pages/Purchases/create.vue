<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Product } from '@/types';
import { SelectSearch } from '@/components/ui/SelectSearch';
import { LoaderCircle, Trash2 } from 'lucide-vue-next';

// This is the purchase form, now using useForm to manage a list of items.
const purchaseForm = useForm({
    purchase_date: new Date().toISOString().slice(0, 10),
    items: [{
        product_id: null,
        purchase_quantity: '',
    }],
});

const { products } = defineProps<{
    products: Product[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Registro de compras',
        href: route('rpurchases.create'),
    },
];

const addPurchaseItem = () => {
    purchaseForm.items.push({
        product_id: null,
        purchase_quantity: '',
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
                                v-model="item.purchase_quantity"
                            />
                            <InputError :message="purchaseForm.errors[`items.${index}.purchase_quantity`]"/>
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