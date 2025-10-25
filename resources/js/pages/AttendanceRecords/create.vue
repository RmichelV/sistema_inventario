<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Table as UserTable} from '@/components/ui/Table';
import type { User } from '@/types'
import { computed, onMounted, watch } from 'vue'; 
import { usePage } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal'; 

// Lógica de SweetAlert2
const page = usePage();
const swal = useSwal(); // Instancia el composable

// Función unificada para manejar la visualización de alertas con botones Bootstrap
const handleAlerts = () => {
    // 1. Manejar mensajes de ERROR personalizados (del controlador - try/catch)
    if (page.props.flash?.error) {
        swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: page.props.flash.error,
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
        });
        page.props.flash.error = undefined;
    }

    // 2. Manejar mensajes de ÉXITO
    if (page.props.flash?.success) {
        swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: page.props.flash.success,
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
        });
        page.props.flash.success = undefined;
    }

    // 3. Manejar errores de VALIDACIÓN de formulario (cuando falla el FormRequest)
    const validationErrors = Object.values(page.props.errors ?? {}).flat();
    if (validationErrors.length > 0) {
        const errorText = validationErrors.join('<br>');
        swal.fire({
            icon: 'warning',
            title: 'Datos Faltantes o Inválidos',
            html: errorText,
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
        });
    }
};

// Observamos TODO el objeto page.props con deep: true
watch(
    () => page.props, 
    (newProps) => {
        // Ejecutamos la función si el objeto flash está presente y tiene contenido.
        if (newProps.flash && (newProps.flash.success || newProps.flash.error)) {
            handleAlerts();
        }
    }, 
    { deep: true } 
);


onMounted(() => {
    // Ejecutar en la carga inicial
    handleAlerts();
});


// fin sweet alert
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

// Formulario para cambiar la sucursal del usuario (solo visible para admin o si no tiene branch)
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

// Variables estáticas que SÍ deben estar fuera de la función (o mejor, como computed)
// **Se mantienen las variables aquí para los botones de 'Presente'/'Tarde'**

const now = new Date();
const horaActual = now.getHours();
const MinutosActuales = now.getMinutes();
const attendance_date_formatted = now.toISOString().split('T')[0]; // Ejemplo: '2025-10-01'
const check_formatted = now.toTimeString().split(' ')[0]; 
const HoraEntradaMaxima = ( 9 * 60 + 10) ;
const HoraActualMinutos = (horaActual * 60 + MinutosActuales);

const acciones = computed(() => {
    if (HoraActualMinutos <= HoraEntradaMaxima) {
        return [
            {
                color: 'green',
                name: 'Presente',
                iconName: 'bx-check-double',
                onClick: (usuario: User) => {
                    marcarPresente(usuario);
                }
            },
            {
                color: 'green',
                name: 'Permiso',
                iconName: 'bx-check-double',
                 onClick: (usuario: User) => {
                    marcarPermiso(usuario);
                 }
            },
            {
                color: 'red',
                name: 'Salida',
                iconName: 'bx-check-double',
                 onClick: (usuario: User) => {
                    marcarSalida(usuario);
                 }
            },
        ];
    } else {
        return [
            {
                color: 'yellow',
                name: 'Tarde',
                iconName: 'bx-time-five',
                onClick: (usuario: User) => {
                    marcarTarde(usuario);
                }
            },
            {
                color: '(#f59e0b)',
                name: 'Permiso',
                iconName: 'bx-check-double',
                 onClick: (usuario: User) => {
                    marcarPermiso(usuario);
                 }
            },
            {
                color: 'red',
                name: 'Salida',
                iconName: 'bx-check-double',
                 onClick: (usuario: User) => {
                    marcarSalida(usuario);
                 }
            },
        ];
    }
   
});

// Nota: Hemos quitado los bloques onSuccess para confiar en el watch.
// La actualización de asisitencia_registrada se puede realizar después
// de la alerta de éxito si es necesario, pero por ahora dependemos de Inertia.

const marcarPresente = (usuario: User) => {
    swal.fire({
        title: `¿Registrar a ${usuario.name} como presente?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true,
    }).then((result: any) => {
        if (result.isConfirmed) {
            router.post(route('rattendance_records.store'), {
                user_id: usuario.id,
                attendance_status: "Presente",
                attendance_date: attendance_date_formatted,
                check_in_at: check_formatted,
                check_out_at: null,
                minutes_worked: null,
            });
            swal.fire({
                title: '¡Registrado!',
                text: 'La asistencia ha sido registrada como presente.',
                icon: 'success',
                confirmButtonText: 'Cerrar',
            });
        } else if (result.dismiss === swal.DismissReason.cancel) {
            swal.fire({
                title: 'Cancelado',
                text: 'No se registró la asistencia.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
        }
    });
};

const marcarPermiso = (usuario: User) => {
    swal.fire({
        title: `¿Registrar permiso para ${usuario.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true,
    }).then((result: any) => {
        if (result.isConfirmed) {
            router.post(route('rattendance_records.permition'), {
                user_id: usuario.id,
                attendance_status: "Permiso",
                attendance_date: attendance_date_formatted,
                check_in_at: null,
                check_out_at: null,
                minutes_worked: 0,
            });
            swal.fire({
                title: '¡Registrado!',
                text: 'El permiso ha sido registrado.',
                icon: 'success',
                confirmButtonText: 'Cerrar',
            });
        } else if (result.dismiss === swal.DismissReason.cancel) {
            swal.fire({
                title: 'Cancelado',
                text: 'No se registró el permiso.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
        }
    });
};

const marcarTarde = (usuario: User) => {
    swal.fire({
        title: `¿Registrar a ${usuario.name} como tarde?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true,
    }).then((result: any) => {
        if (result.isConfirmed) {
            router.post(route('rattendance_records.store'), {
                user_id: usuario.id,
                attendance_status: "Tarde",
                attendance_date: attendance_date_formatted,
                check_in_at: check_formatted,
                check_out_at: null,
                minutes_worked: null,
            });
            swal.fire({
                title: '¡Registrado!',
                text: 'La asistencia ha sido registrada como tarde.',
                icon: 'success',
                confirmButtonText: 'Cerrar',
            });
        } else if (result.dismiss === swal.DismissReason.cancel) {
            swal.fire({
                title: 'Cancelado',
                text: 'No se registró la asistencia.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
        }
    });
};

const marcarSalida = (usuario: User) => {
    // 1. OBTENER LA HORA EXACTA DEL CLIC
    const nowOnCheckOut = new Date();
    let check_out_formatted = nowOnCheckOut.toTimeString().split(' ')[0];

    // Si la hora actual es igual o mayor a las 21:00, marcar 21:00 como hora de salida
    const horaActualSalida = nowOnCheckOut.getHours();
    const minutosActualSalida = nowOnCheckOut.getMinutes();
    if (horaActualSalida > 21 || (horaActualSalida === 21 && minutosActualSalida >= 0)) {
        check_out_formatted = '21:00:00';
    }

    // Confirmación antes de marcar salida
    swal.fire({
        title: `¿Registrar salida para ${usuario.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar salida!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true,
    }).then((result: any) => {
        if (result.isConfirmed) {
            router.put(route('rattendance_records.update', usuario.id), {
                user_id: usuario.id,
                attendance_date: attendance_date_formatted,
                check_out_at: check_out_formatted,
            });
            swal.fire({
                title: '¡Salida registrada!',
                text: 'La salida ha sido registrada correctamente.',
                icon: 'success',
                confirmButtonText: 'Cerrar',
            });
        } else if (result.dismiss === swal.DismissReason.cancel) {
            swal.fire({
                title: 'Cancelado',
                text: 'La salida no fue registrada.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
        }
    });
};
</script>

<template>
    <Head title="Registrar Asistencias" />  
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
                        Lista de empleados: <span class="font-bold">{{ currentBranch ? currentBranch.name : 'Sin sucursal asignada' }}</span>
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
                    :cabeceras="['nombre','acciones']"
                    :campos="['name']"
                    :agregar="currentUser && currentUser.role_id === 1 ? { href: '/rattendance_records', color: 'green', name: 'Registros de asistencias', iconName: 'bx-receipt' } : false"
                    :acciones="acciones"
                />
            </div>
        </div>
    </AppLayout>
</template>
