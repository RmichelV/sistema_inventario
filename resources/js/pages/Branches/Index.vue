<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Branch } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Table as BranchTable } from '@/components/ui/Table';
import Swal from 'sweetalert2';

const breadcrumbs: BreadcrumbItem[] = [
        {
                title: 'Sucursales',
                href: '/rbranches',
        },
];

const props = defineProps<{ branches: Branch[] }>();
const { branches } = props;

// Configuración de la tabla
const cabeceras = ['Nombre', 'Dirección','Acciones'];
const campos = ['name', 'address'];

function makeAcciones() {
    return [
        {
            href: (item: any) => route('rbranches.edit', item.id),
            color: 'blue',
            name: 'Editar',
            iconName: 'bx-pencil',
        },
            {
                color: 'red',
                name: 'Eliminar',
                iconName: 'bx-trash',
                onClick: (item: any) => {
                    Swal.fire({
                        title: '¿Eliminar sucursal?',
                        text: `La sucursal "${item.name}" será eliminada. Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                                const token = String(usePage().props.csrf_token || '');
                                const response = await fetch(route('rbranches.destroy', item.id), {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': token,
                                    },
                                    body: JSON.stringify({ _method: 'DELETE' }),
                                });

                                if (response.ok) {
                                    Swal.fire('Eliminada', 'La sucursal fue eliminada.', 'success').then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', 'Ocurrió un error al eliminar la sucursal.', 'error');
                                }
                                                    } catch {
                                                        Swal.fire('Error', 'Ocurrió un error al eliminar la sucursal.', 'error');
                                                    }
                        }
                    });
                }
            }
    ];
}

const acciones = makeAcciones();

const agregar = {
    href: route('rbranches.create'),
    color: 'green',
    name: 'Agregar Sucursal',
    iconName: 'bx-plus'
};
</script>

<template>
        <Head title="Sucursales" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="p-4">
                <BranchTable
                    :cadena="branches ?? []"
                    :cabeceras="cabeceras"
                    :campos="campos"
                    :agregar="agregar"
                    :acciones="acciones"
                />
            </div>
        </AppLayout>
</template>
