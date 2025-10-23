<script setup lang="ts">
import InputError from '@/components/InputError.vue';
// TextLink removed (unused)
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// AuthBase removed (unused)
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
// PlaceholderPattern removed (unused)
import type { User } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Registro de movimientos de salarios',
        href: '/rsalary_adjustments',
    },
];

const { users } = defineProps<{
    users:User[];
}>();

import { ref, watch } from 'vue';

const adjustmentType = ref('');
const amount = ref('');

const validateAmount = () => {
    if (!amount.value) return;
    
    const numericValue = parseFloat(amount.value);
    
    if (adjustmentType.value === 'Descuento') {
        // Si es descuento, asegurarse que sea negativo
        if (numericValue > 0) {
            amount.value = (-numericValue).toString();
        }
    } else if (adjustmentType.value === 'Bonificacion') {
        // Si es bonificación, asegurarse que sea positivo
        if (numericValue < 0) {
            amount.value = Math.abs(numericValue).toString();
        }
    }
};

// Observar cambios en el tipo de ajuste
watch(adjustmentType, () => {
    if (amount.value) {
        validateAmount();
    }
});

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
                
                    <Form
                        method="post"
                        :action="route('rsalary_adjustments.store')"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="user_id">Elija un empleado</Label>
                                <select id="user_id" name="user_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="" disabled selected>Seleccione un empleado</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                                <InputError :message="errors.user_id" />
                            </div>

                           <div class="grid gap-2">
                                <Label for="salary_adjustment_type">Tipo </Label>
                                <select 
                                    id="salary_adjustment_type" 
                                    name="salary_adjustment_type" 
                                    v-model="adjustmentType"
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" 
                                    required
                                >
                                    <option value="" disabled selected>Seleccione un tipo</option>
                                    <option value="Bonificacion">Bonificación</option>
                                    <option value="Descuento">Descuento</option>
                                </select>
                                <InputError :message="errors.salary_adjustment_type" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="amount">Monto</Label>
                                <Input 
                                    id="amount" 
                                    type="number" 
                                    required 
                                    autofocus 
                                    :tabindex="1" 
                                    v-model="amount"
                                    @input="validateAmount"
                                    autocomplete="amount" 
                                    name="amount" 
                                    placeholder="Ej. 500.00" 
                                    step="0.01"
                                />
                                <InputError :message="errors.amount" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="date">Fecha del suceso</Label>
                                <Input id="date" type="date" required autofocus :tabindex="1" autocomplete="date" name="date" />
                                <InputError :message="errors.date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Descripción</Label>
                                <Input id="description" type="text" required autofocus :tabindex="1" autocomplete="description" name="description" placeholder="Ej. Bonificación por desempeño"/>
                                <InputError :message="errors.description" />

                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="processing">
                                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                                Registrar suceso
                            </Button>
                        </div>

                        <!-- <div class="text-sm text-center text-muted-foreground">
                            Already have an account?
                            <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                        </div> -->
                    </Form>
            </div>
        </div>
    </AppLayout>
</template>