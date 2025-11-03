<script setup lang="ts">
import { defineProps, computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useSwal } from '@/composables/useSwal';
import { Table as ItemsTable } from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pagar Reservación',
        href: '/rreservations',
    },
];

const { reservation, items } = defineProps<{
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
const totalAmountInBs = computed(() => {
    if (!reservation || !reservation.total_amount) return 0;
    if (reservation.pay_type === 'Dolares') {
        return (reservation.total_amount || 0) * (reservation.exchange_rate || 1);
    }
    return reservation.total_amount || 0;
});

const advanceAmountInBs = computed(() => {
    if (!reservation || !reservation.advance_amount) return 0;
    if (reservation.pay_type === 'Dolares') {
        return (reservation.advance_amount || 0) * (reservation.exchange_rate || 1);
    }
    return reservation.advance_amount || 0;
});

const restAmountInBs = computed(() => {
    if (!reservation || !reservation.rest_amount) return 0;
    if (reservation.pay_type === 'Dolares') {
        return (reservation.rest_amount || 0) * (reservation.exchange_rate || 1);
    }
    return reservation.rest_amount || 0;
});

const totalAmountUsd = computed(() => {
    if (!reservation || !reservation.total_amount) return null;
    if (reservation.pay_type === 'Dolares') {
        return reservation.total_amount;
    }
    return null;
});

const advanceAmountUsd = computed(() => {
    if (!reservation || !reservation.advance_amount) return null;
    if (reservation.pay_type === 'Dolares') {
        return reservation.advance_amount;
    }
    return null;
});

const restAmountUsd = computed(() => {
    if (!reservation || !reservation.rest_amount) return null;
    if (reservation.pay_type === 'Dolares') {
        return reservation.rest_amount;
    }
    return null;
});

// Formulario de pago
const form = useForm({
    pay_type: '',
    notes: '',
});

const isSubmitting = ref(false);

const submitPayment = () => {
    if (!form.pay_type) {
        swal.fire('Error', 'Debes seleccionar un método de pago', 'error');
        return;
    }

    isSubmitting.value = true;
    form.post(route('rreservations.storePayment', reservation.id), {
        onSuccess: () => {
            swal.fire('Éxito', 'Venta registrada y reservación completada', 'success').then(() => {
                router.push(route('rsales.index'));
            });
        },
        onError: (errors: any) => {
            isSubmitting.value = false;
            console.error('Payment error:', errors);
            swal.fire('Error', errors.error || 'Ocurrió un error al procesar el pago', 'error');
        }
    });
};
</script>

<template>
    <Head title="Pagar Reservación" />

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
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-green-600 dark:text-green-400">Bs. {{ (totalAmountInBs || 0)?.toFixed(2) }}</p>
                        <p v-if="reservation?.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-green-600 dark:text-green-400 mt-2">${{ (totalAmountUsd || 0)?.toFixed(2) }}</p>
                    </div>

                    <!-- 3. Anticipo -->
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-blue-900 dark:text-blue-100">Anticipo</h3>
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-blue-600 dark:text-blue-400">Bs. {{ (advanceAmountInBs || 0)?.toFixed(2) }}</p>
                        <p v-if="reservation?.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-blue-600 dark:text-blue-400 mt-2">${{ (advanceAmountUsd || 0)?.toFixed(2) }}</p>
                    </div>

                    <!-- 4. Faltante -->
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/40 shadow">
                        <h3 class="font-bold text-sm sm:text-base md:text-lg mb-2 text-red-900 dark:text-red-100">Faltante a Pagar</h3>
                        <p class="text-2xl sm:text-3xl md:text-3xl font-extrabold text-red-600 dark:text-red-400">Bs. {{ (restAmountInBs || 0)?.toFixed(2) }}</p>
                        <p v-if="reservation?.pay_type === 'Dolares'" class="text-sm sm:text-base font-semibold text-red-600 dark:text-red-400 mt-2">${{ (restAmountUsd || 0)?.toFixed(2) }}</p>
                    </div>
                </div>

                <!-- Información Adicional (responsiva) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Tipo de Pago (Reservación)</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ reservation?.pay_type || 'N/A' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Tasa de Cambio</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ reservation?.exchange_rate || 'N/A' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 shadow">
                        <h3 class="font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2">Fecha de Creación</h3>
                        <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300">{{ reservation?.created_at ? new Date(reservation.created_at).toLocaleDateString('es-ES') : 'N/A' }}</p>
                    </div>
                </div>

                <!-- Lista de Productos -->
                <h3 class="font-bold text-xl sm:text-2xl mt-8 mb-4">Productos Reservados</h3>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-8">
                    <ItemsTable
                        :cadena="tableItems"
                        :cabeceras="['Producto', 'Cantidad', 'Total (Bs.)']"
                        :campos="['product_name', 'quantity_products', 'total_price']"
                        :agregar="false"
                        :acciones="[]"
                    />
                </div>

                <!-- Formulario de Pago -->
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-bold text-xl sm:text-2xl mb-6">Completar Pago</h3>
                    
                    <form @submit.prevent="submitPayment" class="space-y-6">
                        <!-- Método de Pago -->
                        <div class="flex flex-col">
                            <label for="pay_type" class="font-semibold mb-2 text-gray-700 dark:text-gray-300">
                                Método de Pago *
                            </label>
                            <select
                                id="pay_type"
                                v-model="form.pay_type"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">-- Seleccionar método de pago --</option>
                                <option value="Dolares">Dólares</option>
                                <option value="Bolivianos">Bolivianos</option>
                                <option value="QR">QR</option>
                            </select>
                            <span v-if="form.errors.pay_type" class="text-red-500 text-sm mt-1">{{ form.errors.pay_type }}</span>
                        </div>

                        <!-- Descripción/Notas -->
                        <div class="flex flex-col">
                            <label for="notes" class="font-semibold mb-2 text-gray-700 dark:text-gray-300">
                                Descripción (opcional)
                            </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Notas sobre la venta..."
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white resize-vertical"
                                rows="4"
                            ></textarea>
                            <span v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</span>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 mt-8">
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition"
                            >
                                {{ isSubmitting ? 'Procesando...' : 'Completar Pago y Crear Venta' }}
                            </button>
                            <a
                                :href="route('rreservations.index')"
                                class="px-6 py-2 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 transition inline-block text-center"
                            >
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
