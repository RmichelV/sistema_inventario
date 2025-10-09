<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Product, Purchase } from '@/types'

// Agrega las importaciones necesarias
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal'; // Importa el composable


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Historial de compras',
        href: '/rpurchases',
    },
];

const props = defineProps<{
    products: Product[];
    purchases: Purchase[];
    
}>();

// Lógica de SweetAlert2
const page = usePage();
const swal = useSwal(); // Instancia el composable

onMounted(() => {
    // Si existe un mensaje flash de éxito, muestra la alerta
    if (page.props.flash.success) {
        // Usa el composable directamente
        swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: page.props.flash.success,
        });
    }
});
</script>

<template>
    <Head title="Empleados" />

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
            
            <ProductTable
                :cadena="purchases??[]"
                :cabeceras="[
                    
                    'Producto',
                    'Cantidad comprada',
                    'Fecha de compra',
                ]"
                :campos="[
                        
                        'product',
                        'purchase_quantity',
                        'purchase_date',
                        ]"
                :agregar="{
                    href: route('rpurchases.create'), 
                    color: 'green', 
                    name: 'Registrar compra',
                    iconName: 'bx-plus' }"
                :acciones="[
                ]"
            />
            </div>
        </div>
    </AppLayout>
</template>
