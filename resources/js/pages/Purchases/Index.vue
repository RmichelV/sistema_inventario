<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table as ProductTable} from '@/components/ui/Table';
import type { Purchase } from '@/types'

// Agrega las importaciones necesarias
import { onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal'; // Importa el composable


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Historial de compras',
        href: '/rpurchases',
    },
];

const { purchases, branches, currentBranch, currentUser } = defineProps<{
    purchases: Purchase[];
    branches: { id: number; name: string }[];
    currentBranch: { id: number; name: string } | null;
    currentUser: { id: number; role_id: number };
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
                    <div class="text-lg font-semibold">Historial de compras</div>
                    <div>
                        <label v-if="currentUser && currentUser.role_id === 1" class="mr-2 font-medium">Seleccionar sucursal:</label>
                        <select v-if="currentUser && currentUser.role_id === 1" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                            <option value="">-- Seleccionar --</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>

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
