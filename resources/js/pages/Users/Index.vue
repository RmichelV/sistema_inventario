<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Table as UserTable} from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const props = defineProps({
  users: Array
});

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
                    :Cadena='users ?? []'
                    :Cabeceras="['ID', 'Nombre', 'Email']"
                    :campos="['id', 'name', 'email']"
                    :acciones="[
                        {
                            href: (item) => route('rusers.edit' , item.id),
                            color: 'blue',
                            nombre: 'Editar',
                            iconName: 'pencil',
                        },
                        {
                            href: (item) => `/users/${item.id}`,
                            color: 'green',
                            nombre: 'Ver',
                            iconName: 'eye',
                        }
                    ]"
                    :agregar="{ href: route('rusers.create'), color: 'green', nombre: 'Agregar Empleado', iconName: 'plus' }"
                    :agregarAccion="true"
               ></UserTable>
            </div>
        </div>
    </AppLayout>
</template>
