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
        title: 'Registro de compras',
        href: route('rpurchases.create'),
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
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div> -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                
                    <Form
                        method="post"
                        :action="route('rpurchases.store')"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="product_id">Elija un producto </Label>
                                <select id="product_id" name="product_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="" disabled selected>Seleccione un producto</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                                </select>
                                <InputError :message="errors.product_id" />
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="purchase_quantity">Cantidad de compra</Label>
                                <Input id="purchase_quantity" type="number" required autofocus :tabindex="1" autocomplete="purchase_quantity" name="purchase_quantity" placeholder="Ej. 500" min="0"/>
                                <InputError :message="errors.purchase_quantity" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="purchase_date">Fecha de compra</Label>
                                <Input id="purchase_date" type="date" required autofocus :tabindex="1" autocomplete="purchase_date" name="purchase_date" />
                                <InputError :message="errors.purchase_date" />
                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="processing">
                                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                                Registrar compra
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