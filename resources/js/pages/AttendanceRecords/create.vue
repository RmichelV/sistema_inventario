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

const HoraEntradaMaxima = (5 * 60 + 0) ;
const HoraActualMinutos = (horaActual * 60 + MinutosActuales);
const acciones = computed(() => {
    // La lógica de las acciones se encuentra dentro de esta función
    if (HoraActualMinutos <= HoraEntradaMaxima) {
        return [
            {
                color: 'green',
                name: 'Presente',
                iconName: 'bx-check-double',
                
            },
            {
                color: 'green',
                name: 'Permiso',
                iconName: 'bx-check-double',
                
            },
        ];

    } else {
        // Retorna un array vacío si la condición no se cumple
        return [
            {
                color: 'yellow',
                name: 'Tarde',
                iconName: 'bx-time-five',
                onClick: (usuario: User) => marcarAccion(usuario, 'Tarde'),
            },
        ];
    }
});


const marcarAccion = (usuario: User, tipoAccion: string) => {
    router.post(route('rattendance_records.store'), {
        user_id: usuario.id,
        attendace_status: tipoAccion,
        late_minutes: HoraActualMinutos - HoraEntradaMaxima
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
