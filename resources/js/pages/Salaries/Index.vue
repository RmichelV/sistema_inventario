<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types'; // Se importan User y BreadcrumbItem
import { Head, router } from '@inertiajs/vue3'; // Importar router
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

import { Table as salaryTable} from '@/components/ui/Table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Gestion de Salarios',
        href: '/rsalaries',
    },
];

const props = defineProps<{
    users: User[];

}>();

// ------------------------------------------
// LÓGICA DE PAGO (Envío de valor bruto)
// ------------------------------------------

/**
 * Función que toma los datos calculados de la fila del usuario y los envía al backend.
 * Envía el ajuste total sin separar, dejando que el controlador de Laravel maneje
 * el valor negativo (-300) y lo divida entre salary_adjustment y discounts.
 */
const realizarPago = (usuario: User) => {
    
    // 1. CONVERTIR CAMPOS A NÚMEROS
    const salarioBase = parseFloat(usuario.base_salary.replace(/,/g, ''));
    // totalAdjustmentAdicional contiene el valor bruto (ej: -300)
    const totalAdjustmentAdicional = parseFloat(usuario.total_adjustment.replace(/,/g, ''));
    const salarioDevengado = parseFloat(usuario.salario_devengado.replace(/,/g, ''));
    const finalSalary = parseFloat(usuario.final_salary.replace(/,/g, ''));


    // 2. CÁLCULO DE DESCUENTO POR MINUTOS NO TRABAJADOS (PÉRDIDA DE SUELDO BASE)
    const descuentoPorMinutos = salarioBase - salarioDevengado;
    const descuentoBasePorMinutos = Math.max(0, descuentoPorMinutos); // Siempre >= 0


    // 3. DATOS ADICIONALES
    const payDate = new Date().toISOString().split('T')[0];
    const userIdM = 1; 

    // Enviamos los datos al endpoint de almacenamiento (store)
    router.post(route('rsalaries.store'), {
        user_id: usuario.id,
        base_salary: salarioBase, // El sueldo completo de referencia
        
        // Mapeo a los campos de la tabla 'salaries'
        
        // -> salary_adjustment: El valor total del ajuste (PUEDE SER NEGATIVO, ej: -300).
        // SU CONTROLADOR DEBE MANEJAR ESTO.
        salary_adjustment: totalAdjustmentAdicional, 
        
        // -> discounts: SOLO la pérdida de salario por minutos no trabajados (el backend agregará aquí el -300).
        discounts: descuentoBasePorMinutos, 

        // -> total_salary: El monto final a pagar 
        total_salary: finalSalary, 
        
        paydate: payDate,
        user_id_m: userIdM,
    }, {
        onSuccess: () => {
            router.reload({ only: ['users'] }); 
        },
        onError: (errors) => {
            console.error('Error al registrar el pago:', errors);
        }
    });
};

// La corrección de tipado en 'item'
const accionesTabla = [
    {
        onClick: (item: User) => { 
            realizarPago(item);
        },
        color: 'blue',
        name: 'Pagar',
        iconName: 'bx-money',
    },
]; 

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
                    :cabeceras="['nombre','Cargo','Salario Base','Ajustes (B/D)','Horas trabajadas','Salario Final','Acciones']"
                    :campos="['name','role','base_salary','total_adjustment','total_time_formatted','final_salary']"
                    :agregar="false"
                    :acciones="accionesTabla"
                />
            </div>
        </div>
    </AppLayout>
</template>