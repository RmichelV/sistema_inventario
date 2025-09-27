<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';


import { Table as salaryTable} from '@/components/ui/Table';
import type { User } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    users: User[];

}>();
console.log('user')
console.log(props.users);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <PlaceholderPattern/>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-4">
                        <PlaceholderPattern/>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                   <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                      <PlaceholderPattern/>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <salaryTable
                    :cadena="users??[]"
                    :cabeceras="['nombre','Cargo','Salario (Bs.)','Ingreso','Ajuste Total (Bs.)','Salario Final','Acciones']"
                    :campos="['name','role','base_salary','hire_date','total_salary_adjustment','final_salary']"
                    :agregar="{ 
                        href: route('rusers.create'), 
                        color: 'green', 
                        name: 'Agregar Empleado',
                        iconName: 'bx-plus' }"
                    :acciones="[
                        {
                            href: (item) => route('rusers.edit' , item.id),
                            color: 'blue',
                            name: 'Editar',
                            iconName: 'bx-pencil',
                        }
                    ]"
                />
            </div>
        </div>
    </AppLayout>
</template>
