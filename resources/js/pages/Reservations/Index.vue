<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Table as ReservationTable } from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de reservaciones',
        href: '/rreservations',
    },
];

const { reservations, currentUser, branches, currentBranch } = defineProps<{
    reservations: any[];
    currentUser: { id: number; role_id: number };
    branches?: { id: number; name: string }[];
    currentBranch?: { id: number; name: string } | null;
}>();

const swal = useSwal();

// Método para cambiar la branch del usuario autenticado (solo role_id === 1 lo verá)
const form = useForm({ branch_id: '' });
const switchBranch = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const branchId = select.value;

    if (!branchId) return;

    form.branch_id = branchId;
    form.post(route('rusers.switchBranch'), {
        onSuccess: () => {
            // recargar para que el backend sirva las reservaciones del nuevo branch
            window.location.reload();
        },
        onError: (errors: any) => {
            console.error('Failed to switch branch', errors);
        }
    });
};

function makeAcciones() {
    const baseActions: any[] = [
        {
            href: (item: any) => route('rreservations.show', item.id),
            color: 'blue',
            name: 'Detalles',
            iconName: 'bx-detail',
        },
        {
            href: (item: any) => route('rreservations.payment', item.id),
            color: 'green',
            name: 'Pagar',
            iconName: 'bx-credit-card',
        },
    ];

    // Solo admin puede eliminar
    if (currentUser && currentUser.role_id === 1) {
        baseActions.push({
            color: 'red',
            name: 'Eliminar',
            iconName: 'bx-trash',
            onClick: (item: any) => {
                swal.fire({
                    title: '¿Eliminar reservación?',
                    text: `La reservación será eliminada. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                }).then(async (result: any) => {
                    if (result.isConfirmed) {
                        router.delete(route('rreservations.destroy', item.id), {
                            onSuccess: () => {
                                swal.fire('Eliminado', 'La reservación fue eliminada.', 'success').then(() => {
                                    window.location.reload();
                                });
                            },
                            onError: (errors: any) => {
                                console.error('Delete error:', errors);
                                swal.fire('Error', 'Ocurrió un error al eliminar la reservación.', 'error');
                            }
                        });
                    }
                });
            }
        });
    }

    return baseActions;
}

const acciones = makeAcciones();
</script>

<template>
    <Head title="Reservaciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold">
                        Sucursal: <span class="font-bold">{{ currentBranch ? currentBranch.name : 'Sin sucursal asignada' }}</span>
                    </div>

                    <div>
                        <label v-if="currentUser && currentUser.role_id === 1" class="mr-2 font-medium">Seleccionar sucursal:</label>
                        <select v-if="currentUser && currentUser.role_id === 1" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                            <option value="">-- Seleccionar --</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>

                <ReservationTable
                    :cadena="reservations ?? []"
                    :cabeceras="['Cliente', 'Monto Total (Bs.)', 'Anticipo (Bs.)', 'Saldo (Bs.)', 'Tipo de Pago', 'Acciones']"
                    :campos="['customer_name', 'total_amount', 'advance_amount', 'rest_amount', 'pay_type']"
                    :agregar="{
                        href: route('rreservations.create'),
                        color: 'green',
                        name: 'Nueva Reservación',
                        iconName: 'bx-plus'
                    }"
                    :acciones="acciones"
                />
            </div>
        </div>
    </AppLayout>
</template>
