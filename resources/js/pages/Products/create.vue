<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import type { Product} from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Registro de productos',
        href: route('rproducts.create'),
    },
];

const props = defineProps<{
    products:Product[];
}>();

</script>

<template>
    <Head title="Productos" />

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
                
                    <Form
                        method="post"
                        :action="route('rproducts.store')"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="name">Nombre del producto</Label>
                                <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name" placeholder="Ej. Auriculares"/>
                                <InputError :message="errors.name"/>
                            </div>
                            <div class="grid gap-2">
                                <Label for="code">Codigo</Label>
                                <Input id="code" type="text" required autofocus :tabindex="1" autocomplete="code" name="code" placeholder="Ej. AUD432"/>
                                <InputError :message="errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="img_product">Imagen del producto</Label>
                                <Input id="img_product" type="file"  required autofocus :tabindex="1" autocomplete="img_product" name="img_product" accept="image/*"/>
                                <InputError :message="errors.img_product" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="quantity_in_stock">Cantidad que ingresa / Cantidad en Stock</Label>
                                <Input id="aquantity_in_stock" type="number" required autofocus :tabindex="1" autocomplete="quantity_in_stock" name="quantity_in_stock" placeholder="Ej. 500" step="0.01" min="0"/>
                                <InputError :message="errors.quantity_in_stock" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="units_per_box">Unidades del producto por caja</Label>
                                <Input id="units_per_box" type="number" required autofocus :tabindex="1" autocomplete="units_per_box" name="units_per_box" placeholder="Ej. 50" step="0.01" min="0"/>
                                <InputError :message="errors.units_per_box" />
                            </div>

                             <!-- <div class="grid gap-2">
                                <Label for="minimum_wholesale_quantity">Cantidad minima para el precio por mayor</Label>
                                <Input id="minimum_wholesale_quantity" type="number" required autofocus :tabindex="1" autocomplete="minimum_wholesale_quantity" name="minimum_wholesale_quantity" placeholder="Ej. 12" step="0.01" min="0"/>
                                <InputError :message="errors.minimum_wholesale_quantity" />
                            </div>

                           <div class="grid gap-2">
                                <Label for="currency_type">Tipo de moneda</Label>
                                <select id="currency_type" name="currency_type" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione un tipo de moneda</option>
                                    <option value="Bolivianos"> Bolivianos</option>
                                    <option value="Dolares"> Dolares</option>
                                </select>
                                <InputError :message="errors.currency_type" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="unit_price_wholesale">Precio unitario al por mayor</Label>
                                <Input id="unit_price_wholesale" type="number" required autofocus :tabindex="1" autocomplete="unit_price_wholesale" name="unit_price_wholesale" placeholder="Ej. 9.99" step="0.01" min="0"/>
                                <InputError :message="errors.unit_price_wholesale" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="unit_price_retail">Precio unitario al por menor</Label>
                                <Input id="unit_price_retail" type="number" required autofocus :tabindex="1" autocomplete="unit_price_retail" name="unit_price_retail" placeholder="Ej. 9.99" step="0.01" min="0"/>
                                <InputError :message="errors.unit_price_retail" />
                            </div>
 -->

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="processing">
                                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                                Registrar producto
                            </Button>
                        </div>

                        <!-- <div class="text-sm text-center text-muted-foreground">
                            Already have an account?
                            <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                        </div> -->
                    </Form>
            </div>
        </div>
    </AppLayout>
</template>