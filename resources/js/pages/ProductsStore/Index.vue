<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Product, ProductStore } from '@/types'

// Agregar imports y composables para swal y cambio de sucursal
import { onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de productos en la tienda',
        href: '/rpurchases',
    },
];

const { products, productstores, branches, currentBranch, currentUser } = defineProps<{
    products: Product[];
    productstores: ProductStore[];
    branches: { id: number; name: string }[];
    currentBranch: { id: number; name: string } | null;
    currentUser: { id: number; role_id: number };
}>();

// SweetAlert2 y switchBranch
const page = usePage();
const swal = useSwal();

onMounted(() => {
    if (page.props.flash.success) {
        swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: page.props.flash.success,
        });
    }
});

const form = useForm({ branch_id: '' });
const switchBranch = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const branchId = select.value;

    if (!branchId) return;

    form.branch_id = branchId;
    form.post(route('rusers.switchBranch'), {
        onSuccess: () => {
            window.location.reload();
        },
        onError: (errors: any) => {
            console.error('Failed to switch branch', errors);
        }
    });
};
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
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div>P/U $us: Precio Unitario en Dolares</div>
                    <div>P/U BS: Precio Unitario en Bolivianos</div>
                    <div>P/U BS + 1.1%: Precio Unitario en Bolivianos + 1.1% del precio</div>
                </div>

                <div>
                    <label v-if="currentUser && currentUser.role_id === 1" class="mr-2 font-medium">Seleccionar sucursal:</label>
                    <select v-if="currentUser && currentUser.role_id === 1" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                        <option value="">-- Seleccionar --</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>
            <ProductTable
                :cadena="productstores??[]"
                :cabeceras="[
                   
                    'Producto',
                    'Cantidad',
                    'P/U $us.',
                    'P/U Bs.',
                    'P/U + 1.1%',
                    'Acciones'
                ]"
                :campos="[
                       
                        'product_id',
                        'quantity',
                        'unit_price',
                        'unit_price_bs',
                        'porcentaje',
                        ]"
                :agregar="{
                    href: route('rproductstores.create'), 
                    color: 'green', 
                    name: 'Agregar Producto a Tienda',
                    iconName: 'bx-plus' }"
                 :acciones="[
                    {
                        href: (item) => route('rproductstores.edit' , item.id),
                        color: 'blue',
                        name: 'Editar',
                        iconName: 'bx-pencil',
                    }
                ]"
                :searchSelectConfig="{
                options: products, 
                valueKey: 'id',
                labelKey: 'code',
                placeholder: 'Buscar productos...'
                }"
            />
            </div>
        </div>
    </AppLayout>
</template>
