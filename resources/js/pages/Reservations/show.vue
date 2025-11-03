<script setup lang="ts">
import { defineProps, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useSwal } from '@/composables/useSwal';
import { Table as ItemsTable } from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Detalle de la reservación',
        href: '/rreservations',
    },
];

const { reservation, items, currentUser } = defineProps<{
    reservation: any;
    items: any[];
    currentUser?: { id: number; role_id: number } | null;
}>();

const swal = useSwal();

// Propiedad computada para procesar los ítems para la tabla
const tableItems = computed(() => {
    return items.map(item => ({
        ...item,
        product_name: item.product_code || 'N/A',
        total_price: item.total_price.toFixed(2),
    }));
});

// Convertir montos según el tipo de pago
// Si es Dólares: total_amount está en USD, convertir a Bs para mostrar
// Si no es Dólares: total_amount está en Bs, mostrar tal cual
const totalAmountInBs = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        // total_amount está en USD, convertir a Bs
        return reservation.total_amount * reservation.exchange_rate;
    }
    // Ya está en Bs
    return reservation.total_amount;
});

const advanceAmountInBs = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        // advance_amount está en USD, convertir a Bs
        return reservation.advance_amount * reservation.exchange_rate;
    }
    // Ya está en Bs
    return reservation.advance_amount;
});

const restAmountInBs = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        // rest_amount está en USD, convertir a Bs
        return reservation.rest_amount * reservation.exchange_rate;
    }
    // Ya está en Bs
    return reservation.rest_amount;
});

// Mostrar en USD (solo si es pago en Dólares)
const totalAmountUsd = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        return reservation.total_amount;
    }
    return null;
});

const advanceAmountUsd = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        return reservation.advance_amount;
    }
    return null;
});

const restAmountUsd = computed(() => {
    if (reservation.pay_type === 'Dolares') {
        return reservation.rest_amount;
    }
    return null;
});

// Acciones para cada fila: solo para administradores
const acciones = computed(() => {
    if (currentUser && currentUser.role_id === 1) {
        return [
            {
                color: 'red',
                name: 'Eliminar',
                iconName: 'bx-trash',
                onClick: (item: any) => {
                    swal.fire({
                        title: '¿Eliminar producto?',
                        text: 'Este producto será eliminado de la reservación.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result: any) => {
                        if (result.isConfirmed) {
                            router.delete(route('rreservation_items.destroy', item.id), {
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
                }
            },
        ];
    }
    return [];
});
</script>

<template>
    <Head title="Detalle de Reservación" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6">
                
                <!-- Información General (4 columnas - responsiva) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- 1. Cliente -->
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-blue-900 dark:text-blue-100">Cliente</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300 line-clamp-2">{{ reservation.customer?.name }}</p>
                        <p v-if="reservation.customer?.phone" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">📱 {{ reservation.customer.phone }}</p>
                        <p v-if="reservation.customer?.email" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-1">📧 {{ reservation.customer.email }}</p>
                    </div>

                    <!-- 2. Total a Pagar -->
                    <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-green-900 dark:text-green-100">Total a Pagar</h3>
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-green-600 dark:text-green-400">Bs. {{ totalAmountInBs?.toFixed(2) }}</p>
                        <p v-if="reservation.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-green-600 dark:text-green-400 mt-2">${{ totalAmountUsd?.toFixed(2) }}</p>
                    </div>

                    <!-- 3. Anticipo -->
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-blue-900 dark:text-blue-100">Anticipo</h3>
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-blue-600 dark:text-blue-400">Bs. {{ advanceAmountInBs?.toFixed(2) }}</p>
                        <p v-if="reservation.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-blue-600 dark:text-blue-400 mt-2">${{ advanceAmountUsd?.toFixed(2) }}</p>
                    </div>

                    <!-- 4. Faltante -->
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-red-900 dark:text-red-100">Faltante</h3>
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-red-600 dark:text-red-400">Bs. {{ restAmountInBs?.toFixed(2) }}</p>
                        <p v-if="reservation.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-red-600 dark:text-red-400 mt-2">${{ restAmountUsd?.toFixed(2) }}</p>
                    </div>
                </div>

                <!-- Información Adicional (responsiva) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Tipo de Pago</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ reservation.pay_type }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Tasa de Cambio</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ reservation.exchange_rate }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Fecha de Creación</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ new Date(reservation.created_at).toLocaleDateString('es-ES') }}</p>
                    </div>
                </div>

                <!-- Lista de Productos -->
                <h3 class="font-bold text-xl sm:text-2xl mt-8 mb-4">Productos Reservados</h3>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <ItemsTable
                        :cadena="tableItems"
                        :cabeceras="['Producto', 'Cantidad', 'Total (Bs.)', 'Acciones']"
                        :campos="['product_name', 'quantity_products', 'total_price']"
                        :agregar="false"
                        :acciones="acciones"
                    />
                </div>

            </div>
        </div>
    </AppLayout>
</template>
