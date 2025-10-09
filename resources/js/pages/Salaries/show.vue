<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Salary, type BreadcrumbItem, type User } from '@/types'; // Se importan User y BreadcrumbItem
import { Head, router } from '@inertiajs/vue3'; // Importar router
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

import { Table as salaryTable} from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Gestion de Salarios',
        href: '/rsalariesF',
    },
];

const props = defineProps<{
    salaries: Salary[];

}>();

// Agrega las importaciones necesarias
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useSwal } from '../../composables/useSwal'; // Importa el composable

// Lógica de SweetAlert2
const page = usePage();
const swal = useSwal(); // Instancia el composable

onMounted(() => {
    // Si existe un mensaje flash de éxito, muestra la alerta
    if (page.props.flash.success) {
        // Usa el composable directamente
        swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: page.props.flash.success,
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
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
            </div> -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <salaryTable
                    :cadena="salaries??[]"
                    :cabeceras="['Empleado','Salario Base','Ajustes (B/D)','Des/Minutos tarde','Salario Final','Fecha de Pago','Pagado Por']"
                    :campos="['user_id','base_salary','salary_adjustment','discounts','total_salary','paydate','user_id_m']"
                    :agregar="false"
                    :acciones="[]"
                />
            </div>
        </div>
    </AppLayout>
</template>