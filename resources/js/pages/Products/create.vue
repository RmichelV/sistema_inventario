<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { LoaderCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Registrar Producto',
    href: route('rproducts.create'),
    },
];

// No props required for this form; defineProps removed to avoid unused prop warnings


// Creamos un array vacío para guardar los productos
const productsForm = useForm({
    products: [{
    name: '',
    code: '',
    img_product: null as File | null,
    quantity_in_stock: '',
    units_per_box: '',
    last_update: '',
    }],
});


const addProduct = () => {
    productsForm.products.push({
        name: '',
        code: '',
        img_product: null as File | null,
        quantity_in_stock: '',
        units_per_box: '',
        last_update: '',
    });
};

// Función para eliminar un producto
const removeProduct = (index: number) => {
    productsForm.products.splice(index, 1);
};

const submit = () => {
    productsForm.post(route('rproducts.store'), {
        forceFormData: true,
        onSuccess: () => {
            productsForm.reset();
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
                    <div v-for="(product, index) in productsForm.products" :key="index" class="grid gap-6 p-4 border-b border-gray-200">
                        <h3>Producto #{{ index + 1 }}</h3>
                        <div class="grid gap-2">
                            <Label :for="'name-' + index">Nombre del producto (Opcional)</Label>
                            <Input
                                :id="'name-' + index"
                                type="text"
                                autofocus
                                :tabindex="1"
                                :name="'products[' + index + '][name]'"
                                placeholder="Ej. Auriculares"
                                v-model="product.name"
                            />
                            <InputError :message="productsForm.errors[`products.${index}.name`]"/>
                        </div>
                        <div class="grid gap-2">
                            <Label :for="'code-' + index">Codigo</Label>
                            <Input
                                :id="'code-' + index"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                :name="'products[' + index + '][code]'"
                                placeholder="Ej. AUD432"
                                v-model="product.code"
                            />
                            <InputError :message="productsForm.errors[`products.${index}.code`]" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="'img_product-' + index">Imagen del producto</Label>
                            <Input
                                :id="'img_product-' + index"
                                type="file"
                                autofocus
                                :tabindex="1"
                                :name="'products[' + index + '][img_product]'"
                                accept="image/*"
                                @input="product.img_product = $event.target.files[0]"
                            />
                            <InputError :message="productsForm.errors[`products.${index}.img_product`]" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="'quantity_in_stock-' + index">Cantidad que ingresa / Cantidad en Stock</Label>
                                <Input
                                :id="'quantity_in_stock-' + index"
                                type="number"
                                required
                                autofocus
                                :tabindex="1"
                                :name="'products[' + index + '][quantity_in_stock]'"
                                placeholder="Ej. 500"
                                step="0.01"
                                min="0"
                                v-model="product.quantity_in_stock"
                            />
                            <InputError :message="productsForm.errors[`products.${index}.quantity_in_stock`]" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="'units_per_box-' + index">Unidades del producto por caja</Label>
                            <Input
                                :id="'units_per_box-' + index"
                                type="number"
                                required
                                autofocus
                                :tabindex="1"
                                :name="'products[' + index + '][units_per_box]'"
                                placeholder="Ej. 50"
                                step="0.01"
                                min="0"
                                v-model="product.units_per_box"
                            />
                            <InputError :message="productsForm.errors[`products.${index}.units_per_box`]" />
                        </div>
                        <Button v-if="productsForm.products.length > 1" type="button" @click="removeProduct(index)" class="mt-2 bg-red-500 hover:bg-red-600">Eliminar Producto</Button>
                    </div>
                        <Button type="button" @click="addProduct" class="w-full mt-2">Agregar otro producto</Button>
                        <Button type="submit" class="w-full mt-2" :disabled="productsForm.processing">
                        <LoaderCircle v-if="productsForm.processing" class="w-4 h-4 animate-spin" />
                        Registrar productos
                        </Button>
                    </form>
            </div>
        </div>
    </AppLayout>
</template>