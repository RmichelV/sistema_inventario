<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
// PlaceholderPattern removed (unused)
import { Table as UserTable} from '@/components/ui/Table';
import type { User } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const { users, branches, currentBranch, currentUser } = defineProps<{
    users: User[];
    branches: { id: number; name: string }[];
    currentBranch: { id: number; name: string } | null;
    currentUser: { id: number; role_id: number };
}>();

// Formulario para cambiar la sucursal del usuario (solo visible para admin)
const form = useForm({ branch_id: '' });
const switchBranch = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const branchId = select.value;

    if (!branchId) return;

    form.branch_id = branchId;
    form.post(route('rusers.switchBranch'), {
        onSuccess: () => {
            // recargar para que el backend sirva los productos del nuevo branch
            window.location.reload();
        },
        onError: (errors: any) => {
            console.error('Failed to switch branch', errors);
        }
    });
};
const swal = useSwal();

function makeAcciones() {
    const baseActions: any[] = [
        {
            href: (item: any) => route('rusers.edit' , item.id),
            color: 'blue',
            name: 'Editar',
            iconName: 'bx-pencil',
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
                    title: '¿Eliminar empleado?',
                    text: `El empleado "${item.name}" será eliminado. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                }).then(async (result: any) => {
                    if (result.isConfirmed) {
                        // Usar Inertia router.delete para manejar CSRF y respuestas correctamente
                        router.delete(route('rusers.destroy', item.id), {
                            onSuccess: () => {
                                swal.fire('Eliminado', 'El empleado fue eliminado.', 'success').then(() => {
                                    // Recargar para mantener consistencia (podemos actualizar localmente si prefieres)
                                    window.location.reload();
                                });
                            },
                            onError: (errors: any) => {
                                console.error('Delete error', errors);
                                swal.fire('Error', 'Ocurrió un error al eliminar el empleado.', 'error');
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
                    <div class="text-lg font-semibold">
                        Empleados: <span class="font-bold">{{ currentBranch ? currentBranch.name : 'Sin sucursal asignada' }}</span>
                    </div>

                    <div>
                        <label v-if="currentUser && (currentUser.role_id === 1 || !currentBranch)" class="mr-2 font-medium">Seleccionar sucursal:</label>
                        <select v-if="currentUser && (currentUser.role_id === 1 || !currentBranch)" @change="switchBranch" :value="currentBranch ? currentBranch.id : ''" class="px-2 py-1 border rounded">
                            <option value="">-- Seleccionar --</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>

                <UserTable
                :cadena="users??[]"
                :cabeceras="['nombre','Cargo','Salario (Bs.)','Dirección','Teléfono','Ingreso','Acciones']"
                :campos="['name','role','base_salary','address','phone','hire_date']"
                :agregar="{ 
                    href: route('rusers.create'), 
                    color: 'green', 
                    name: 'Agregar Empleado',
                    iconName: 'bx-plus' }"
                :acciones="acciones"
            />
            </div>
        </div>
    </AppLayout>
</template>
