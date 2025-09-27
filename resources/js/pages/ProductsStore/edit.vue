<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { useForm, Head, usePage } from '@inertiajs/vue3'; // Asegúrate de importar useForm
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
import AuthBase from '@/layouts/AuthLayout.vue';


import type {  Product,ProductStore } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const props = defineProps<{
    productStore: ProductStore
    products: Product[]
}>();

console.log(props)

// 1. Inicializa el formulario con los datos del usuario
const form = useForm({
    product_id: props.productStore.product_id,
    quantity: props.productStore.quantity,
    unit_price: props.productStore.unit_price,
});

// 2. Define el método para enviar la actualización
const submit = () => {
    // Usa form.put() para enviar una solicitud PUT y el id del usuario en la URL
    form.put(route('rproductstores.update', props.productStore.id), {
        onSuccess: () => {
            
        },
    });
};
</script>

<template>
    <Head title="Empleados" />

    <AppLayout :breadcrumbs="breadcrumbs">
       <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <AuthBase title="Editar empleado" >
                    <Head title="Registro" />

                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid gap-6">

                           <div class="grid gap-2">
                                <Label for="product_id">Producto</Label>
                                <select 
                                    id="product_id" 
                                    v-model="form.product_id" 
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" 
                                    required 
                                    disabled
                                >
                                    <option value="" disabled>Seleccione un Producto</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                                </select>
                                <InputError :message="form.errors.product_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="quantity">Cantidad de unidades en la tienda</Label>
                                <Input readonly id="quantity" type="text" required autofocus :tabindex="1" autocomplete="base_salary" v-model="form.quantity" placeholder="Ej. 500.00" step="0.01" min="0"/>
                                <InputError :message="form.errors.quantity" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="unit_price_wholesale">Precio unitario en Dolares ($us) </Label>
                                <Input id="unit_price_wholesale" type="number" required autofocus :tabindex="1" autocomplete="unit_price" v-model="form.unit_price" placeholder="Ej. 500.00" step="0.01" min="0"/>
                                <InputError :message="form.errors.unit_price" />
                            </div>
                    

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                                Guardar cambios
                            </Button>
                        </div>
                    </form>
                </AuthBase>
            </div>
        </div>
    </AppLayout>
</template>