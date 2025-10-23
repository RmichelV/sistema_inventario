<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// PlaceholderPattern removed (unused)
import { Table as UserTable} from '@/components/ui/Table';
import type { SalaryAdjustment } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const { salaryAdjustments } = defineProps<{
    salaryAdjustments: SalaryAdjustment[]
}>();
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
            
            <UserTable
                :cadena="salaryAdjustments??[]"
                :cabeceras="['Empleado','Tipo','Monto','Descripción']"
                :campos="['user_name','salary_adjustment_type','amount','description']"
                :agregar="{ 
                    href: route('rsalary_adjustments.create'), 
                    color: 'green', 
                    name: 'Agregar Cobro',
                    iconName: 'bx-plus' }"
                :acciones="[
                ]"
            />
            </div>
        </div>
    </AppLayout>
</template>
