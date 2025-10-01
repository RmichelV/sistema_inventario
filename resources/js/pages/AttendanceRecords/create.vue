<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as UserTable} from '@/components/ui/Table';
import type { User } from '@/types'
import { computed, onMounted, watch } from 'vue'; 
import { usePage } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal'; 

// Lógica de SweetAlert2
const page = usePage();
const swal = useSwal(); // Instancia el composable

// Función unificada para manejar la visualización de alertas
const handleAlerts = () => {
    // 1. Manejar mensajes de ERROR personalizados (del controlador - try/catch)
    if (page.props.flash?.error) {
        swal.fire({
            icon: 'error', 
            title: '¡Error!',  
            text: page.props.flash.error,
        });
        // Usamos 'undefined' en lugar de 'null' para cumplir con el tipo de TS
        page.props.flash.error = undefined; 
    }

    // 2. Manejar mensajes de ÉXITO
    if (page.props.flash?.success) {
         swal.fire({
            icon: 'success', 
            title: '¡Éxito!', 
            text: page.props.flash.success,
        });
        // Usamos 'undefined' en lugar de 'null' para cumplir con el tipo de TS
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

const props = defineProps<{
    users: User[];
}>();



const now = new Date();
const horaActual = now.getHours();
const MinutosActuales = now.getMinutes();
const attendance_date_formatted = now.toISOString().split('T')[0]; // Ejemplo: '2025-10-01'
const check_in_at_formatted = now.toTimeString().split(' ')[0]; 
const HoraEntradaMaxima = (10 * 60 + 0) ;
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
        ];
    }
});

// Nota: Hemos quitado los bloques onSuccess para confiar en el watch.
// La actualización de asisitencia_registrada se puede realizar después
// de la alerta de éxito si es necesario, pero por ahora dependemos de Inertia.

const marcarPresente = (usuario: User) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendance_status: "Presente",
        attendance_date: attendance_date_formatted,
        check_in_at: check_in_at_formatted,
        check_out_at: null,
        minutes_worked: null,
    });
};

const marcarPermiso = (usuario: User) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendance_status: "Permiso",
        attendance_date: attendance_date_formatted,
        late_minutes: HoraActualMinutos - HoraEntradaMaxima
    });
};

const marcarTarde = (usuario: User) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendance_status: "Tarde",
        attendance_date: attendance_date_formatted,
        late_minutes: HoraActualMinutos - HoraEntradaMaxima
    });
};

</script>

<template>
    <Head title="Empleados" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <UserTable
                    :cadena="users??[]"
                    :cabeceras="['nombre','acciones']"
                    :campos="['name']"
                    :agregar="false"
                    :acciones="acciones"
                />
            </div>
        </div>
    </AppLayout>
</template>
