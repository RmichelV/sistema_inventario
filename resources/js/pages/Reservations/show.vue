<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Trash2 } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de reservaciones',
        href: '/rreservations',
    },
];

const { reservation, items } = defineProps<{
    reservation: any;
    items: any[];
    currentUser: { id: number; role_id: number };
}>();

const swal = useSwal();

const deleteItem = (itemId: number) => {
    swal.fire({
        title: '¿Eliminar producto?',
        text: 'Este producto será eliminado de la reservación.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result: any) => {
        if (result.isConfirmed) {
            router.delete(route('rreservation_items.destroy', itemId), {
                onSuccess: () => {
                    swal.fire('Eliminado', 'El producto fue eliminado.', 'success').then(() => {
                        window.location.reload();
                    });
                },
                onError: () => {
                    swal.fire('Error', 'Ocurrió un error al eliminar el producto.', 'error');
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Detalle de Reservación" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">
                
                <!-- Información General de la Reservación -->
                <div class="grid gap-6 mb-8">
                    <h1 class="text-3xl font-bold">Detalle de Reservación</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-indigo-50 dark:bg-indigo-900 rounded-lg border border-indigo-200 dark:border-indigo-700">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Cliente</p>
                            <p class="text-lg font-bold">{{ reservation.customer?.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Email del Cliente</p>
                            <p class="text-lg font-bold">{{ reservation.customer?.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tipo de Pago</p>
                            <p class="text-lg font-bold">{{ reservation.pay_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tasa de Cambio</p>
                            <p class="text-lg font-bold">{{ reservation.exchange_rate }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Monto Total (Bs.)</p>
                            <p class="text-lg font-bold text-green-700 dark:text-green-300">Bs. {{ reservation.total_amount?.toFixed(2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Anticipo (Bs.)</p>
                            <p class="text-lg font-bold text-blue-700 dark:text-blue-300">Bs. {{ reservation.advance_amount?.toFixed(2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Restante (Bs.)</p>
                            <p class="text-lg font-bold text-red-700 dark:text-red-300">Bs. {{ reservation.rest_amount?.toFixed(2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fecha de Creación</p>
                            <p class="text-lg font-bold">{{ new Date(reservation.created_at).toLocaleDateString('es-ES') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Productos -->
                <div class="grid gap-6">
                    <h2 class="text-2xl font-bold">Productos Reservados</h2>
                    
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold">Código</th>
                                    <th class="px-6 py-3 text-left font-semibold">Nombre</th>
                                    <th class="px-6 py-3 text-center font-semibold">Cantidad</th>
                                    <th class="px-6 py-3 text-right font-semibold">Total (Bs.)</th>
                                    <th class="px-6 py-3 text-center font-semibold">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-3">{{ item.product_code }}</td>
                                    <td class="px-6 py-3">{{ item.product_name }}</td>
                                    <td class="px-6 py-3 text-center">{{ item.quantity_products }}</td>
                                    <td class="px-6 py-3 text-right font-semibold">Bs. {{ item.total_price?.toFixed(2) }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <button
                                            @click="deleteItem(item.id)"
                                            class="flex items-center justify-center gap-2 px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 rounded-md transition"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                            <span class="text-xs font-semibold">Eliminar</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen de Totales al pie -->
                    <div class="grid gap-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total de Items:</span>
                            <span>{{ items.length }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-green-700 dark:text-green-300">
                            <span>Monto Total:</span>
                            <span>Bs. {{ reservation.total_amount?.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-blue-700 dark:text-blue-300">
                            <span>Anticipo Pagado:</span>
                            <span>Bs. {{ reservation.advance_amount?.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-red-700 dark:text-red-300 border-t border-gray-300 dark:border-gray-600 pt-3">
                            <span>Saldo Pendiente:</span>
                            <span>Bs. {{ reservation.rest_amount?.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
