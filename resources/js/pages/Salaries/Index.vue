<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types'; // Se importan User y BreadcrumbItem
import { Head, router } from '@inertiajs/vue3'; // Importar router

import { Table as salaryTable} from '@/components/ui/Table';
import { useSwal } from '../../composables/useSwal';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Gestion de Salarios',
        href: '/rsalaries',
    },
];

const { users } = defineProps<{
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
const swal = useSwal();

const realizarPago = (usuario: User) => {
    swal.fire({
        title: `¿Registrar pago para ${usuario.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, pagar!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true,
    }).then((result: any) => {
        if (result.isConfirmed) {
            // 1. CONVERTIR CAMPOS A NÚMEROS
            const salarioBase = parseFloat(usuario.base_salary.replace(/,/g, ''));
            const totalAdjustmentAdicional = parseFloat(usuario.total_adjustment.replace(/,/g, ''));
            const salarioDevengado = parseFloat(usuario.salario_devengado.replace(/,/g, ''));
            const finalSalary = parseFloat(usuario.final_salary.replace(/,/g, ''));

            // 2. CÁLCULO DE DESCUENTO POR MINUTOS NO TRABAJADOS (PÉRDIDA DE SUELDO BASE)
            const descuentoPorMinutos = salarioBase - salarioDevengado;
            const descuentoBasePorMinutos = Math.max(0, descuentoPorMinutos);

            // 3. DATOS ADICIONALES
            const payDate = new Date().toISOString().split('T')[0];
            const userIdM = 1;

            router.post(route('rsalaries.store'), {
                user_id: usuario.id,
                base_salary: salarioBase,
                salary_adjustment: totalAdjustmentAdicional,
                discounts: descuentoBasePorMinutos,
                total_salary: finalSalary,
                paydate: payDate,
                user_id_m: userIdM,
            }, {
                onSuccess: () => {
                    swal.fire({
                        title: '¡Pago registrado!',
                        text: 'El pago se ha registrado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Cerrar',
                    });
                    router.reload({ only: ['users'] });
                },
                onError: (errors) => {
                    swal.fire({
                        title: 'Error',
                        text: 'Hubo un error al registrar el pago.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar',
                    });
                    console.error('Error al registrar el pago:', errors);
                }
            });
        } else if (result.dismiss === swal.DismissReason.cancel) {
            swal.fire({
                title: 'Cancelado',
                text: 'No se realizó el pago.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
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
                    :cadena="users??[]"
                    :cabeceras="['nombre','Cargo','Salario Base','Ajustes (B/D)','Horas trabajadas','Salario Final','Acciones']"
                    :campos="['name','role','base_salary','total_adjustment','total_time_formatted','final_salary']"
                    :agregar="false"
                    :acciones="accionesTabla"
                />
                <p class="mt-10">
                    Nota: <br>
                    <strong>Ajustes (B/D)</strong>: Es la suma de los ajustes positivos (bonos) y negativos (descuentos) aplicados al empleado a lo largo del mes <br>
                    <strong>Salario Final</strong> : Es la suma de Salario Base + Ajustes (B/D) + el precio calculado por las horas trabajadas
                </p>
            </div>
        </div>
    </AppLayout>
</template>