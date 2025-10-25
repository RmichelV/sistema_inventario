<script setup lang="ts">
import { defineProps, ref, onMounted, computed, watchEffect } from 'vue'; // ✅ Added watchEffect
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Product, type BreadcrumbItem, SaleItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const { saleItem, product } = defineProps<{
    saleItem: SaleItem;
    product: Product;
}>();

const productName = ref('');
const soldQuantity = ref<number | null>(null);

onMounted(() => {
    productName.value = product.name;
    soldQuantity.value = saleItem.quantity_products;
});

type DevolutionForm = {
    product_id: number;
    quantity: number;
    reason: string;
    refund_amount: number;
    sale_item_id: number;
};

const form = useForm<DevolutionForm>({
    product_id: saleItem.product_id,
    quantity: 0,
    reason: '',
    refund_amount: 0,
    sale_item_id: saleItem.id,
});

const calculatedRefundAmount = computed(() => {
    if (!saleItem.total_price || !saleItem.quantity_products || !form.quantity) {
        return 0;
    }
    const unitPrice = saleItem.total_price / saleItem.quantity_products;
    return unitPrice * form.quantity;
});

const calculatedRefundAmountBs = computed(() => {
    if (!saleItem.total_price || !saleItem.quantity_products || !form.quantity) {
        return 0;
    }
    const unitPrice = saleItem.total_price / saleItem.quantity_products;
    return unitPrice * form.quantity;
});

// ✅ SOLUTION: Update the form's refund_amount whenever the calculated value changes.
watchEffect(() => {
    form.refund_amount = calculatedRefundAmount.value;
});
</script>


<template>
    <Head title="Devolución" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">
                <Head title="Devolución" />
                
                <h1 class="text-2xl font-bold mb-6">Registrar una devolución</h1>

                <form @submit.prevent="form.post(route('rdevolutions.store'))" class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <div class="grid gap-2">
                                <Label for="product_name">Producto</Label>
                                <Input 
                                    id="product_name" 
                                    type="text" 
                                    :value="productName" 
                                    disabled
                                />
                                <input type="hidden" name="product_id" :value="product.id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="sold_quantity">Cantidad Vendida</Label>
                                <Input 
                                    id="sold_quantity" 
                                    type="number" 
                                    :value="soldQuantity"
                                    disabled
                                />
                            </div>
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="quantity">Cantidad a devolver</Label>
                                <Input 
                                id="quantity" 
                                type="number" 
                                required 
                                :tabindex="1" 
                                placeholder="Ej. 1" 
                                v-model.number="form.quantity"
                                    :max="saleItem.quantity_products"
                                min="1"
                            />
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="reason">Motivo de la devolución</Label>
                            <Input 
                                id="reason" 
                                type="text" 
                                required 
                                :tabindex="2" 
                                placeholder="Ej. Producto dañado" 
                                v-model="form.reason"
                            />
                            <InputError :message="form.errors.reason" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- <div class="grid gap-2">
                                <Label for="refund_amount">Monto a reembolsar en $us</Label>
                                <Input 
                                    id="refund_amount" 
                                    type="text" 
                                    disabled 
                                    :value="calculatedRefundAmount.toFixed(2)"
                                />
                                <InputError :message="form.errors.refund_amount" />
                            </div> -->
                            <div class="grid gap-2">
                                <Label for="refund_amountbs">Monto a reembolsar en Bs.</Label>
                                <Input 
                                    id="refund_amount" 
                                    type="text" 
                                    disabled 
                                    :value="calculatedRefundAmountBs.toFixed(2)"
                                />
                                </div>
                        </div>
                  
                        <input type="hidden" name="sale_item_id" :value="saleItem.id" />
                        <input type="hidden" name="refund_amount" :value="form.refund_amount" />
                        
                        <Button type="submit" class="w-full mt-2" tabindex="3" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Registrar devolución
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>