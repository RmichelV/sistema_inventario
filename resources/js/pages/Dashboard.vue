<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { defineProps, ref, computed } from 'vue';
import { Usd_exchange_rate, Product } from '@/types';
import {SelectSearch} from '@/components/ui/SelectSearch';
import { Input } from '@/components/ui/input';
import { ActionButton } from '@/components/ui/ActionButton';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    usd: Usd_exchange_rate;
    products: Product[];
}>();

const selectedProduct = ref<number | null>(null);

// 🚨 CAMBIO AQUÍ: Propiedad computada para encontrar el producto seleccionado
const currentProduct = computed(() => {
    if (selectedProduct.value === null) {
        return null;
    }
    // Busca en el array `props.products` por el ID
    return props.products.find(p => p.id === selectedProduct.value) || null;
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <h1 class="text-2xl font-bold">Buscar Producto</h1>
                       <SelectSearch
                            v-model="selectedProduct"
                            :options="props.products"
                            :searchKeys="['name', 'code']"
                            placeholder="Buscar un producto por nombre o código..."
                            labelKey="code"
                        />
                        <h1 class="text-2xl font-bold"> Cantidad</h1>
                        <Input id="quantity" type="number" required autofocus :tabindex="1" autocomplete="quantity" name="quantity" placeholder="Cantidad" defaultValue="1" />
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <h1 class="text-xl font-bold">Mi Producto</h1>
                        <div v-if="currentProduct">
                            <p><strong>ID:</strong> {{ currentProduct.id }}</p>
                            <p><strong>Nombre:</strong> {{ currentProduct.name }}</p>
                            <p><strong>Código:</strong> {{ currentProduct.code }}</p>
                        </div>
                        <div v-else>
                            <p class="text-gray-500 mt-2">No hay producto seleccionado.</p>
                        </div>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <div class="absolute inset-0 flex flex-col items-center justify-center">
                       <p class="mt-2 text-xl">
                            Cambio Actual 
                       </p>
                        <h1 class="text-5xl font-bold">
                            Bs. {{props.usd.exchange_rate}}
                        </h1>
                        <div>
                            <ActionButton
                                color="green"
                                iconName="bx-pencil"
                                :href="route('rusdexchangerates.edit',1)"
                                name="Editar"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>