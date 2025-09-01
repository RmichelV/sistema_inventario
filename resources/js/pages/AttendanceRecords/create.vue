<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as UserTable} from '@/components/ui/Table';
import type { User } from '@/types'
import { computed } from 'vue'; 

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

const HoraEntradaMaxima = (17 * 60 + 0) ;
const HoraActualMinutos = (horaActual * 60 + MinutosActuales);

// 'acciones' es una propiedad computada que devuelve un array de botones.
// Su valor depende de la hora actual, pero no del usuario específico.
const acciones = computed(() => {
    if (HoraActualMinutos <= HoraEntradaMaxima) {
        return [
            {
                color: 'green',
                name: 'Presente',
                iconName: 'bx-check-double',
                onClick: (usuario: User) => {
                    marcarPresente(usuario, 'Presente');
                }
            },
            {
                color: 'green',
                name: 'Permiso',
                iconName: 'bx-check-double',
                 onClick: (usuario: User) => {
                    marcarPermiso(usuario, 'Permiso');
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
                    marcarTarde(usuario, 'Tarde');
                }
            },
        ];
    }
});

const marcarPresente = (usuario: User, tipoAccion: string) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendace_status: tipoAccion,
    },{
         onSuccess: () => {
            // Encuentra el usuario específico y cambia su estado
            const user = props.users.find(u => u.id === usuario.id);
            if (user) {
                user.asistencia_registrada = true;
            }
        }
    });
};

const marcarPermiso = (usuario: User, tipoAccion: string) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendace_status: tipoAccion,
        late_minutes: HoraActualMinutos - HoraEntradaMaxima
    },{
        onSuccess: () => {
            const user = props.users.find(u => u.id === usuario.id);
            if (user) {
                user.asistencia_registrada = true;
            }
        }
    });
};

const marcarTarde = (usuario: User, tipoAccion: string) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendace_status: tipoAccion,
        late_minutes: HoraActualMinutos - HoraEntradaMaxima
    },{
         onSuccess: () => {
            const user = props.users.find(u => u.id === usuario.id);
            if (user) {
                user.asistencia_registrada = true;
            }
        }
    });
};

console.log('Hora actual:', horaActual, 'Minutos actuales:', MinutosActuales);

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