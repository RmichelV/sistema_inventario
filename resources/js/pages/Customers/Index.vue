<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Table as CustomerTable } from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de clientes',
        href: '/rcustomers',
    },
];

const { customers } = defineProps<{
    customers: any[];
}>();

const swal = useSwal();

function makeAcciones() {
    const baseActions: any[] = [
        {
            href: (item: any) => route('rcustomers.edit', item.id),
            color: 'blue',
            name: 'Editar',
            iconName: 'bx-pencil',
        },
        {
            color: 'red',
            name: 'Eliminar',
            iconName: 'bx-trash',
            onClick: (item: any) => {
                swal.fire({
                    title: '¿Eliminar cliente?',
                    text: `El cliente "${item.name}" será eliminado. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                }).then(async (result: any) => {
                    if (result.isConfirmed) {
                        router.delete(route('rcustomers.destroy', item.id), {
                            onSuccess: () => {
                                swal.fire('Eliminado', 'El cliente fue eliminado.', 'success').then(() => {
                                    window.location.reload();
                                });
                            },
                            onError: (errors: any) => {
                                console.error('Delete error', errors);
                                swal.fire('Error', 'Ocurrió un error al eliminar el cliente.', 'error');
                            }
                        });
                    }
                });
            }
        },
    ];

    return baseActions;
}

const acciones = makeAcciones();
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold">
                        Lista de Clientes
                    </div>
                </div>

                <CustomerTable
                    :cadena="customers ?? []"
                    :cabeceras="['Nombre', 'Email', 'Teléfono', 'Dirección', 'Notas', 'Acciones']"
                    :campos="['name', 'email', 'phone', 'address', 'notes']"
                    :agregar="{
                        href: route('rcustomers.create'),
                        color: 'green',
                        name: 'Agregar Cliente',
                        iconName: 'bx-plus'
                    }"
                    :acciones="acciones"
                />
            </div>
        </div>
    </AppLayout>
</template>
